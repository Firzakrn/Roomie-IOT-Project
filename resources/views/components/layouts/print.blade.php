@props([
    'title' => 'Roomie',
    'description' => 'Label QR pintu ruang kelas Roomie.',
])

{{--
    Print shell for the QR label sheet. No sidebar, no topbar.
    Supports dark mode on screen; forced light colours for print output.

    Chrome that must not print (nav, buttons, instructions) is marked
    `no-print` by the page itself.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <x-layouts.head :title="$title" :description="$description">
        <x-slot:head>{{ $head ?? '' }}</x-slot:head>
    </x-layouts.head>
    {{-- Print-only CSS: force light mode for printed output --}}
    <style>
        @media print {
            html, body {
                background: white !important;
                color: #1e293b !important;
            }
            .roomie-label-sheet,
            .roomie-label,
            .roomie-label * {
                background: white !important;
                color: #1e293b !important;
                box-shadow: none !important;
                border-color: #e2e8f0 !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-sm text-slate-700 antialiased dark:bg-roomie-night dark:text-slate-300">
    {{-- Screen-only header with theme toggle --}}
    <header class="no-print border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-roomie-surface/80">
        <div class="mx-auto flex h-16 w-full max-w-5xl items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-2 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-roomie-surface">
                    <span class="flex shrink-0 items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <x-ui.brand :size="22" />
                    </span>
                    <span class="text-[15px] font-bold tracking-tight text-slate-900 dark:text-slate-50">Roomie</span>
                </a>
            </div>

            <div class="flex items-center gap-3">
                {{-- Theme Toggle --}}
                <button type="button" data-theme-toggle
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                        aria-label="Ganti tema">
                    <span class="sr-only">Ganti tema</span>
                    <span data-theme-icon="sun"><x-ui.icon name="sun" :size="18" /></span>
                    <span data-theme-icon="moon"><x-ui.icon name="moon" :size="18" /></span>
                </button>

                {{-- Back to Dashboard --}}
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                    <x-ui.icon name="chevron-left" :size="16" />
                    <span class="hidden sm:inline">Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    </header>

    <main id="main" class="print-reset mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
        {{ $slot }}
    </main>

    <x-layouts.assets />
</body>
</html>