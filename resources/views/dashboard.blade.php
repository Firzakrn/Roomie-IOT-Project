<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#F8FAFC">
    <meta name="description" content="Dashboard ketersediaan dan monitoring ruang kelas Roomie.">
    <title>Dashboard Roomie — Monitoring Ruangan</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        roomie: {
                            ink: '#0F172A',
                            'ink-muted': '#475569',
                            'ink-weak': '#94A3B8',
                            primary: '#4F46E5',
                            'primary-hover': '#4338CA',
                            'primary-subtle': '#EEF2FF',
                            canvas: '#F8FAFC',
                            surface: '#FFFFFF',
                            line: '#E2E8F0',
                            available: '#10B981',
                            occupied: '#EF4444',
                            attention: '#F59E0B',
                            unknown: '#64748B',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                    },
                    boxShadow: {
                        card: '0 1px 3px 0 rgba(15, 23, 42, 0.03), 0 10px 15px -3px rgba(15, 23, 42, 0.05)',
                        'card-hover': '0 10px 25px -5px rgba(79, 70, 229, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04)',
                        glow: '0 0 20px -3px rgba(79, 70, 229, 0.3)',
                    },
                }
            }
        };
    </script>

    <x-ui.styles />
</head>

<body class="min-h-screen bg-slate-50/80 font-sans text-slate-900 antialiased selection:bg-indigo-500 selection:text-white">

<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:rounded-xl focus:bg-indigo-600 focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white focus:shadow-lg focus:outline-none">
    Lewati ke konten utama
</a>

{{-- ───────────────────────── App Shell / Header ───────────────────────── --}}
<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-md supports-[backdrop-filter]:bg-white/70 transition-all">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4">
            <a href="{{ route('dashboard') }}" class="group flex min-w-0 items-center gap-3 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-600/20 transition-transform group-hover:scale-105 active:scale-95">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true">
                        <path d="M3.75 10.25 12 3.75l8.25 6.5v9.1a1 1 0 0 1-1 1h-5.1v-6.1h-4.3v6.1h-5.1a1 1 0 0 1-1-1v-9.1Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="flex flex-col">
                    <span class="text-lg font-bold tracking-tight text-slate-900 group-hover:text-indigo-600 transition-colors">Roomie</span>
                    <span class="hidden text-[10px] font-semibold uppercase tracking-wider text-slate-400 sm:inline">Access Hub</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1.5 rounded-xl border border-slate-200/80 bg-slate-100/60 p-1 md:flex" aria-label="Navigasi utama">
                <a href="{{ route('dashboard') }}" class="nav-pill flex items-center gap-2 rounded-lg bg-white px-3.5 py-1.5 text-xs font-bold text-indigo-600 shadow-sm transition-all" aria-current="page">
                    <span class="size-1.5 rounded-full bg-indigo-600"></span>
                    Dashboard
                </a>
                <a href="{{ route('scanner') }}" class="nav-pill flex items-center gap-2 rounded-lg px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-white/60 transition-all">
                    Scan QR
                </a>
                <a href="{{ route('reservation.history') }}" class="nav-pill flex items-center gap-2 rounded-lg px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-white/60 transition-all">
                    Riwayat
                </a>
            </nav>

            <details class="group relative shrink-0">
                <summary class="flex min-h-[44px] cursor-pointer list-none items-center gap-2.5 rounded-xl border border-transparent p-1.5 transition-all hover:border-slate-200 hover:bg-slate-100/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">
                    <span class="hidden text-right sm:block">
                        <span class="block text-xs font-bold text-slate-900 leading-tight">{{ auth()->user()->name }}</span>
                        <span class="block text-[11px] font-medium text-slate-500 leading-tight">{{ auth()->user()->role === 'admin' ? 'Administrator' : 'Mahasiswa' }}</span>
                    </span>
                    <span class="flex size-9 items-center justify-center rounded-xl bg-indigo-50 font-mono text-xs font-bold text-indigo-600 ring-1 ring-indigo-500/20 shadow-sm">
                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}
                    </span>
                    <svg viewBox="0 0 20 20" class="size-4 text-slate-400 transition-transform duration-200 group-open:rotate-180" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.22 7.47a.75.75 0 0 1 1.06 0L10 11.19l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                    </svg>
                </summary>
                <div class="absolute right-0 top-full z-20 mt-2 w-60 rounded-2xl border border-slate-200/90 bg-white/95 p-1.5 shadow-xl backdrop-blur-lg animate-in fade-in slide-in-from-top-2 duration-150">
                    <div class="border-b border-slate-100 px-3 py-2.5 sm:hidden">
                        <p class="text-xs font-bold text-slate-900">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] font-medium text-slate-500">{{ auth()->user()->role === 'admin' ? 'Administrator' : 'Mahasiswa' }}</p>
                    </div>
                    <a href="{{ route('reservation.history') }}" class="flex min-h-[40px] items-center gap-2 rounded-xl px-3 text-xs font-medium text-slate-600 transition-all hover:bg-slate-100/80 hover:text-slate-900">
                        <svg viewBox="0 0 24 24" class="size-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        Riwayat Penggunaan
                    </a>
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('rooms.qr-labels') }}" class="flex min-h-[40px] items-center gap-2 rounded-xl px-3 text-xs font-semibold text-indigo-600 transition-all hover:bg-indigo-50">
                            <svg viewBox="0 0 24 24" class="size-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2m-4 0H10v4h4v-4Z"/></svg>
                            Cetak QR Ruangan
                        </a>
                    @endif
                    <div class="my-1 border-t border-slate-100"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex min-h-[40px] w-full items-center gap-2 rounded-xl px-3 text-left text-xs font-semibold text-rose-600 transition-all hover:bg-rose-50">
                            <svg viewBox="0 0 24 24" class="size-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H9"/></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </details>
        </div>

        <nav class="-mx-1 flex gap-1 overflow-x-auto pb-3 md:hidden" aria-label="Navigasi utama (mobile)">
            <a href="{{ route('dashboard') }}" class="nav-pill rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm" aria-current="page">Dashboard</a>
            <a href="{{ route('scanner') }}" class="nav-pill rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200">Scan QR</a>
            <a href="{{ route('reservation.history') }}" class="nav-pill rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200">Riwayat</a>
        </nav>
    </div>
