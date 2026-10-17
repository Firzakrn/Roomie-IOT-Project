{{--
    QR check-in / check-out.

    PR1 scope: moved onto the shared shell with the new components, and the
    camera viewport gets its DESIGN.md §4.C treatment (corner brackets + laser
    line, shake on error). PR3 adds torch/camera switch, retry, the success
    flash and the manual-token fallback disclosure.
--}}
<x-layouts.blank title="Scan QR untuk Check-in · Roomie"
                description="Pindai QR di pintu kelas untuk check-in atau check-out ruang kelas Roomie.">
    <x-slot:head>
        <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    </x-slot:head>

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
        <div class="text-center">
            <span class="mx-auto flex size-11 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300"
                  aria-hidden="true">
                <x-ui.icon name="scan" :size="22" />
            </span>
            <h1 class="mt-3 text-xl font-semibold tracking-tight text-slate-900 dark:text-slate-50">Scan QR Kelas</h1>
            <p class="mx-auto mt-1.5 max-w-[38ch] text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                Datang ke depan kelas lalu arahkan kamera ke QR di pintu.
                Scan pertama check-in, scan kedua check-out.
            </p>
        </div>

        @if ($errors->any())
            <div class="mt-5">
                <x-ui.alert tone="danger">{{ $errors->first() }}</x-ui.alert>
            </div>
        @endif

        <div id="scan-stage" class="relative mt-5 overflow-hidden rounded-2xl bg-slate-950">
            {{-- Square viewport with glowing corner brackets (DESIGN.md §4.C). --}}
            <div class="relative aspect-square w-full">
                <div id="reader" class="absolute inset-0"></div>

                <div class="pointer-events-none absolute inset-0 hidden" id="scan-reticle" aria-hidden="true">
                    <span class="roomie-corner left-4 top-4 border-l-2 border-t-2"></span>
                    <span class="roomie-corner right-4 top-4 border-r-2 border-t-2"></span>
                    <span class="roomie-corner bottom-4 left-4 border-b-2 border-l-2"></span>
                    <span class="roomie-corner bottom-4 right-4 border-b-2 border-r-2"></span>
                    <span class="roomie-laser"></span>
                </div>
            </div>
        </div>

        <p id="scan-message" role="status" aria-live="polite"
           class="mt-4 text-center text-sm leading-relaxed text-slate-500 dark:text-slate-400">Menyiapkan kamera&hellip;</p>

        <form action="{{ route('scan.process') }}" id="scan-form" method="POST" class="hidden">
            @csrf
            <input type="hidden" id="qr-token" name="qr_token">
        </form>
    </section>

    <p class="mt-5 text-center text-xs leading-relaxed text-slate-500 dark:text-slate-400">
        Kamera hanya berjalan di situs HTTPS (misalnya lewat Cloudflare Tunnel).
        Waktu check-in dan batas durasi selalu ditetapkan server.
    </p>

<script>
    (() => {
        const stage = document.getElementById('scan-stage');
        const reticle = document.getElementById('scan-reticle');
        const statusText = document.getElementById('scan-message');
        const form = document.getElementById('scan-form');

        let scanningDone = false;

        const say = (message, tone) => {
            statusText.textContent = message;
            statusText.className = 'mt-4 text-center text-sm leading-relaxed '
                + (tone === 'error'
                    ? 'text-rose-600 dark:text-rose-400'
                    : tone === 'ok'
                        ? 'text-emerald-700 dark:text-emerald-400'
                        : 'text-slate-500 dark:text-slate-400');

            if (tone === 'error' && stage && !stage.classList.contains('roomie-shake')) {
                stage.classList.remove('roomie-shake');
                // Restart the animation on repeated failures.
                void stage.offsetWidth;
                stage.classList.add('roomie-shake');
            }
        };

        const tokenFromQR = (value) => {
            const raw = String(value || '').trim();
            // QR Roomie dicetak admin sebagai token acak, bukan URL.
            return /^[A-Za-z0-9]{16,128}$/.test(raw) ? raw : null;
        };

        const handleResult = (value) => {
            if (scanningDone) return;

            const token = tokenFromQR(value);
            if (!token) {
                say('QR ini tidak sesuai format Roomie. Gunakan QR dari kelas.', 'error');
                return;
            }

            scanningDone = true;
            say('QR terbaca! Memproses check-in atau check-out…', 'ok');
            document.getElementById('qr-token').value = token;
            form.submit();
        };

        if (typeof Html5Qrcode === 'undefined') {
            say('Komponen scanner tidak dimuat. Periksa koneksi internet lalu muat ulang.', 'error');
            return;
        }

        const reader = new Html5Qrcode('reader');

        Html5Qrcode.getCameras()
            .then((cameras) => {
                if (!cameras.length) throw new Error('Tidak ada kamera yang tersedia.');
                const preferred = cameras.find((c) => /back|rear|environment|belakang/i.test(c.label)) || cameras[0];
                return reader.start(preferred.id, { fps: 10, qrbox: 220 }, handleResult, () => {});
            })
            .then(() => {
                reticle?.classList.remove('hidden');
                say('Kamera siap. Arahkan ke QR yang ada di pintu kelas.', 'ok');
            })
            .catch((error) => {
                say(
                    'Tidak dapat membuka kamera. Pastikan izin kamera aktif dan situs dibuka lewat HTTPS. '
                    + (error?.message || ''),
                    'error'
                );
            });
    })();
</script>
</x-layouts.blank>