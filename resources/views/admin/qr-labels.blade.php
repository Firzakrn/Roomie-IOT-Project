{{--
    Printable QR door labels (admin only — the gate lives in
    DashboardController::qrLabels and is unchanged).

    Each label is a fixed 90 x 60 mm box, in millimetres on screen AND on paper,
    so the preview is what the printer actually produces. On A4 that is two per
    row and eight per page, and the dashed edge doubles as a cut guide.

    The raw qr_token is deliberately NOT printed. The QR already encodes it, and
    a token printed in plain type beside the code would let anyone at the door
    copy it down and check in without being there. It lives in the data-token
    attribute only, which no one sees on paper.
--}}
<x-layouts.print title="Cetak QR Ruangan · Roomie">
    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    </x-slot:head>

    {{-- Everything below is screen-only guidance. It is stripped before printing,
         which is why the essential instruction also sits ON each label. --}}
    <div class="no-print mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Label QR ruang kelas</h1>
                <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-slate-600">
                    Cetak satu label untuk setiap kelas, lalu tempel di pintu. Isi QR adalah
                    <strong class="font-semibold">token ruangan</strong>, bukan alamat web, jadi label
                    tetap berlaku walau tunnel berubah. Pengguna harus login ke Roomie lebih dulu
                    untuk memindainya.
                </p>
            </div>

            <x-ui.button type="button" variant="primary" size="md" icon="printer" data-print class="shrink-0">
                Cetak label
            </x-ui.button>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
            @foreach ([
                ['icon' => 'layers', 'title' => 'Ukuran tetap', 'body' => 'Setiap label 90 × 60 mm. Di kertas A4 ada 2 label per baris dan 8 per halaman.'],
                ['icon' => 'info', 'title' => 'Garis putus', 'body' => 'Bingkai label berubah jadi putus-putus saat dicetak. Potong mengikuti garis itu.'],
                ['icon' => 'door', 'title' => 'Token tidak dicetak', 'body' => 'Kode QR tidak ditulis sebagai teks. Kalau ada yang menyalin token, check-in bisa dilakukan tanpa datang.'],
            ] as $tip)
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600" aria-hidden="true">
                        <x-ui.icon :name="$tip['icon']" :size="16" />
                    </span>
                    <p class="mt-2.5 text-sm font-semibold text-slate-900">{{ $tip['title'] }}</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $tip['body'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Form Tambah Ruangan Baru --}}
        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-semibold text-slate-900">Tambah Ruang Kelas Baru</h2>
            <p class="mt-1 text-sm text-slate-500">Isi data ruangan, QR code akan otomatis digenerate.</p>

            <form method="POST" action="{{ route('rooms.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @csrf

                <div>
                    <x-ui.field label="Kode Ruangan" name="code" required
                        placeholder="A10.01.12"
                        hint="Contoh: A10.01.12, LAB-01, RUANG-1 (maks 50 karakter)" />
                </div>

                <div class="sm:col-span-2 lg:col-span-2">
                    <x-ui.field label="Nama Ruangan" name="name" required
                        placeholder="Ruang Kuliah 101" />
                </div>

                <div>
                    <x-ui.field label="Gedung" name="building" required
                        placeholder="A10" />
                </div>

                <div>
                    <x-ui.field label="Lantai" name="floor" type="number" min="1" max="50"
                        placeholder="1 (opsional)"
                        hint="Kosongkan jika tidak berlaku" />
                </div>

                <div>
                    <x-ui.field label="Kapasitas" name="capacity" type="number" min="1" max="500"
                        placeholder="40 (opsional)" />
                </div>

                <div class="sm:col-span-2 lg:col-span-5 flex justify-end">
                    <x-ui.button type="submit" variant="primary" icon="plus">
                        Tambah Ruangan
                    </x-ui.button>
                </div>
            </form>

            @if (session('errors'))
                <x-ui.alert variant="error" :message="session('errors')->first()" />
            @endif
        </div>
    </div>

    <div class="roomie-label-sheet">
        @php $newRoomId = session('new_room_id') @endphp
        @forelse ($rooms as $room)
            <article class="roomie-label print-keep {{ $newRoomId === $room->id ? 'ring-4 ring-indigo-500' : '' }}">
                <div>
                    <h2 class="truncate text-[13pt] font-semibold leading-tight text-slate-900">{{ $room->name }}</h2>
                    <p class="mt-1 truncate font-mono text-[8pt] text-slate-500">
                        {{ $room->code }} · Gedung {{ $room->building }} · Lantai {{ $room->floor ?? '-' }}
                    </p>
                </div>

                <div id="qr-{{ $room->id }}" data-token="{{ $room->qr_token }}" data-room="{{ $room->name }}"
                     class="roomie-qr flex items-center justify-center"></div>

                <div>
                    <p class="text-[8pt] font-bold uppercase tracking-widest text-indigo-700">Scan lewat Roomie</p>
                    <p class="mt-0.5 text-[7pt] leading-tight text-slate-500">
                        Tempel di pintu kelas · arahkan kamera ke QR
                    </p>
                </div>
            </article>
        @empty
            <div class="col-span-full">
                <x-ui.empty title="Belum ada ruangan"
                            body="Tambahkan ruang kelas terlebih dahulu sebelum mencetak label QR."
                            icon="qr" />
            </div>
        @endforelse
    </div>

<script>
    document.querySelector('[data-print]')?.addEventListener('click', () => window.print());

    if (typeof QRCode !== 'undefined') {
        // The printed QR is 28mm on paper, which is ~331px at 300dpi. Rendering
        // at 448 leaves headroom so the code stays crisp instead of turning into
        // a blur, and the img is then scaled down by CSS.
        const RENDER_SIZE = 448;

        document.querySelectorAll('[data-token]').forEach((node) => {
            new QRCode(node, {
                text: node.dataset.token,
                width: RENDER_SIZE,
                height: RENDER_SIZE,
                correctLevel: QRCode.CorrectLevel.M,
            });

            // qrcodejs injects an <img> with no alt text. The QR encodes an
            // opaque token, so describe the label rather than the code itself.
            const img = node.querySelector('img');
            if (img) {
                img.alt = 'QR code untuk ' + node.dataset.room;
                img.width = RENDER_SIZE;
                img.height = RENDER_SIZE;
            }
        });
    }
</script>
</x-layouts.print>