</header>

<main id="main" class="mx-auto max-w-7xl px-4 pb-16 pt-6 sm:px-6 sm:pt-8 lg:px-8">

    {{-- ───────────────────────── Page Header ───────────────────────── --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50/80 px-3 py-1 text-[11px] font-bold tracking-wide uppercase text-indigo-700">
                <svg viewBox="0 0 24 24" class="size-3.5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                {{ now()->locale('id')->translatedFormat('l, d F Y') }}
            </div>
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Dashboard Ruangan</h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 max-w-xl">
                Pantau ketersediaan kelas secara <span class="font-semibold text-slate-700">real-time</span>. Penggunaan hanya dapat dimulai melalui verifikasi QR Code di lokasi.
            </p>
        </div>

        <div class="inline-flex shrink-0 items-center gap-2.5 rounded-full border border-slate-200/80 bg-white px-3.5 py-2 shadow-sm backdrop-blur-sm sm:self-auto">
            <span class="relative flex size-2.5">
                <span class="live-dot absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex size-2.5 rounded-full bg-emerald-500"></span>
            </span>
            <span class="text-xs font-semibold text-slate-600">Data usage diperbarui otomatis</span>
        </div>
    </div>

    {{-- ───────────────────────── Quick Actions (Bento Banner) ───────────────────────── --}}
    <section class="mt-6" aria-labelledby="aksi-cepat-heading">
        <h2 id="aksi-cepat-heading" class="sr-only">Aksi cepat</h2>
        <div class="relative overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 p-5 sm:p-6 shadow-xl text-white">
            <div class="absolute -right-10 -top-10 size-64 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <span class="inline-block rounded-md bg-indigo-500/20 px-2.5 py-1 text-[10px] font-mono font-bold tracking-widest uppercase text-indigo-200 border border-indigo-400/20">Akses Cepat</span>
                    <h3 class="mt-2 text-lg font-bold sm:text-xl text-white">Siap Menggunakan Ruangan Hari Ini?</h3>
                    <p class="mt-1 text-xs text-indigo-200/80 max-w-md">
                        Scan pertama berfungsi sebagai <span class="text-white font-medium">Check-In</span>, dan scan kedua pada QR yang sama berfungsi sebagai <span class="text-white font-medium">Check-Out</span>.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('scanner') }}" class="btn btn--primary flex items-center justify-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-95 px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-indigo-600/30 transition-all border border-indigo-400/30">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" aria-hidden="true">
                            <path d="M4 8V5a1 1 0 0 1 1-1h3M16 4h3a1 1 0 0 1 1 1v3M20 16v3a1 1 0 0 1-1 1h-3m-8 0H5a1 1 0 0 1-1-1v-3M8 8h8v8H8zM4 12h2m12 0h2M12 4v2m0 12v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Scan QR Ruangan
                    </a>
                    <a href="{{ route('reservation.history') }}" class="btn btn--secondary flex items-center justify-center gap-2 rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 px-4 py-2.5 text-xs font-semibold text-white backdrop-blur-sm border border-white/15 transition-all">
                        Riwayat Penggunaan
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ───────────────────────── Summary Stats Bento Grid ───────────────────────── --}}
    <section class="mt-8" aria-labelledby="ringkasan-heading">
        <h2 id="ringkasan-heading" class="sr-only">Ringkasan ruangan</h2>

        <div class="grid grid-cols-2 gap-3.5 lg:grid-cols-4">
            <x-ui.stat-card label="Total Ruangan" :value="$totalRooms" caption="Terdaftar di kampus" tone="neutral" stat="total">
                <x-slot:icon>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-slate-100 text-slate-700 ring-1 ring-slate-200">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" aria-hidden="true">
                            <path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </x-slot:icon>
            </x-ui.stat-card>

            <x-ui.stat-card label="Ruangan Tersedia" :value="$availableRooms" caption="Siap digunakan" tone="available" stat="available">
                <x-slot:icon>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200/80">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" aria-hidden="true">
                            <path d="m5 12.5 4.5 4.5L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </x-slot:icon>
            </x-ui.stat-card>

            <x-ui.stat-card label="Sedang Terpakai" :value="$occupiedRooms" caption="Check-in QR aktif" tone="occupied" stat="occupied">
                <x-slot:icon>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 ring-1 ring-rose-200/80">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" aria-hidden="true">
                            <path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20m6-9a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm5-6.6a3.5 3.5 0 0 1 0 6.8m2 4.1a3.5 3.5 0 0 1 3 3.4V20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </x-slot:icon>
            </x-ui.stat-card>

            <x-ui.stat-card label="Perlu Diperiksa" :value="$unknownRooms" caption="Laporan sensor tidak tersedia" tone="attention" stat="unknown">
                <x-slot:icon>
                    <div class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-200/80">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" aria-hidden="true">
                            <path d="M12 8v4m0 4h.01M10.3 4.9 2.8 18a1.5 1.5 0 0 0 1.3 2.2h15.8a1.5 1.5 0 0 0 1.3-2.2L13.7 4.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </x-slot:icon>
            </x-ui.stat-card>
        </div>
    </section>

    {{-- ───────────────────────── Room Monitoring Table / Cards Section ───────────────────────── --}}
    <section class="mt-8" aria-labelledby="rooms-heading">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 id="rooms-heading" class="text-lg font-extrabold tracking-tight text-slate-900">Monitoring Ruangan</h2>
                    <span class="rounded-full bg-slate-200/70 px-2 py-0.5 font-mono text-[11px] font-bold text-slate-700" data-list-count>{{ count($rooms) }}</span>
                </div>
                <p class="mt-0.5 text-xs text-slate-500">Status penggunaan dan laporan telemetri sensor ESP32.</p>
            </div>

            <div class="relative w-full sm:w-72">
                <label class="sr-only" for="room-search">Cari nama atau kode ruangan</label>
                <svg viewBox="0 0 24 24" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" aria-hidden="true">
                    <circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="2"/><path d="m16 16 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input
                    id="room-search"
                    type="search"
                    placeholder="Cari nama, kode, atau gedung..."
                    autocomplete="off"
                    class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3.5 text-xs text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                >
            </div>
        </div>

        <div class="mt-3 flex items-start gap-2.5 rounded-xl border border-amber-200/70 bg-amber-50/50 p-3 text-xs leading-relaxed text-amber-900/90 backdrop-blur-sm">
            <svg viewBox="0 0 24 24" class="mt-0.5 size-4 shrink-0 text-amber-600" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 11v5m0-8.5h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>Kolom status sensor adalah <strong class="font-semibold text-slate-900">indikasi dari perangkat ESP32</strong> (PIR & Door Switch), bukan bukti fisik mutlak. Status <code class="rounded bg-amber-100 px-1 py-0.5 font-mono font-semibold text-amber-800">UNKNOWN</code> menandakan belum ada laporan atau sinyal terputus > 90 detik.</span>
        </div>

        @if ($rooms->isEmpty())
            <div class="mt-4">
                <x-ui.empty-state
                    title="Belum ada ruangan"
                    body="Belum ada ruang kelas yang terdaftar dalam sistem. Hubungi administrator untuk menambahkan daftar ruangan.">
                    <x-slot:icon>
                        <div class="flex size-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                            <svg viewBox="0 0 24 24" class="size-6" fill="none" aria-hidden="true">
                                <path d="M3.75 10.25 12 3.75l8.25 6.5v9.1a1 1 0 0 1-1 1h-5.1v-6.1h-4.3v6.1h-5.1a1 1 0 0 1-1-1v-9.1Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </x-slot:icon>
                </x-ui.empty-state>
            </div>
        @else
            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-card">

                {{-- Column labels, desktop only --}}
                <div class="room-item room-item--head hidden grid-cols-12 gap-4 border-b border-slate-200/80 bg-slate-50/80 px-5 py-3 text-[11px] font-bold tracking-wider uppercase text-slate-500 lg:grid">
                    <span class="col-span-4">Ruangan & Gedung</span>
                    <span class="col-span-2">Ketersediaan</span>
                    <span class="col-span-3">Status Telemetri Sensor</span>
                    <span class="col-span-1">Pembaruan</span>
                    <span class="col-span-2 text-right">Aksi</span>
                </div>

                <div id="room-list" class="divide-y divide-slate-100">
                    @foreach ($rooms as $room)
                        @php
                            $isOccupied = $room['availability'] === 'occupied';
                            $searchIndex = \Illuminate\Support\Str::lower($room['code'].' '.$room['name'].' '.$room['building']);
                        @endphp

                        <article
                            data-room-code="{{ $room['code'] }}"
                            data-search="{{ $searchIndex }}"
                            class="room-item flex flex-col gap-3 p-4 transition-all duration-150 hover:bg-slate-50/80 sm:px-5 lg:grid lg:grid-cols-12 lg:items-center lg:gap-4"
                        >
                            <div class="room-item__identity col-span-4 flex items-center gap-3">
                                <span class="room-item__icon flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 font-mono text-xs font-bold text-slate-700 ring-1 ring-slate-200/80" aria-hidden="true">
                                    {{ mb_substr($room['code'], 0, 3) }}
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-xs font-bold text-slate-900 sm:text-sm">{{ $room['name'] }}</span>
                                    <span class="block truncate font-mono text-[11px] text-slate-400">{{ $room['code'] }} · {{ $room['building'] }}</span>
                                </span>
                            </div>

                            <div class="col-span-2">
                                <x-ui.status-badge
                                    :state="$isOccupied ? 'occupied' : 'available'"
                                    data-availability-badge>
                                    <span data-availability>{{ $isOccupied ? 'Sedang dipakai' : 'Tersedia' }}</span>
                                </x-ui.status-badge>
                            </div>

                            <div class="col-span-3 flex items-center gap-2">
                                <x-ui.status-badge :state="$room['sensor']" :sensor="true" data-sensor-badge>
                                    <span data-sensor>{{ $room['sensor'] }}</span>
                                </x-ui.status-badge>
                            </div>

                            <div class="room-item__updated col-span-1">
                                <span class="font-mono text-[11px] text-slate-400" data-updated>{{ $room['updated'] }}</span>
                            </div>

                            <div class="room-item__actions col-span-2 flex items-center justify-end gap-2 pt-2 border-t border-slate-100 lg:border-none lg:pt-0">
                                <a href="{{ route('scanner') }}" class="btn btn--primary btn--sm inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition-all">
                                    <svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/></svg>
                                    Scan
                                </a>
                                <button type="button" onclick="openModal(@js($room['code']))" class="btn btn--secondary btn--sm inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 active:scale-95 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition-all">
                                    Detail
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div id="room-no-match" hidden class="px-5 py-12 text-center">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <p class="mt-3 text-sm font-bold text-slate-900">Tidak ada ruangan yang cocok</p>
                    <p class="mt-1 text-xs text-slate-500">Coba kata kunci lain atau kosongkan kolom pencarian.</p>
                </div>

                <div class="flex flex-col gap-2 border-t border-slate-200/80 bg-slate-50/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <p class="text-xs text-slate-500">
                        Menampilkan <span class="font-bold text-slate-800" data-list-count>{{ count($rooms) }}</span> dari {{ $totalRooms }} ruangan
                    </p>
                    <x-ui.status-badge state="attention" data-unknown-badge>
                        <span data-unknown-count>{{ $unknownRooms }}</span> sensor perlu diperiksa
                    </x-ui.status-badge>
                </div>
            </div>
        @endif
    </section>
