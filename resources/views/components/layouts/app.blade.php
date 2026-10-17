@props([
    'title' => 'Roomie',
    'description' => null,
])

{{--
    Authenticated app shell (DESIGN.md §4.A).

    Sidebar: 240px expanded, 64px collapsed rail from 1024px up, off-canvas
    drawer below that. Topbar: sticky glass bar carrying the primary QR CTA,
    the theme toggle and the user menu.

    A notifications bell is deliberately absent for now — there is no
    notification source to render, and a dead control is worse than no control.
    It arrives with the dashboard live feed.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <x-layouts.head :title="$title" :description="$description">
        <x-slot:head>{{ $head ?? '' }}</x-slot:head>
    </x-layouts.head>
</head>
<body class="min-h-screen bg-slate-50 font-sans text-sm text-slate-600 antialiased dark:bg-roomie-night dark:text-slate-300">
    <a href="#main" class="skip-link">Lewati ke konten utama</a>

    @php
        $user = auth()->user();
        $isAdmin = $user?->role === 'admin';

        $navItems = [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
            ['route' => 'scanner', 'label' => 'Scan QR', 'icon' => 'scan'],
            ['route' => 'reservation.history', 'label' => 'Riwayat', 'icon' => 'history'],
        ];

        if ($isAdmin) {
            $navItems[] = ['route' => 'rooms.qr-labels', 'label' => 'Cetak QR', 'icon' => 'qr'];
        }
    @endphp

    <div class="roomie-backdrop no-print" id="roomie-nav-backdrop" aria-hidden="true" hidden></div>

    <aside id="roomie-sidebar" class="roomie-sidebar no-print" data-open="false" aria-label="Navigasi utama">
        <div class="flex h-16 shrink-0 items-center gap-3 px-5">
            <a href="{{ route('dashboard') }}"
               class="flex min-w-0 items-center gap-3 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-roomie-surface">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white">
                    <x-ui.icon name="house" :size="18" />
                </span>
                <span class="roomie-brand-text min-w-0">
                    <span class="block truncate text-[15px] font-bold tracking-tight text-slate-900 dark:text-slate-50">Roomie</span>
                    <span class="block truncate text-[11px] font-medium text-slate-500 dark:text-slate-400">Smart Classroom</span>
                </span>
            </a>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-2" aria-label="Menu">
            @foreach ($navItems as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="roomie-nav-item relative flex items-center gap-3 rounded-xl py-2.5 pl-4 pr-3 text-sm font-semibold transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-roomie-surface
                          {{ $active
                                ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-100' }}"
                   @if ($active) aria-current="page" @endif>
                    <span class="roomie-nav-indicator absolute inset-y-2 left-0 w-1 rounded-full bg-indigo-600" aria-hidden="true"></span>
                    <x-ui.icon :name="$item['icon']" :size="18" />
                    <span class="roomie-nav-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="roomie-sidebar-foot border-t border-slate-200 p-4 dark:border-slate-800">
            <p class="text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">
                Pemakaian tercatat lewat scan QR di pintu kelas. Tidak ada pemesanan jarak jauh.
            </p>
        </div>
    </aside>

    <div class="roomie-content flex min-h-screen flex-col">
        <header class="roomie-topbar sticky top-0 z-30 border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-roomie-surface/80">
            <div class="mx-auto flex h-16 w-full max-w-7xl items-center gap-2 px-4 sm:gap-3 sm:px-6 lg:px-8">
                <button type="button" data-nav-toggle aria-expanded="false" aria-controls="roomie-sidebar"
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100 lg:hidden">
                    <span class="sr-only">Buka navigasi</span>
                    <x-ui.icon name="menu" :size="20" />
                </button>

                <button type="button" data-nav-collapse aria-controls="roomie-sidebar"
                        class="hidden size-10 shrink-0 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100 lg:inline-flex">
                    <span class="sr-only">Ciutkan sidebar</span>
                    <x-ui.icon name="chevron-left" :size="20" />
                </button>

                <a href="{{ route('dashboard') }}"
                   class="flex min-w-0 items-center gap-2 lg:hidden">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white">
                        <x-ui.icon name="house" :size="16" />
                    </span>
                    <span class="truncate text-[15px] font-bold tracking-tight text-slate-900 dark:text-slate-50">Roomie</span>
                </a>

                <div class="flex-1"></div>

                {{-- DESIGN.md §4.A: the quick scanner CTA lives in the topbar. --}}
                <x-ui.button href="{{ route('scanner') }}" variant="primary" size="md" icon="camera" class="shrink-0">
                    <span class="sm:hidden">Scan</span>
                    <span class="hidden sm:inline">Scan QR Ruangan</span>
                </x-ui.button>

                {{-- Page-specific topbar controls. The dashboard mounts its
                     notification centre here, driven by the live feed. --}}
                {{ $topbar ?? '' }}

                <button type="button" data-theme-toggle aria-pressed="false"
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100">
                    <span class="sr-only">Ganti tema</span>
                    <span data-theme-icon="sun"><x-ui.icon name="sun" :size="18" /></span>
                    <span data-theme-icon="moon" hidden><x-ui.icon name="moon" :size="18" /></span>
                </button>

                <div class="relative shrink-0" data-menu>
                    <button type="button" data-menu-trigger aria-expanded="false" aria-haspopup="true"
                            class="flex items-center gap-2 rounded-xl p-1 pr-1.5 transition-colors hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:hover:bg-slate-800 sm:gap-2.5 sm:pr-2">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 font-mono text-xs font-bold text-indigo-700 ring-1 ring-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300 dark:ring-indigo-400/20">
                            {{ mb_strtoupper(mb_substr($user?->name ?? '?', 0, 2)) }}
                        </span>
                        <span class="hidden min-w-0 text-left sm:block">
                            <span class="block max-w-[10rem] truncate text-xs font-semibold text-slate-900 dark:text-slate-100">{{ $user?->name }}</span>
                            <span class="block text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ $isAdmin ? 'Administrator' : 'Mahasiswa' }}</span>
                        </span>
                        <x-ui.icon name="chevron-down" :size="16" class="hidden text-slate-400 sm:block" />
                    </button>

                    <div data-menu-panel hidden
                         class="absolute right-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-pop dark:border-slate-700 dark:bg-roomie-surface">
                        <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $user?->name }}</p>
                            <p class="mt-0.5 truncate font-mono text-xs text-slate-500 dark:text-slate-400">{{ $user?->email }}</p>
                            <span class="badge mt-2 {{ $isAdmin ? '' : 'hidden' }}" data-state="info">Administrator</span>
                        </div>

                        <div class="p-1.5">
                            <a href="{{ route('reservation.history') }}"
                               class="flex min-h-10 items-center gap-2.5 rounded-xl px-3 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                                <x-ui.icon name="history" :size="16" class="text-slate-400" />
                                Riwayat penggunaan
                            </a>
                            @if ($isAdmin)
                                <a href="{{ route('rooms.qr-labels') }}"
                                   class="flex min-h-10 items-center gap-2.5 rounded-xl px-3 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                                    <x-ui.icon name="qr" :size="16" class="text-slate-400" />
                                    Cetak QR ruangan
                                </a>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 p-1.5 dark:border-slate-800">
                            @csrf
                            <button type="submit"
                                    class="flex min-h-10 w-full items-center gap-2.5 rounded-xl px-3 text-sm font-semibold text-rose-600 transition-colors hover:bg-rose-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 dark:text-rose-400 dark:hover:bg-rose-500/10">
                                <x-ui.icon name="logout" :size="16" />
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main id="main" class="mx-auto w-full max-w-7xl flex-1 px-4 pb-16 pt-6 sm:px-6 sm:pt-8 lg:px-8">
            {{ $slot }}
        </main>
    </div>

    <x-layouts.assets />
</body>
</html>