<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f5f7fb">
    <title>Dashboard Roomie — Monitoring Ruangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        roomie: {
                            ink: '#17243a',
                            blue: '#2563eb',
                            canvas: '#f5f7fb',
                            line: '#e7ebf2'
                        }
                    },
                    boxShadow: {
                        soft: '0 10px 30px -18px rgba(23, 36, 58, .20)'
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen bg-roomie-canvas font-sans text-roomie-ink antialiased">

    <header class="border-b border-roomie-line bg-white">
        <nav class="mx-auto flex h-[68px] max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8" aria-label="Navigasi utama">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5" aria-label="Roomie, halaman utama">
                <span class="flex size-9 items-center justify-center rounded-xl bg-roomie-blue text-white shadow-sm">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true">
                        <path d="M3.75 10.25 12 3.75l8.25 6.5v9.1a1 1 0 0 1-1 1h-5.1v-6.1h-4.3v6.1h-5.1a1 1 0 0 1-1-1v-9.1Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="text-xl font-bold tracking-tight">Roomie</span>
            </a>

            <details class="group relative">
                <summary class="flex cursor-pointer list-none items-center gap-2.5 rounded-xl p-1.5 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-roomie-blue">
                    <span class="hidden text-right sm:block">
                        <span class="block text-sm font-semibold leading-5">{{ auth()->user()->name }}</span>
                        <span class="block text-xs text-slate-500">{{ ucfirst(auth()->user()->role) }}</span>
                    </span>
                    <span class="flex size-9 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-800">{{ strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}</span>
                    <svg viewBox="0 0 20 20" class="size-4 text-slate-500 transition group-open:rotate-180" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.22 7.47a.75.75 0 0 1 1.06 0L10 11.19l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                    </svg>
                </summary>
                <div class="absolute right-0 top-full z-20 mt-2 w-52 rounded-xl border border-roomie-line bg-white p-1.5 shadow-soft">
                    <div class="border-b border-roomie-line px-3 py-2 sm:hidden">
                        <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                    <a href="{{ route('reservation.history') }}" class="block rounded-lg px-3 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50">Riwayat Penggunaan</a>
                    @if(auth()->user()->role === 'admin')<a href="{{ route('rooms.qr-labels') }}" class="block rounded-lg px-3 py-2.5 text-sm text-blue-700 hover:bg-blue-50">Cetak QR Ruangan</a>@endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-lg px-3 py-2.5 text-left text-sm font-medium text-rose-600 transition hover:bg-rose-50">Keluar</button>
                    </form>
                </div>
            </details>
        </nav>
    </header>

    <main id="dashboard" class="mx-auto max-w-7xl px-4 pb-12 pt-6 sm:px-6 sm:pt-9 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 sm:mb-8 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-1 text-sm font-semibold text-roomie-blue">{{ now()->translatedFormat('l, d F Y') }}</p>
                <h1 class="text-2xl font-bold tracking-tight text-roomie-ink sm:text-3xl">Dashboard Ruangan</h1>
                <p class="mt-1.5 text-sm text-slate-500">Pantau ketersediaan kelas. Penggunaan hanya dapat dimulai melalui scan QR di kelas.</p>
            </div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                <span class="relative flex size-2.5">
                    <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-50"></span>
                    <span class="relative inline-flex size-2.5 rounded-full bg-emerald-500"></span>
                </span>
                Status ruangan diperbarui berkala
            </div>
        </div>

        <section aria-labelledby="scan-heading" class="mb-7 overflow-hidden rounded-2xl bg-roomie-blue shadow-soft">
            <div class="flex flex-col gap-5 px-5 py-6 text-white sm:flex-row sm:items-center sm:justify-between sm:px-8 sm:py-7">
                <div class="flex items-center gap-4">
                    <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                        <svg viewBox="0 0 24 24" class="size-7" fill="none" aria-hidden="true">
                            <path d="M4 8V5a1 1 0 0 1 1-1h3M16 4h3a1 1 0 0 1 1 1v3M20 16v3a1 1 0 0 1-1 1h-3M8 20H5a1 1 0 0 1-1-1v-3M8 8h8v8H8zM4 12h2m12 0h2M12 4v2m0 12v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <div>
                        <h2 id="scan-heading" class="text-lg font-bold sm:text-xl">Check-in langsung dari kelas</h2>
                        <p class="mt-1 max-w-lg text-sm leading-5 text-blue-100">Datang ke depan kelas dan scan QR untuk check-in. Scan lagi saat selesai untuk check-out.</p>
                    </div>
                </div>
                <button type="button" onclick="window.location.href = @js(route('scanner'))" class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-roomie-blue shadow-sm transition hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-white/40 sm:w-auto">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true">
                        <path d="M4 8V5a1 1 0 0 1 1-1h3m8 0h3a1 1 0 0 1 1 1v3m0 8v3a1 1 0 0 1-1 1h-3m-8 0H5a1 1 0 0 1-1-1v-3M8 8h8v8H8z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Scan QR Ruangan
                </button>
            </div>
        </section>

        @if(session('success'))<div role="status" class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>@endif
        @if(session('error'))<div role="alert" class="mb-5 rounded-xl bg-red-50 p-4 text-sm font-semibold text-red-800">{{ session('error') }}</div>@endif
        <div class="mb-5 flex justify-end"><a href="{{ route('reservation.history') }}" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-bold text-blue-700 hover:bg-blue-100">Lihat riwayat penggunaan →</a></div>
        <section aria-label="Ringkasan ruangan" class="mb-9 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            @php
                $stats = [
                    ['label' => 'Total Ruangan', 'value' => $totalRooms, 'caption' => 'Terdaftar di kampus', 'tone' => 'blue', 'icon' => 'grid'],
                    ['label' => 'Ruangan Tersedia', 'value' => $availableRooms, 'caption' => 'Siap digunakan', 'tone' => 'green', 'icon' => 'check'],
                    ['label' => 'Sedang Terpakai', 'value' => $occupiedRooms, 'caption' => 'Check-in QR aktif', 'tone' => 'red', 'icon' => 'users'],
                    ['label' => 'Perlu Diperiksa', 'value' => $unknownRooms, 'caption' => 'Status sensor tidak diketahui', 'tone' => 'amber', 'icon' => 'alert'],
                ];
            @endphp
            @foreach ($stats as $stat)
                @php
                    $toneClasses = [
                        'blue' => ['icon' => 'bg-blue-50 text-blue-700', 'value' => 'text-roomie-ink'],
                        'green' => ['icon' => 'bg-emerald-50 text-emerald-700', 'value' => 'text-emerald-700'],
                        'red' => ['icon' => 'bg-rose-50 text-rose-700', 'value' => 'text-rose-700'],
                        'amber' => ['icon' => 'bg-amber-50 text-amber-700', 'value' => 'text-amber-700'],
                    ];
                @endphp
                <article class="rounded-2xl border border-roomie-line bg-white p-4 shadow-soft sm:p-5">
                    <div class="mb-4 flex items-start justify-between gap-2">
                        <p class="text-xs font-semibold leading-5 text-slate-500 sm:text-sm">{{ $stat['label'] }}</p>
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $toneClasses[$stat['tone']]['icon'] }}">
                            @if ($stat['icon'] === 'grid')
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                            @elseif ($stat['icon'] === 'check')
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @elseif ($stat['icon'] === 'users')
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20m6-9a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm5-6.6a3.5 3.5 0 0 1 0 6.8m2 4.1a3.5 3.5 0 0 1 3 3.4V20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M12 8v4m0 4h.01M10.3 4.9 2.8 18a1.5 1.5 0 0 0 1.3 2.2h15.8a1.5 1.5 0 0 0 1.3-2.2L13.7 4.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @endif
                        </span>
                    </div>
                    <p data-stat="{{ ['Total Ruangan'=>'total','Ruangan Tersedia'=>'available','Sedang Terpakai'=>'occupied','Perlu Diperiksa'=>'unknown'][$stat['label']] }}" class="text-3xl font-bold leading-none {{ $toneClasses[$stat['tone']]['value'] }}">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-[11px] leading-4 text-slate-400 sm:text-xs">{{ $stat['caption'] }}</p>
                </article>
            @endforeach
        </section>

        <section id="rooms" aria-labelledby="rooms-heading">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="rooms-heading" class="text-lg font-bold text-roomie-ink sm:text-xl">Monitoring Ruangan</h2>
                    <p class="mt-1 text-sm text-slate-500">Status sensor dan ketersediaan terkini.</p>
                </div>
                <label class="sr-only" for="room-search">Cari nama atau kode ruangan</label>
                <div class="relative w-full sm:w-64">
                    <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <input id="room-search" type="search" placeholder="Cari ruangan..." class="h-10 w-full rounded-xl border border-roomie-line bg-white pl-9 pr-3 text-sm text-roomie-ink outline-none placeholder:text-slate-400 focus:border-roomie-blue focus:ring-2 focus:ring-blue-100">
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-roomie-line bg-white shadow-soft">
                <div class="hidden grid-cols-[minmax(0,1.5fr)_minmax(130px,.85fr)_minmax(130px,.85fr)_minmax(150px,.9fr)_auto] gap-4 border-b border-roomie-line bg-slate-50/80 px-5 py-3 text-[11px] font-bold uppercase tracking-wide text-slate-400 md:grid">
                    <span>Ruangan</span><span>Ketersediaan</span><span>Status sensor</span><span>Pembaruan</span><span class="text-right">Aksi</span>
                </div>
                <div class="divide-y divide-roomie-line">
                    @foreach ($rooms as $room)
                        @php
                            $availability = [
                                'available' => ['label' => 'Tersedia', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/15', 'dot' => 'bg-emerald-500'],
                                'occupied' => ['label' => 'Sedang dipakai', 'class' => 'bg-rose-50 text-rose-700 ring-rose-600/15', 'dot' => 'bg-rose-500'],
                                'unknown' => ['label' => 'Perlu diperiksa', 'class' => 'bg-amber-50 text-amber-800 ring-amber-600/20', 'dot' => 'bg-amber-500'],
                            ][$room['availability']];
                            $sensorClass = $room['sensor'] === 'TERTUTUP'
                                ? 'bg-emerald-50 text-emerald-700'
                                : ($room['sensor'] === 'TERBUKA' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600');
                        @endphp
                        <article data-room-code="{{ $room['code'] }}" class="grid gap-3 px-4 py-4 transition hover:bg-slate-50/60 sm:px-5 md:grid-cols-[minmax(0,1.5fr)_minmax(130px,.85fr)_minmax(130px,.85fr)_minmax(150px,.9fr)_auto] md:items-center md:gap-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                                    <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true"><path d="M3.75 10.25 12 3.75l8.25 6.5v9.1a1 1 0 0 1-1 1h-5.1v-6.1h-4.3v6.1h-5.1a1 1 0 0 1-1-1v-9.1Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-roomie-ink">{{ $room['name'] }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $room['code'] }} <span class="px-0.5 text-slate-300">·</span> {{ $room['building'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-3 md:block">
                                <span class="text-xs font-medium text-slate-400 md:hidden">Ketersediaan</span>
                                <span data-availability class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $availability['class'] }}">
                                    <span class="size-1.5 rounded-full {{ $availability['dot'] }}"></span>{{ $availability['label'] }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between gap-3 md:block">
                                <span class="text-xs font-medium text-slate-400 md:hidden">Status sensor</span>
                                <span data-sensor class="inline-flex rounded-md px-2 py-1 font-mono text-[10px] font-bold tracking-wide {{ $sensorClass }}">{{ $room['sensor'] }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 md:block">
                                <span class="text-xs font-medium text-slate-400 md:hidden">Pembaruan sensor</span>
                                <span data-updated class="text-xs text-slate-500">{{ $room['updated'] }}</span>
                            </div>
                            <div class="flex justify-end border-t border-slate-100 pt-3 md:border-0 md:pt-0">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('scanner') }}" class="inline-flex min-h-9 items-center justify-center rounded-lg bg-roomie-blue px-3.5 text-xs font-semibold text-white hover:bg-blue-700">Scan QR</a>
                                    <button onclick="openModal(@js($room['code']))" type="button" class="inline-flex min-h-9 items-center justify-center rounded-lg border border-roomie-line bg-white px-3.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Detail</button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="flex flex-col gap-2 border-t border-roomie-line bg-slate-50/60 px-4 py-3 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <span>Menampilkan {{ count($rooms) }} dari {{ $totalRooms }} ruangan</span>
                    <span class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-amber-500"></span><span data-unknown-count>{{ $unknownRooms }}</span> sensor perlu diperiksa</span>
                </div>
            </div>
        </section>
    </main>
    <div id="room-modal-container"></div>
    <script>
        async function refreshRooms() {
            try {
                const response = await fetch(@json(route('rooms.live')), {headers: {'Accept':'application/json'}, cache: 'no-store'});
                if (!response.ok) return;
                const snapshot = await response.json();
                for (const item of snapshot.rooms) {
                    // Cari menggunakan atribut dan bandingkan nilainya (tidak memakai selector token tidak aman).
                    const row = Array.from(document.querySelectorAll('[data-room-code]')).find(el => el.dataset.roomCode === item.code);
                    if (!row) continue;
                    const availability = row.querySelector('[data-availability]');
                    const sensor = row.querySelector('[data-sensor]');
                    const updated = row.querySelector('[data-updated]');
                    availability.textContent = item.availability === 'occupied' ? '● Sedang dipakai' : '● Tersedia';
                    availability.className = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset ' + (item.availability === 'occupied' ? 'bg-rose-50 text-rose-700 ring-rose-600/15' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/15');
                    sensor.textContent = item.sensor;
                    sensor.className = 'inline-flex rounded-md px-2 py-1 font-mono text-[10px] font-bold tracking-wide ' + (item.sensor === 'TERBUKA' ? 'bg-rose-50 text-rose-700' : item.sensor === 'TERTUTUP' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600');
                    updated.textContent = item.updated;
                }
                for (const [key, val] of Object.entries(snapshot.summary)) {
                    const cell = document.querySelector('[data-stat="' + key + '"]');
                    if (cell) cell.textContent = val;
                }
                const unknown = document.querySelector('[data-unknown-count]');
                if (unknown) unknown.textContent = snapshot.summary.unknown;
            } catch (error) {
                console.warn('Monitoring sementara tidak tersedia:', error);
            }
        }
        setInterval(() => { if (!document.hidden) refreshRooms(); }, 10000);

        let countdownInterval;

        async function openModal(roomCode) {
            const modalContainer = document.getElementById('room-modal-container');
            const detailUrlTemplate = @json(route('room.detail-modal', ['code' => '__ROOM_CODE__']));

            try {
                const response = await fetch(detailUrlTemplate.replace('__ROOM_CODE__', encodeURIComponent(roomCode)));
                if (!response.ok) throw new Error('Detail ruangan gagal dimuat.');

                modalContainer.innerHTML = await response.text();
                const modal = document.getElementById('room-modal');
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                startCountdown();
            } catch (error) {
                console.error(error);
                alert('Detail ruangan gagal dimuat. Coba lagi.');
            }
        }

        function startCountdown() {
            const timerDisplay = document.getElementById('countdown-timer');
            if (!timerDisplay) return;

            clearInterval(countdownInterval);
            const endTime = new Date(timerDisplay.dataset.endAt).getTime();

            function updateCountdown() {
                const distance = endTime - Date.now();
                if (distance <= 0) {
                    timerDisplay.textContent = 'WAKTU HABIS';
                    clearInterval(countdownInterval);
                    return;
                }

                const hours = Math.floor(distance / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                timerDisplay.textContent = [hours, minutes, seconds]
                    .map(value => String(value).padStart(2, '0'))
                    .join(':');
            }

            updateCountdown();
            countdownInterval = setInterval(updateCountdown, 1000);
        }

        function closeModal() {
            const modal = document.getElementById('room-modal');

            if (!modal) return;

            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            clearInterval(countdownInterval);
        }
    </script>
</body>
</html>