</main>

{{-- ───────────────────────── Overlays & Notifications ───────────────────────── --}}
<div id="room-modal-container"></div>

<div id="roomie-toast" class="toast fixed bottom-5 right-5 z-50 max-w-sm" hidden>
    <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-white p-4 shadow-2xl ring-1 ring-black/5" data-toast>
        <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
            <svg viewBox="0 0 24 24" class="size-5 text-roomie-occupied" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 7.5v5m0 3.5h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        <p class="text-xs font-semibold leading-relaxed text-slate-800" data-toast-text></p>
    </div>
</div>

<div id="roomie-live" class="sr-only" role="status" aria-live="polite" aria-atomic="true"></div>

<script>
    (() => {
        const POLL_INTERVAL_MS = 10000;
        const liveRegion = document.getElementById('roomie-live');
        const toast = document.getElementById('roomie-toast');
        const roomList = document.getElementById('room-list');
        const searchInput = document.getElementById('room-search');
        const noMatch = document.getElementById('room-no-match');
        const listCount = document.querySelector('[data-list-count]');

        const detailUrlTemplate = @json(route('room.detail-modal', ['code' => '__ROOM_CODE__']));

        let countdownInterval = null;
        let lastSummary = null;
        let toastTimer = null;
        let lastFocused = null;

        /* ---------------- announcements & toast ---------------- */

        function announce(message) {
            if (!liveRegion || !message) return;
            liveRegion.textContent = '';
            window.setTimeout(() => { liveRegion.textContent = message; }, 80);
        }

        function showToast(message) {
            if (!toast) return;
            toast.querySelector('[data-toast-text]').textContent = message;
            toast.hidden = false;
            window.clearTimeout(toastTimer);
            toastTimer = window.setTimeout(() => { toast.hidden = true; }, 5000);
        }

        /* ---------------- search filter ---------------- */

        function filterRooms() {
            if (!roomList) return;
            const term = (searchInput?.value || '').trim().toLowerCase();
            let visible = 0;

            Array.from(roomList.children).forEach((row) => {
                const haystack = (row.dataset.search || '').toLowerCase();
                const match = !term || haystack.includes(term);
                row.hidden = !match;
                if (match) visible += 1;
            });

            if (listCount) listCount.textContent = visible;
            if (noMatch) noMatch.hidden = visible !== 0;
        }

        searchInput?.addEventListener('input', filterRooms);

        /* ---------------- live room data ---------------- */

        function applyRoomSnapshot(item) {
            const row = Array.from(document.querySelectorAll('[data-room-code]'))
                .find((el) => el.dataset.roomCode === item.code);
            if (!row) return;

            const badge = row.querySelector('[data-availability-badge]');
            const label = row.querySelector('[data-availability]');
            if (badge && label) {
                badge.dataset.state = item.availability === 'occupied' ? 'occupied' : 'available';
                label.textContent = item.availability === 'occupied' ? 'Sedang dipakai' : 'Tersedia';
            }

            const sensorBadge = row.querySelector('[data-sensor-badge]');
            const sensor = row.querySelector('[data-sensor]');
            if (sensorBadge && sensor) {
                sensorBadge.dataset.state = item.sensor;
                sensor.textContent = item.sensor;
            }

            const updated = row.querySelector('[data-updated]');
            if (updated) updated.textContent = item.updated;
        }

        async function refreshRooms() {
            try {
                const response = await fetch(@json(route('rooms.live')), {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store',
                });
                if (!response.ok) return;

                const snapshot = await response.json();
                (snapshot.rooms || []).forEach(applyRoomSnapshot);

                for (const [key, val] of Object.entries(snapshot.summary || {})) {
                    const cell = document.querySelector('[data-stat="' + key + '"]');
                    if (cell) cell.textContent = val;
                }

                const unknownBadge = document.querySelector('[data-unknown-badge]');
                const unknown = document.querySelector('[data-unknown-count]');
                if (unknownBadge && unknown && snapshot.summary) {
                    unknownBadge.dataset.state = snapshot.summary.unknown > 0 ? 'attention' : 'unknown';
                    unknown.textContent = snapshot.summary.unknown;
                }

                if (lastSummary && snapshot.summary) {
                    const changes = [];
                    if (lastSummary.occupied !== snapshot.summary.occupied) {
                        changes.push(snapshot.summary.occupied + ' ruangan sedang dipakai');
                    }
                    if (lastSummary.available !== snapshot.summary.available) {
                        changes.push(snapshot.summary.available + ' ruangan tersedia');
                    }
                    if (changes.length) announce('Status ruangan diperbarui. ' + changes.join(', ') + '.');
                }
                lastSummary = snapshot.summary || null;
            } catch (error) {
                console.warn('Monitoring sementara tidak tersedia:', error);
            }
        }

        window.setInterval(() => {
            if (!document.hidden) refreshRooms();
        }, POLL_INTERVAL_MS);

        /* ---------------- room detail modal ---------------- */

        window.openModal = async function (roomCode) {
            const container = document.getElementById('room-modal-container');
            if (!container) return;

            lastFocused = document.activeElement;

            try {
                const response = await fetch(
                    detailUrlTemplate.replace('__ROOM_CODE__', encodeURIComponent(roomCode)),
                    { headers: { 'Accept': 'text/html' } }
                );
                if (!response.ok) throw new Error('HTTP ' + response.status);

                container.innerHTML = await response.text();

                const modal = document.getElementById('room-modal');
                if (!modal) return;

                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                startCountdown();

                const closer = modal.querySelector('[data-modal-close]');
                if (closer) closer.focus();

                document.addEventListener('keydown', onModalKeydown);
            } catch (error) {
                console.error(error);
                showToast('Detail ruangan gagal dimuat. Silakan coba lagi.');
                if (lastFocused && lastFocused.focus) lastFocused.focus();
            }
        };

        window.closeModal = function () {
            const modal = document.getElementById('room-modal');
            if (!modal) return;

            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');

            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }

            document.removeEventListener('keydown', onModalKeydown);

            if (lastFocused && lastFocused.focus) lastFocused.focus();
            lastFocused = null;
        };

        function onModalKeydown(event) {
            const modal = document.getElementById('room-modal');
            if (!modal || modal.classList.contains('hidden')) return;

            if (event.key === 'Escape') {
                event.preventDefault();
                window.closeModal();
                return;
            }

            if (event.key !== 'Tab') return;

            const focusables = Array.from(modal.querySelectorAll(
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
            )).filter((el) => el.offsetParent !== null);

            if (!focusables.length) return;

            const first = focusables[0];
            const last = focusables[focusables.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }

        function startCountdown() {
            const timerDisplay = document.getElementById('countdown-timer');
            if (!timerDisplay) return;

            if (countdownInterval) clearInterval(countdownInterval);

            const endTime = new Date(timerDisplay.dataset.endAt).getTime();

            function updateCountdown() {
                const distance = endTime - Date.now();

                if (distance <= 0) {
                    timerDisplay.textContent = 'WAKTU HABIS';
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                    return;
                }

                const hours = Math.floor(distance / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                timerDisplay.textContent = [hours, minutes, seconds]
                    .map((value) => String(value).padStart(2, '0'))
                    .join(':');
            }

            updateCountdown();
            countdownInterval = window.setInterval(updateCountdown, 1000);
        }

        lastSummary = {
            total: {{ $totalRooms }},
            available: {{ $availableRooms }},
            occupied: {{ $occupiedRooms }},
            unknown: {{ $unknownRooms }},
        };
    })();
</script>

</body>
</html>