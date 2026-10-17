{{--
    Dashboard overview (DESIGN.md §5.1).

    Bento grid of room cards with a single search box, honest live state, and a
    notification centre derived from the same poll. Segmented filter chips were
    tried and removed — with only three rooms they cost more vertical space than
    they saved, and the cards already carry their own status badges and pulse.

    Nothing here adds an endpoint: everything comes from
    DashboardController::roomSnapshot() and /rooms/live.
--}}
<x-layouts.app title="Dashboard Ruangan · Roomie"
               description="Dashboard ketersediaan dan monitoring sensor ruang kelas Roomie.">

    <x-slot:topbar>
        <x-ui.notifications />
    </x-slot:topbar>

    <x-layouts.page-header title="Dashboard Ruangan"
                           description="Pantau ketersediaan kelas secara real-time. Penggunaan hanya dapat dimulai lewat verifikasi QR Code di lokasi.">
        <x-slot:actions>
            <span class="roomie-livechip" data-live="live">
                <span class="live-dot text-emerald-500" data-live-dot data-pulse="true" aria-hidden="true"></span>
                <span data-live-label>Menyambung&hellip;</span>
            </span>

            <x-ui.button type="button" variant="secondary" size="md"
                        icon="pause" data-live-toggle aria-pressed="false">
                <span data-live-toggle-label>Jeda</span>
            </x-ui.button>
        </x-slot:actions>
    </x-layouts.page-header>

    {{-- Ringkasan --}}
    <section class="mt-6 grid grid-cols-2 gap-3.5 lg:grid-cols-4" aria-labelledby="ringkasan-heading">
        <h2 id="ringkasan-heading" class="sr-only">Ringkasan ruangan</h2>

        <x-ui.stat label="Total Ruangan" :value="$totalRooms" caption="Terdaftar di kampus"
                   tone="neutral" stat="total" icon="dashboard" />
        <x-ui.stat label="Sesi Aktif" :value="$occupiedRooms" caption="Check-in QR berjalan"
                   tone="occupied" stat="occupied" icon="users" />
        <x-ui.stat label="Tersedia" :value="$availableRooms" caption="Siap dipakai sekarang"
                   tone="available" stat="available" icon="check-circle" />
        <x-ui.stat label="Sensor Stale" :value="$unknownRooms" caption="Laporan lebih dari 90 detik"
                   tone="attention" stat="unknown" icon="signal-off" />
    </section>

    {{-- Monitoring --}}
    <section class="mt-8" aria-labelledby="rooms-heading">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <div class="flex items-center gap-2.5">
                    <h2 id="rooms-heading" class="text-lg font-semibold tracking-tight text-slate-900 dark:text-slate-50">Monitoring Ruangan</h2>
                    <span class="rounded-full bg-slate-200/80 px-2 py-0.5 font-mono text-[11px] font-bold tabular-nums text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                          data-list-count>{{ count($rooms) }}</span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Status penggunaan dan laporan telemetri sensor ESP32.
                </p>
            </div>

            <div class="relative w-full sm:w-72">
                <label for="room-search" class="sr-only">Cari nama, kode, gedung, atau kapasitas</label>
                <x-ui.icon name="search" :size="16"
                           class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                <input id="room-search" type="search" autocomplete="off"
                       placeholder="Cari nama, kode, gedung..."
                       class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3.5 text-sm text-slate-900 shadow-tile outline-none transition-colors placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-roomie-surface dark:text-slate-100 dark:placeholder:text-slate-500">
            </div>
        </div>

        <div class="mt-4">
            <x-ui.alert tone="warning">
                Kolom status sensor adalah <strong class="font-semibold">indikasi perangkat ESP32</strong> (PIR &amp; door switch), bukan bukti fisik.
                Status <code class="rounded bg-amber-100 px-1 py-0.5 font-mono text-[11px] font-semibold text-amber-800 dark:bg-amber-400/20 dark:text-amber-200">UNKNOWN</code>
                berarti belum ada laporan atau sinyal terputus lebih dari 90 detik.
            </x-ui.alert>
        </div>

        @if ($rooms->isEmpty())
            <div class="mt-4">
                <x-ui.empty title="Belum ada ruangan"
                            body="Belum ada ruang kelas yang terdaftar dalam sistem. Hubungi administrator untuk menambahkan daftar ruangan."
                            icon="building" :level="2" />
            </div>
        @else
            <div id="room-list"
                 class="room-bento mt-4 grid grid-flow-dense gap-3.5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($rooms as $room)
                    <x-ui.room-card :room="$room" />
                @endforeach
            </div>

            <div id="room-no-match" hidden class="mt-4">
                <x-ui.empty title="Tidak ada ruangan yang cocok"
                            body="Coba kata kunci lain atau kosongkan kolom pencarian."
                            icon="search" :level="3" />
            </div>

            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500 dark:text-slate-400" data-list-summary>
                    Menampilkan <span class="font-semibold tabular-nums text-slate-800 dark:text-slate-200" data-list-count>{{ count($rooms) }}</span>
                    dari {{ $totalRooms }} ruangan
                </p>
                <x-ui.badge state="attention" data-unknown-badge>
                    <span data-unknown-count>{{ $unknownRooms }}</span> sensor perlu diperiksa
                </x-ui.badge>
            </div>
        @endif
    </section>

    <div id="room-modal-container"></div>

