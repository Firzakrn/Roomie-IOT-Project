{{--
    QR check-in / check-out.

    Two ways in, one form:

      1. the camera, which fills #qr-token and submits the form itself;
      2. a manual token entry, for when the camera is denied, blocked, or the
         phone has no usable camera. DESIGN.md §5.3 asks for this fallback; it
         reuses the existing POST /scan, so no new endpoint and no new rule.

    Every camera control is hidden unless the device actually supports it —
    a torch button on a laptop is a dead control, not a feature.
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

        {{-- Viewport: hugs the camera's own aspect ratio, with an aim reticle
             (DESIGN.md §4.C). Square while there is no camera, so the frame
             never jumps after a permission prompt. --}}
        <div id="scan-stage" class="relative mt-5 overflow-hidden rounded-2xl">
            <div id="scan-frame" class="roomie-scanframe relative w-full" data-camera="off">
                <div id="reader"></div>

                <div id="scan-reticle" class="roomie-reticle" aria-hidden="true" hidden>
                    <span class="roomie-corner roomie-corner--tl"></span>
                    <span class="roomie-corner roomie-corner--tr"></span>
                    <span class="roomie-corner roomie-corner--bl"></span>
                    <span class="roomie-corner roomie-corner--br"></span>
                    <span class="roomie-laser"></span>
                </div>

                {{-- Confirmation before handing off to the server --}}
                <div id="scan-flash" class="absolute inset-0 grid place-items-center bg-emerald-500/90" hidden>
                    <div class="roomie-flash__mark flex flex-col items-center gap-2 text-white">
                        <x-ui.icon name="check-circle" :size="44" />
                        <span class="text-sm font-semibold">QR terbaca</span>
                    </div>
                </div>
            </div>
        </div>

        <p id="scan-message" role="status" aria-live="polite"
           class="mt-4 text-center text-sm leading-relaxed text-slate-500 dark:text-slate-400">Menyiapkan kamera&hellip;</p>

        {{-- Camera controls. Each wrapper is revealed only when the device
             actually supports the feature. --}}
        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
            <span data-torch-wrap hidden>
                <x-ui.button type="button" variant="secondary" size="md" icon="zap" data-torch aria-pressed="false">
                    Senter
                </x-ui.button>
            </span>

            <span data-camera-wrap hidden>
                <x-ui.button type="button" variant="secondary" size="md" icon="refresh" data-switch-camera>
                    Ganti kamera
                </x-ui.button>
            </span>

            <span data-retry-wrap hidden>
                <x-ui.button type="button" variant="primary" size="md" icon="refresh" data-retry>
                    Coba lagi
                </x-ui.button>
            </span>
        </div>

        {{-- Manual fallback. Shares one form with the camera path. --}}
        <details id="manual-fallback" class="mt-5 group rounded-xl border border-slate-200 dark:border-slate-800">
            <summary class="flex cursor-pointer list-none items-center gap-2 px-4 py-3 text-sm font-semibold text-slate-600 transition-colors hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-slate-300 dark:hover:text-white">
                <x-ui.icon name="keyboard" :size="16" class="text-slate-400" />
                Kamera tidak bisa dibuka?
                <x-ui.icon name="chevron-down" :size="16" class="ml-auto text-slate-400 transition-transform duration-200 group-open:rotate-180" />
            </summary>

            <form id="scan-form" method="POST" action="{{ route('scan.process') }}" novalidate class="border-t border-slate-200 p-4 dark:border-slate-800">
                @csrf

                <label for="qr-token" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Token QR
                </label>
                <p id="qr-token-hint" class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                    Teks di dalam QR pada pintu kelas, misalnya dari foto label QR.
                </p>

                <input id="qr-token" name="qr_token" type="text"
                       autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"
                       inputmode="text"
                       aria-describedby="qr-token-hint"
                       placeholder="Tempel token di sini"
                       class="mt-2 h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 font-mono text-sm text-slate-900 shadow-tile outline-none transition-colors placeholder:font-sans placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-roomie-night dark:text-slate-100">

                <p id="token-error" class="mt-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400" hidden></p>

                <x-ui.button type="submit" variant="primary" size="md" icon="check" class="mt-3 w-full">
                    Lanjutkan dengan token
                </x-ui.button>
            </form>
        </details>
    </section>

    <p class="mt-5 text-center text-xs leading-relaxed text-slate-500 dark:text-slate-400">
        Kamera hanya berjalan di situs HTTPS (misalnya lewat Cloudflare Tunnel).
        Waktu check-in dan batas durasi selalu ditetapkan server.
    </p>

<script>
    (() => {
        const TOKEN_PATTERN = /^[A-Za-z0-9]{16,128}$/;

        const stage = document.getElementById('scan-stage');
        const frame = document.getElementById('scan-frame');
        const reticle = document.getElementById('scan-reticle');
        const flash = document.getElementById('scan-flash');
        const statusText = document.getElementById('scan-message');
        const form = document.getElementById('scan-form');
        const tokenInput = document.getElementById('qr-token');
        const tokenError = document.getElementById('token-error');
        const fallback = document.getElementById('manual-fallback');

        const torchWrap = document.querySelector('[data-torch-wrap]');
        const cameraWrap = document.querySelector('[data-camera-wrap]');
        const retryWrap = document.querySelector('[data-retry-wrap]');
        const torchButton = document.querySelector('[data-torch]');
        const switchButton = document.querySelector('[data-switch-camera]');
        const retryButton = document.querySelector('[data-retry]');

        const TONES = {
            idle: 'mt-4 text-center text-sm leading-relaxed text-slate-500 dark:text-slate-400',
            ok: 'mt-4 text-center text-sm leading-relaxed text-emerald-700 dark:text-emerald-400',
            error: 'mt-4 text-center text-sm leading-relaxed text-rose-600 dark:text-rose-400',
        };

        let reader = null;
        let cameras = [];
        let cameraIndex = 0;
        let track = null;
        let torchOn = false;
        let submitting = false;

        const say = (message, tone = 'idle') => {
            statusText.textContent = message;
            statusText.className = TONES[tone] || TONES.idle;
        };

        const show = (node, visible) => { if (node) node.hidden = !visible; };

        // Once the stream is live the frame drops its square placeholder and
        // simply hugs the camera's own aspect ratio.
        const setCameraState = (live) => {
            if (frame) frame.dataset.camera = live ? 'live' : 'off';
        };

        const shake = () => {
            if (!stage || stage.classList.contains('roomie-shake')) return;
            stage.classList.remove('roomie-shake');
            void stage.offsetWidth; // restart the animation on repeated failures
            stage.classList.add('roomie-shake');
        };

        // A QR on the door encodes a random token, not a URL.
        const tokenFromQR = (value) => {
            const raw = String(value || '').trim();
            return TOKEN_PATTERN.test(raw) ? raw : null;
        };

        /* ---------------- submission ---------------- */

        const submitToken = (token) => {
            if (submitting) return;
            submitting = true;
            tokenInput.value = token;
            form.submit();
        };

        const flashSuccess = (token) => {
            show(reticle, false);
            flash.hidden = false;
            flash.classList.add('roomie-flash');
            say('QR terbaca! Memproses check-in atau check-out…', 'ok');
            // The stream is intentionally left standing for the length of the
            // flash: tearing #reader down would collapse the frame under the
            // overlay. Navigation releases the camera a moment later.
            releaseCamera();
            // Long enough to be perceived, short enough not to feel like a stall.
            window.setTimeout(() => submitToken(token), 420);
        };

        const onDecoded = (value) => {
            if (submitting) return;

            const token = tokenFromQR(value);
            if (!token) {
                say('QR ini bukan milik Roomie. Gunakan QR yang ditempel di pintu kelas.', 'error');
                shake();
                return;
            }

            flashSuccess(token);
        };

        /* ---------------- camera ---------------- */

        const releaseCamera = () => {
            track = null;
            torchOn = false;
            show(torchWrap, false);
            if (torchButton) torchButton.setAttribute('aria-pressed', 'false');
        };

        const stopReader = async () => {
            if (!reader) return;
            try {
                if (reader.isScanning) await reader.stop();
            } catch (error) {
                // A failed stop leaves the stream in an unknown state; clearing
                // the element ourselves is what actually frees the camera.
                reader = null;
                document.getElementById('reader').innerHTML = '';
            }
        };

        const bindTrack = () => {
            const video = document.querySelector('#reader video');
            const stream = video?.srcObject;
            track = stream && stream.getVideoTracks ? stream.getVideoTracks()[0] : null;
        };

        const supportsTorch = () => {
            try {
                return !!(track && track.getCapabilities && track.getCapabilities().torch);
            } catch (error) {
                return false;
            }
        };

        const setTorch = async (on) => {
            if (!track) return;
            try {
                await track.applyConstraints({ advanced: [{ torch: on }] });
                torchOn = on;
                if (torchButton) {
                    torchButton.setAttribute('aria-pressed', String(on));
                    torchButton.dataset.torchActive = on ? 'true' : 'false';
                }
            } catch (error) {
                say('Senter tidak bisa dinyalakan di perangkat ini.', 'error');
            }
        };

        const config = {
            fps: 10,
            // Keep the scan box square and proportional to the viewport, so it
            // fills the reticle on a phone and a desktop alike.
            qrbox: (width, height) => Math.round(Math.min(width, height) * 0.66),
        };

        const cameraError = (error) => {
            const raw = String(error?.message || error || '');

            if (/not allowed|permission|denied/i.test(raw)) {
                return 'Izin kamera ditolak. Izinkan kamera untuk situs ini lewat ikon kunci di address bar, lalu tekan Coba lagi.';
            }
            if (/not found|no camera|overconstrained|requested device/i.test(raw)) {
                return 'Kamera tidak ditemukan atau sedang dipakai aplikasi lain. Tutup aplikasi kamera lalu tekan Coba lagi.';
            }
            if (/secure context|https|getUserMedia/i.test(raw)) {
                return 'Kamera hanya bisa dibuka lewat HTTPS. Akses Roomie melalui Cloudflare Tunnel, bukan alamat http.';
            }
            if (/already started|scan-reset/i.test(raw)) {
                return 'Kamera sedang dalam proses restarting. Tunggu sebentar lalu tekan Coba lagi.';
            }

            return 'Tidak dapat membuka kamera. ' + (raw || '');
        };

        const startCamera = async () => {
            if (typeof Html5Qrcode === 'undefined') {
                say('Komponen scanner tidak dimuat. Periksa koneksi internet lalu muat ulang halaman.', 'error');
                return;
            }

            say('Menyiapkan kamera…');
            show(retryWrap, false);
            show(torchWrap, false);
            show(cameraWrap, false);

            try {
                if (!reader) reader = new Html5Qrcode('reader');

                const available = await Html5Qrcode.getCameras();
                if (!available || !available.length) {
                    throw new Error('No camera found on this device.');
                }

                cameras = available;

                if (cameraIndex >= cameras.length) cameraIndex = 0;

                // Prefer the rear camera on phones.
                const rear = cameras.findIndex((camera) =>
                    /back|rear|environment|belakang/i.test(camera.label || '')
                );
                if (rear > -1) cameraIndex = rear;

                await stopReader();
                if (!reader) reader = new Html5Qrcode('reader');

                await reader.start(cameras[cameraIndex].id, config, onDecoded, () => {});

                bindTrack();

                setCameraState(true);
                show(reticle, true);
                show(torchWrap, supportsTorch());
                show(cameraWrap, cameras.length > 1);

                const label = cameras[cameraIndex].label || 'kamera';
                say(cameras.length > 1
                    ? 'Kamera siap. Arahkan ke QR di pintu kelas.'
                    : 'Kamera siap (' + label + '). Arahkan ke QR di pintu kelas.', 'ok');
            } catch (error) {
                console.warn(error);
                say(cameraError(error), 'error');
                show(reticle, false);
                setCameraState(false);
                show(retryWrap, true);
                shake();

                // If the camera is unusable, surface the manual path instead of
                // leaving the user at a dead end.
                if (fallback) fallback.open = true;
            }
        };

        const switchCamera = async () => {
            if (cameras.length < 2 || submitting) return;

            // Keep the square placeholder up while #reader is empty, otherwise
            // the frame collapses to zero height for the length of the restart.
            setCameraState(false);

            await stopReader();
            if (!reader) reader = new Html5Qrcode('reader');

            cameraIndex = (cameraIndex + 1) % cameras.length;
            say('Berpindah ke ' + (cameras[cameraIndex].label || 'kamera lain') + '…');

            try {
                await reader.start(cameras[cameraIndex].id, config, onDecoded, () => {});
                bindTrack();
                torchOn = false;
                if (torchButton) torchButton.setAttribute('aria-pressed', 'false');
                setCameraState(true);
                show(reticle, true);
                show(torchWrap, supportsTorch());
                say('Kamera siap. Arahkan ke QR di pintu kelas.', 'ok');
            } catch (error) {
                console.warn(error);
                say(cameraError(error), 'error');
                show(reticle, false);
                setCameraState(false);
                show(retryWrap, true);
                shake();
            }
        };

        torchButton?.addEventListener('click', () => setTorch(!torchOn));
        switchButton?.addEventListener('click', switchCamera);
        retryButton?.addEventListener('click', startCamera);

        /* ---------------- manual entry ---------------- */

        form?.addEventListener('submit', (event) => {
            event.preventDefault();

            const token = tokenFromQR(tokenInput.value);

            if (!token) {
                tokenError.hidden = false;
                tokenError.textContent = 'Token tidak terbaca. Isi 16–128 karakter huruf atau angka, tanpa spasi.';
                tokenInput.setAttribute('aria-invalid', 'true');
                tokenInput.focus();
                shake();
                return;
            }

            tokenError.hidden = true;
            tokenInput.removeAttribute('aria-invalid');
            submitToken(token);
        });

        tokenInput?.addEventListener('input', () => {
            tokenError.hidden = true;
            tokenInput.removeAttribute('aria-invalid');
        });

        /* ---------------- go ---------------- */

        startCamera();
    })();
</script>
</x-layouts.blank>