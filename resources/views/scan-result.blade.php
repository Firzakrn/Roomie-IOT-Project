{{--
    Result of a QR scan.

    Four states, all driven by the same two-step scan (AGENTS.md):
      roomie_scan_action === 'form'      → fill in course + SKS, then check in
      roomie_scan_action === 'checkin'   → check-in confirmed
      roomie_scan_action === 'checkout'  → check-out confirmed
      otherwise                         → room is busy (someone else is using it)

    Duration is never computed from anything the browser sends. The form only
    submits course_name and sks; the server derives end_at from the SKS count.
--}}
<x-layouts.blank title="Hasil Scan · Roomie"
                description="Hasil pemindaian QR ruang kelas beserta data check-in Roomie.">

    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-1.5 rounded-lg text-sm font-semibold text-slate-500 transition-colors hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-slate-400 dark:hover:text-slate-100">
            <x-ui.icon name="arrow-left" :size="16" />
            Dashboard
        </a>
        <a href="{{ route('reservation.history') }}"
           class="inline-flex items-center gap-1.5 rounded-lg text-sm font-semibold text-slate-500 transition-colors hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-slate-400 dark:hover:text-slate-100">
            Riwayat
            <x-ui.icon name="chevron-right" :size="16" />
        </a>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-tile sm:p-6 dark:border-slate-800 dark:bg-roomie-surface">

        @if (session('roomie_scan_action') === 'checkin')
            <x-ui.alert tone="success" title="Check-in berhasil">
                Data mata kuliah tersimpan dan ruangan sedang tercatat dipakai.
                Scan QR yang sama lagi untuk check-out.
            </x-ui.alert>

        @elseif (session('roomie_scan_action') === 'checkout')
            <x-ui.alert tone="success" title="Check-out berhasil">
                Penggunaan kelas selesai dan tercatat di riwayat.
            </x-ui.alert>

        @elseif ($showCheckinForm)
            <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-slate-50">Isi data kelas</h1>
            <p class="mt-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                QR berhasil dibaca. Lengkapi mata kuliah dan SKS untuk check-in.
            </p>

        @else
            <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-slate-50">Informasi ruangan</h1>
            <p class="mt-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                Status penggunaan ruangan berdasarkan QR kelas.
            </p>
        @endif

        {{-- Ruangan terdeteksi --}}
        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-900/40">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ruangan terdeteksi</p>
            <p class="mt-1.5 text-[15px] font-semibold text-slate-900 dark:text-slate-50">{{ $room->name }}</p>
            <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 font-mono text-xs text-slate-500 dark:text-slate-400">
                <span>{{ $room->code }}</span>
                <span aria-hidden="true">·</span>
                <span>Gedung {{ $room->building ?? '-' }}</span>
                <span aria-hidden="true">·</span>
                <span>Lantai {{ $room->floor ?? '-' }}</span>
            </p>
        </div>

        @if ($errors->any())
            <div class="mt-4">
                <x-ui.alert tone="danger">{{ $errors->first() }}</x-ui.alert>
            </div>
        @endif

        @if ($showCheckinForm)
            <form method="POST" action="{{ route('scan.checkin') }}" class="mt-5 space-y-5" data-checkin-form>
                @csrf

                <x-ui.field name="course_name" label="Nama mata kuliah" :maxlength="255" :autofocus="true"
                           autocomplete="off"
                           placeholder="Contoh: Pemrograman Web" />

                <div>
                    <span class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        Jumlah SKS
                        <span class="text-rose-600" aria-hidden="true">*</span>
                        <span class="sr-only">(wajib diisi)</span>
                    </span>

                    <div class="mt-1.5 grid grid-cols-6 gap-2" role="radiogroup" aria-label="Jumlah SKS">
                        @for ($i = 1; $i <= 6; $i++)
                            <label class="relative">
                                <input type="radio" name="sks" value="{{ $i }}" class="peer sr-only" required
                                       @checked((string) old('sks') === (string) $i)>
                                <span class="flex h-11 cursor-pointer items-center justify-center rounded-xl border border-slate-300 bg-white text-sm font-semibold tabular-nums text-slate-700 transition-colors duration-200 hover:border-indigo-400 peer-focus-visible:ring-4 peer-focus-visible:ring-indigo-500/20 dark:border-slate-700 dark:bg-roomie-night dark:text-slate-200"
                                      data-sks-option>{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                        1 SKS = {{ (int) config('roomie.minutes_per_sks', 50) }} menit.
                    </p>
                </div>

                {{-- Perhitungan durasi: server yang menentukan, hanya pratinjau di sini. --}}
                <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4 dark:border-indigo-500/25 dark:bg-indigo-500/10"
                     aria-live="polite">
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-700 dark:text-indigo-300">Perhitungan waktu</p>

                    <dl class="mt-3 space-y-1.5 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-indigo-900/80 dark:text-indigo-100/80">Durasi perkuliahan</dt>
                            <dd class="font-semibold tabular-nums text-indigo-900 dark:text-indigo-100" id="class-minutes">—</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-indigo-900/80 dark:text-indigo-100/80">Toleransi tambahan</dt>
                            <dd class="font-semibold tabular-nums text-indigo-900 dark:text-indigo-100">{{ (int) config('roomie.grace_minutes', 60) }} menit</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-t border-indigo-200/70 pt-2 dark:border-indigo-400/25">
                            <dt class="font-semibold text-indigo-900 dark:text-indigo-100">Total penggunaan</dt>
                            <dd class="font-bold tabular-nums text-indigo-900 dark:text-indigo-100" id="total-minutes">Pilih SKS</dd>
                        </div>
                    </dl>

                    <p class="mt-3 text-xs leading-relaxed text-indigo-900/70 dark:text-indigo-100/70">
                        Waktu mulai dihitung saat tombol ditekan dan batas akhirnya ditetapkan server.
                    </p>
                </div>

                <x-ui.button type="submit" size="lg" class="w-full" data-submit>
                    <span data-submit-label>Konfirmasi check-in</span>
                </x-ui.button>
            </form>

        @else
            {{-- Status ruangan --}}
            @if ($activeSession)
                <div class="mt-5 rounded-2xl border border-rose-200/80 bg-rose-50/50 p-4 dark:border-rose-500/30 dark:bg-rose-500/5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-[15px] font-semibold text-rose-950 dark:text-rose-100">Sedang dipakai</p>
                        <x-ui.badge state="occupied">{{ $mine ? 'Sesi kamu' : 'Pengguna lain' }}</x-ui.badge>
                    </div>

                    <p class="mt-1.5 text-sm text-rose-900/80 dark:text-rose-100/80">
                        {{ $mine ? 'Kamu sedang memakai kelas ini.' : 'Ruangan sedang digunakan pengguna lain.' }}
                    </p>

                    <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 border-t border-rose-200/70 pt-4 text-sm dark:border-rose-500/20">
                        <div class="col-span-2">
                            <dt class="text-xs uppercase tracking-wider text-rose-700/80 dark:text-rose-300/80">Mata kuliah</dt>
                            <dd class="mt-0.5 font-semibold text-rose-950 dark:text-rose-100">{{ $activeSession->reservation?->course_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-rose-700/80 dark:text-rose-300/80">SKS</dt>
                            <dd class="mt-0.5 font-mono tabular-nums text-rose-950 dark:text-rose-100">{{ $activeSession->reservation?->sks }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-rose-700/80 dark:text-rose-300/80">Check-in</dt>
                            <dd class="mt-0.5 font-mono tabular-nums text-rose-950 dark:text-rose-100">{{ $activeSession->checked_in_at?->format('H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-rose-700/80 dark:text-rose-300/80">Kuliah selesai</dt>
                            <dd class="mt-0.5 font-mono tabular-nums text-rose-950 dark:text-rose-100">
                                @if ($activeSession->checked_in_at && $activeSession->reservation)
                                    {{ $activeSession->checked_in_at->copy()->addMinutes($activeSession->reservation->sks * (int) config('roomie.minutes_per_sks', 50))->format('H:i') }}
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-rose-700/80 dark:text-rose-300/80">Batas penggunaan</dt>
                            <dd class="mt-0.5 font-mono tabular-nums text-rose-950 dark:text-rose-100">{{ $activeSession->reservation?->end_at?->format('H:i') }}</dd>
                        </div>
                    </dl>

                    @if ($mine)
                        <p class="mt-4 text-xs leading-relaxed text-rose-900/80 dark:text-rose-100/80">
                            Scan QR yang sama untuk check-out lebih awal. Jika waktu habis, kelas otomatis tersedia kembali.
                        </p>
                    @endif
                </div>
            @else
                <div class="mt-5">
                    <x-ui.alert tone="success" title="Ruangan tersedia">
                        Tidak ada sesi aktif. Scan lagi dengan tombol di bawah untuk membuka formulir check-in.
                    </x-ui.alert>
                </div>
            @endif
        @endif

        <div class="mt-5">
            <x-ui.button href="{{ route('scanner') }}" variant="secondary" size="lg" icon="camera" class="w-full">
                {{ $mine ? 'Scan lagi untuk check-out' : ($showCheckinForm ? 'Batal, scan ulang' : 'Buka kamera untuk scan') }}
            </x-ui.button>
        </div>
    </section>

<script>
    (() => {
        const form = document.querySelector('[data-checkin-form]');
        if (!form) return;

        const minutesPerSks = {{ (int) config('roomie.minutes_per_sks', 50) }};
        const graceMinutes = {{ (int) config('roomie.grace_minutes', 60) }};

        const classMinutes = document.getElementById('class-minutes');
        const totalMinutes = document.getElementById('total-minutes');
        const submit = form.querySelector('[data-submit]');
        const submitLabel = form.querySelector('[data-submit-label]');

        const selected = () => form.querySelector('input[name="sks"]:checked');

        const paintOptions = () => {
            form.querySelectorAll('[data-sks-option]').forEach((node) => {
                const isActive = node.previousElementSibling.checked;
                node.classList.toggle('border-indigo-600', isActive);
                node.classList.toggle('bg-indigo-600', isActive);
                node.classList.toggle('text-white', isActive);
                node.classList.toggle('border-slate-300', !isActive);
                node.classList.toggle('bg-white', !isActive);
                node.classList.toggle('dark:border-slate-700', !isActive);
                node.classList.toggle('dark:bg-roomie-night', !isActive);
                node.classList.toggle('dark:text-slate-200', !isActive);
            });

            const active = selected();
            return active ? Number(active.value) : null;
        };

        const preview = () => {
            const sks = paintOptions();

            if (!sks) {
                classMinutes.textContent = '—';
                totalMinutes.textContent = 'Pilih SKS';
                return;
            }

            const lecture = sks * minutesPerSks;
            const total = lecture + graceMinutes;

            classMinutes.textContent = lecture + ' menit';
            totalMinutes.textContent = total + ' menit (' + Math.floor(total / 60) + ' jam ' + (total % 60) + ' menit)';
        };

        form.addEventListener('change', (event) => {
            if (event.target.name === 'sks') preview();
        });

        // A double submit would fire the check-in transaction twice.
        form.addEventListener('submit', (event) => {
            if (!selected()) {
                event.preventDefault();
                return;
            }
            submit.disabled = true;
            submitLabel.textContent = 'Memproses…';
        });

        preview();
    })();
</script>
</x-layouts.blank>