<script>
    (() => {
        const POLL_INTERVAL_MS = 10000;
        const SESSION_ENDING_SOON_MS = 15 * 60 * 1000;

        const roomList = document.getElementById('room-list');
        const searchInput = document.getElementById('room-search');
        const noMatch = document.getElementById('room-no-match');
        const countNodes = Array.from(document.querySelectorAll('[data-list-count]'));

        const detailUrlTemplate = @json(route('room.detail-modal', ['code' => '__ROOM_CODE__']));
        const liveUrl = @json(route('rooms.live'));

        const chip = document.querySelector('.roomie-livechip');
        const chipDot = document.querySelector('[data-live-dot]');
        const chipLabel = document.querySelector('[data-live-label]');
        const toggle = document.querySelector('[data-live-toggle]');
        const toggleLabel = document.querySelector('[data-live-toggle-label]');

        let paused = false;
        let failures = 0;
        let lastSuccess = null;
        let lastSummary = {
            total: {{ $totalRooms }},
            available: {{ $availableRooms }},
            occupied: {{ $occupiedRooms }},
            unknown: {{ $unknownRooms }},
        };
        let lastRooms = @json($rooms);

        /* ---------------- search ---------------- */

        const cardByCode = (code) =>
            Array.from(document.querySelectorAll('[data-room-code]'))
                .find((el) => el.dataset.roomCode === code);

        const applySearch = () => {
            if (!roomList) return;

            const term = (searchInput?.value || '').trim().toLowerCase();
            let visible = 0;

            Array.from(roomList.children).forEach((card) => {
                const show = !term || (card.dataset.search || '').includes(term);
                card.hidden = !show;
                if (show) visible += 1;
            });

            countNodes.forEach((node) => { node.textContent = visible; });
            if (noMatch) noMatch.hidden = visible !== 0;
        };

        searchInput?.addEventListener('input', applySearch);

        applySearch();

        /* ---------------- live state ---------------- */

        const setLiveState = (state) => {
            if (chip) chip.dataset.live = state;
            if (chipDot) chipDot.dataset.pulse = String(state === 'live');
        };

        const renderLiveState = () => {
            if (paused) {
                if (chipLabel) chipLabel.textContent = 'Diperbarui otomatis: dijeda';
                setLiveState('paused');
                return;
            }
            if (failures > 0) {
                if (chipLabel) chipLabel.textContent = 'Gagal memuat — mencoba lagi';
                setLiveState('error');
                return;
            }

            const seconds = lastSuccess ? Math.round((Date.now() - lastSuccess) / 1000) : null;
            if (chipLabel) {
                chipLabel.textContent = seconds === null
                    ? 'Menyambung…'
                    : 'Diperbarui ' + seconds + ' dtk lalu';
            }
            setLiveState('live');
        };

        renderLiveState();
        window.setInterval(renderLiveState, 1000);

        toggle?.addEventListener('click', () => {
            paused = !paused;
            toggle.setAttribute('aria-pressed', String(paused));
            if (toggleLabel) toggleLabel.textContent = paused ? 'Lanjutkan' : 'Jeda';
            renderLiveState();
            if (!paused) refresh();
        });

        /* ---------------- session block ----------------
           Mirrors the Blade markup in components/ui/room-card.blade.php. This
           is the one place the card is described twice: Blade paints the first
           render, this keeps it truthful when a session starts or ends between
           polls. Both sides write the same data-* hooks and the same
           timeline contract, and tickCountdowns() sweeps either. */

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        })[c]);

        const pad2 = (value) => String(value).padStart(2, '0');

        // Formatted manually rather than with toLocaleTimeString: the 'id-ID'
        // locale uses a dot as the time separator in some ICU builds, which
        // would disagree with the server-rendered H:i.
        const clockLabel = (iso) => {
            const d = new Date(iso);
            return Number.isNaN(d.getTime())
                ? '--:--'
                : pad2(d.getHours()) + ':' + pad2(d.getMinutes());
        };

        const sessionMarkup = (session) => `
            <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                <p class="min-w-0 truncate text-[15px] font-semibold text-rose-950 dark:text-rose-100">${escapeHtml(session.course)}</p>
                <p class="shrink-0 font-mono text-[11px] tabular-nums text-rose-700/80 dark:text-rose-300/80">${Number(session.sks) || 0} SKS</p>
            </div>
            <p class="mt-0.5 truncate text-xs text-rose-900/70 dark:text-rose-100/70">
                ${session.mine ? 'Sesi kamu' : 'Dipakai ' + escapeHtml(session.owner || 'pengguna lain')}
            </p>
            <div class="mt-3.5 border-t border-rose-200/70 pt-3.5 dark:border-rose-500/20">
                <div class="mb-2 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">Kemajuan sesi</span>
                    <p class="shrink-0 font-mono text-xs font-semibold tabular-nums text-slate-500 dark:text-slate-400"
                       data-countdown="compact" data-end-at="${escapeHtml(session.end)}">—</p>
                </div>
                <div class="timeline__track" role="progressbar" data-start-at="${new Date(session.start).getTime()}"
                     aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="Kemajuan penggunaan ruangan">
                    <div class="timeline__fill" data-countdown-fill style="width: 0%"></div>
                </div>
                <div class="mt-1.5 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 font-mono text-[11px] tabular-nums text-slate-500 dark:text-slate-400">
                    <span>${clockLabel(session.start)}</span>
                    <span class="font-sans text-xs">${session.mine ? 'Sesi kamu' : 'Sesi berjalan'} · batas ${clockLabel(session.end)}</span>
                    <span>${clockLabel(session.end)}</span>
                </div>
            </div>`;

        const paintSession = (card, session) => {
            const block = card.querySelector('[data-session-block]');
            if (!block) return;

            block.hidden = !session;
            if (session) {
                block.innerHTML = sessionMarkup(session);
                block.style.setProperty('--timeline-fill', '#E11D48');
            }

            const label = card.querySelector('[data-scan-label]');
            if (label) label.textContent = session && session.mine ? 'Check-out' : 'Scan';
        };

        /* ---------------- notifications ---------------- */

        const noticeList = document.querySelector('[data-notification-list]');
        const noticeCount = document.querySelector('[data-notification-count]');
        const noticeMeta = document.querySelector('[data-notification-meta]');
        const noticeTrigger = document.querySelector('[data-notifications] [data-menu-trigger]');

        let lastNotices = [];

        // The badge count is visible text, so WCAG "Label in Name" requires it to
        // be part of the button's accessible name too.
        const paintNoticeCount = (count) => {
            if (noticeTrigger) {
                noticeTrigger.setAttribute(
                    'aria-label',
                    count > 0 ? 'Pusat notifikasi, ' + count + ' catatan' : 'Pusat notifikasi'
                );
            }
            if (noticeCount) {
                noticeCount.classList.toggle('hidden', count === 0);
                noticeCount.textContent = count > 9 ? '9+' : count ? String(count) : '';
            }
        };

        // Script-rendered rows cannot use the Blade icon component, so they get
        // a small local glyph set. Page chrome still goes through the component.
        const NOTICE_GLYPHS = {
            info: ['<circle cx="12" cy="12" r="9"/>', '<path d="M12 11v5"/>', '<path d="M12 8h.01"/>'],
            warning: [
                '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>',
                '<path d="M12 9v4"/>', '<path d="M12 17h.01"/>',
            ],
            danger: ['<circle cx="12" cy="12" r="9"/>', '<path d="m9.5 9.5 5 5M14.5 9.5l-5 5"/>'],
        };

        const noticeGlyph = (tone) => `<svg viewBox="0 0 24 24" width="16" height="16" fill="none"
                stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">${(NOTICE_GLYPHS[tone] || []).join('')}</svg>`;

        const noticeRow = (tone, text) => `
            <div class="roomie-notice" data-tone="${tone}" tabindex="0">
                <span class="roomie-notice__icon" aria-hidden="true">${noticeGlyph(tone)}</span>
                <span class="min-w-0">${text}</span>
            </div>`;

        const buildNotices = (rooms) => {
            const notices = [];
            const now = Date.now();

            rooms.forEach((room) => {
                if (room.session && room.session.mine) {
                    notices.push({
                        tone: 'info',
                        code: room.code,
                        text: `<strong>Kamu sedang memakai ${escapeHtml(room.code)}</strong> — scan QR yang sama untuk check-out.`,
                    });
                }
            });

            rooms.forEach((room) => {
                const remaining = room.session ? new Date(room.session.end).getTime() - now : null;
                if (remaining !== null && remaining > 0 && remaining <= SESSION_ENDING_SOON_MS) {
                    notices.push({
                        tone: 'warning',
                        code: room.code,
                        text: `<strong>${escapeHtml(room.code)}</strong> — batas penggunaan tinggal ${Math.max(Math.round(remaining / 60000), 1)} menit lagi.`,
                    });
                }
            });

            const stale = rooms.filter((room) => room.sensor_fresh === false);
            if (stale.length) {
                notices.push({
                    tone: 'warning',
                    code: null,
                    text: `<strong>${stale.length} ruang tidak melapor sensor</strong> — laporan terakhir lebih dari 90 detik lalu.`,
                });
            }

            if (!noticeList) {
                paintNoticeCount(notices.length);
                return notices;
            }

            noticeMeta.textContent = notices.length + ' catatan';

            if (!notices.length) {
                noticeList.innerHTML =
                    '<p class="roomie-notice roomie-notice--empty">Tidak ada catatan. Semua ruang dalam kondisi normal.</p>';
                paintNoticeCount(0);
                return notices;
            }

            noticeList.innerHTML = notices.map((n) => noticeRow(n.tone, n.text)).join('');
            paintNoticeCount(notices.length);
            return notices;
        };

        // Each notice is actionable: it opens that room's detail. Notices with
        // no single room attached (e.g. the sensor roll-up) stay inert.
        const openNotice = (index) => {
            const code = (lastNotices[index] || {}).code;
            if (code) openRoom(code);
        };

        noticeList?.addEventListener('click', (event) => {
            const row = event.target.closest('.roomie-notice');
            if (row) openNotice(Array.from(noticeList.children).indexOf(row));
        });

        noticeList?.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            const row = event.target.closest('.roomie-notice');
            if (!row) return;
            event.preventDefault();
            openNotice(Array.from(noticeList.children).indexOf(row));
        });

        /* ---------------- live room data ---------------- */

        const applyRoomSnapshot = (item) => {
            const card = cardByCode(item.code);
            if (!card) return;

            const occupied = item.availability === 'occupied';

            card.dataset.availability = item.availability;
            card.dataset.occupied = String(occupied);
            card.dataset.bento = occupied ? 'wide' : 'base';
            card.dataset.sensorFresh = item.sensor_fresh ? '1' : '0';

            const badge = card.querySelector('[data-availability-badge]');
            const label = card.querySelector('[data-availability]');
            if (badge && label) {
                badge.dataset.state = occupied ? 'occupied' : 'available';
                label.textContent = occupied ? 'Dipakai' : 'Tersedia';
            }

            const sensorBadge = card.querySelector('[data-sensor-badge]');
            const sensor = card.querySelector('[data-sensor]');
            if (sensorBadge && sensor) {
                sensorBadge.dataset.state = item.sensor;
                sensor.textContent = item.sensor;
            }

            const pulse = card.querySelector('[data-room-pulse]');
            if (pulse) {
                pulse.dataset.pulse = String(!!item.sensor_fresh);
                pulse.classList.toggle('text-emerald-500', !!item.sensor_fresh);
                pulse.classList.toggle('text-slate-300', !item.sensor_fresh);
            }

            const updated = card.querySelector('[data-updated]');
            if (updated) updated.textContent = item.updated;

            paintSession(card, item.session);
        };

        async function refresh() {
            if (paused || document.hidden) return;

            try {
                const response = await fetch(liveUrl, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store',
                });
                if (!response.ok) throw new Error('HTTP ' + response.status);

                const snapshot = await response.json();
                const rooms = snapshot.rooms || [];
                rooms.forEach(applyRoomSnapshot);

                for (const [key, value] of Object.entries(snapshot.summary || {})) {
                    const node = document.querySelector('[data-stat="' + key + '"]');
                    if (node) node.textContent = value;
                }

                const unknownBadge = document.querySelector('[data-unknown-badge]');
                const unknownCount = document.querySelector('[data-unknown-count]');
                if (unknownBadge && unknownCount && snapshot.summary) {
                    unknownBadge.dataset.state = snapshot.summary.unknown > 0 ? 'attention' : 'unknown';
                    unknownCount.textContent = snapshot.summary.unknown;
                }

                if (lastSummary && snapshot.summary) {
                    const changes = [];
                    if (lastSummary.occupied !== snapshot.summary.occupied) {
                        changes.push(snapshot.summary.occupied + ' ruangan sedang dipakai');
                    }
                    if (lastSummary.available !== snapshot.summary.available) {
                        changes.push(snapshot.summary.available + ' ruangan tersedia');
                    }
                    if (changes.length) window.roomieAnnounce('Status ruangan diperbarui. ' + changes.join(', ') + '.');
                }
                lastSummary = snapshot.summary || lastSummary;

                lastRooms = rooms;
                lastNotices = buildNotices(rooms);

                failures = 0;
                lastSuccess = Date.now();
                renderLiveState();
            } catch (error) {
                failures += 1;
                renderLiveState();
                console.warn('Monitoring sementara tidak tersedia:', error);
            }
        }

        window.setInterval(refresh, POLL_INTERVAL_MS);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) refresh();
        });

        /* ---------------- room detail dialog ---------------- */

        const container = document.getElementById('room-modal-container');

        async function openRoom(code) {
            if (!container) return;

            try {
                const response = await fetch(
                    detailUrlTemplate.replace('__ROOM_CODE__', encodeURIComponent(code)),
                    { headers: { 'Accept': 'text/html' } }
                );
                if (!response.ok) throw new Error('HTTP ' + response.status);

                container.innerHTML = await response.text();
                window.roomieOpenDialog('room-modal');
            } catch (error) {
                console.error(error);
                window.roomieToast('danger', 'Detail ruangan gagal dimuat. Silakan coba lagi.');
            }
        }

        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-room-open]');
            if (trigger) openRoom(trigger.dataset.roomOpen);
        });

        /* ---------------- countdowns ---------------- */

        const pad = pad2;

        const tickCountdowns = () => {
            const now = Date.now();

            document.querySelectorAll('[data-end-at]').forEach((node) => {
                const distance = new Date(node.dataset.endAt).getTime() - now;
                const expired = distance <= 0;
                const totalSeconds = Math.floor(Math.abs(distance) / 1000);

                if (node.dataset.countdown === 'compact') {
                    node.textContent = expired
                        ? 'habis'
                        : totalSeconds >= 3600
                            ? Math.floor(totalSeconds / 3600) + 'j ' + Math.floor((totalSeconds % 3600) / 60) + 'm'
                            : Math.max(Math.floor(totalSeconds / 60), 1) + 'm';
                    return;
                }

                node.textContent = expired
                    ? 'WAKTU HABIS'
                    : [Math.floor(totalSeconds / 3600), Math.floor((totalSeconds % 3600) / 60), totalSeconds % 60]
                        .map(pad).join(':');

                const track = node.closest('.timeline__track') || node.parentElement?.querySelector('.timeline__track');
                const fill = track?.querySelector('[data-countdown-fill]');
                if (fill && track) {
                    const start = Number(track.dataset.startAt ?? now);
                    const end = new Date(node.dataset.endAt).getTime();
                    const span = Math.max(end - start, 1);
                    const percent = Math.min(Math.max(Math.round(((now - start) / span) * 100), 0), 100);
                    fill.style.width = percent + '%';
                    track.setAttribute('aria-valuenow', String(percent));
                }
            });
        };

        let ticker = null;

        // Started once, not per modal open: the dialog markup is injected later,
        // so a per-open ticker would never see it. The sweep is one
        // querySelectorAll.
        const startTickers = () => {
            if (ticker) window.clearInterval(ticker);
            tickCountdowns();
            ticker = window.setInterval(tickCountdowns, 1000);
        };

        startTickers();
        lastNotices = buildNotices(lastRooms);
        refresh();
    })();
</script>
</x-layouts.app>