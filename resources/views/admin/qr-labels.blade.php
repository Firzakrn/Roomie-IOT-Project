{{--
    Printable QR label sheet (admin only — the gate lives in
    DashboardController::qrLabels and is unchanged).

    The raw qr_token is deliberately NOT printed in text: the QR already encodes
    it, and printing it in plain type would let anyone at the door copy the token
    and check in without being there.

    PR5 will add @page tuning and per-label install notes; PR1 restores the sheet
    on the shared shell and replaces the old off-brand palette.
--}}
<x-layouts.print title="Cetak QR Ruangan · Roomie">
    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    </x-slot:head>

    <div class="no-print mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">QR kelas Roomie</h1>
            <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-slate-600">
                Cetak satu label untuk setiap kelas lalu tempel di pintu. Isi QR adalah token ruangan,
                bukan alamat web, sehingga tetap berlaku walau tunnel berubah. Pengguna harus login ke
                Roomie lebih dulu untuk memindainya.
            </p>
        </div>

        <x-ui.button type="button" variant="primary" size="md" icon="printer" data-print class="shrink-0">
            Cetak QR
        </x-ui.button>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($rooms as $room)
            <article class="print-keep rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-tile">
                <h2 class="text-[15px] font-semibold text-slate-900">{{ $room->name }}</h2>
                <p class="mt-1 font-mono text-xs text-slate-500">{{ $room->code }} · Gedung {{ $room->building }}</p>

                <div id="qr-{{ $room->id }}" data-token="{{ $room->qr_token }}" data-room="{{ $room->name }}"
                     class="roomie-qr mx-auto mt-5 flex min-h-[190px] items-center justify-center"></div>

                <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-indigo-700">Scan lewat Roomie</p>
            </article>
        @empty
            <div class="sm:col-span-2 lg:col-span-3">
                <x-ui.empty title="Belum ada ruangan"
                            body="Tambahkan ruang kelas terlebih dahulu sebelum mencetak label QR."
                            icon="qr" />
            </div>
        @endforelse
    </div>

<script>
    document.querySelector('[data-print]')?.addEventListener('click', () => window.print());

    if (typeof QRCode !== 'undefined') {
        // Rendered at 2x the printed size so the QR stays crisp on retina
        // screens and on paper; CSS scales it back down to 190px.
        const DISPLAY_SIZE = 190;
        const RENDER_SIZE = DISPLAY_SIZE * 2;

        document.querySelectorAll('[data-token]').forEach((node) => {
            new QRCode(node, {
                text: node.dataset.token,
                width: RENDER_SIZE,
                height: RENDER_SIZE,
                correctLevel: QRCode.CorrectLevel.M,
            });

            // qrcodejs injects an <img> without alt text; the QR encodes an
            // opaque token, so describe the label rather than the code.
            const img = node.querySelector('img');
            if (img) img.alt = 'QR code untuk ' + node.dataset.room;
        });
    }
</script>
</x-layouts.print>