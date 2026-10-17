{{--
    Pagination override for Laravel 10's default `pagination::tailwind` view.

    The framework default renders its own gray/blue markup, which never matched
    the Roomie palette and would have been the one off-brand surface left in the
    app. Overriding the view file is enough — no AppServiceProvider change.
--}}
@if ($paginator->hasPages())
    @php
        $isLengthAware = $paginator instanceof \Illuminate\Pagination\LengthAwarePaginator;
        $current = $paginator->currentPage();
        $last = $isLengthAware ? $paginator->lastPage() : null;

        $window = 1;
        $start = max($current - $window, 1);
        $end = $last ? min($current + $window, $last) : $current;

        $linkClass = 'inline-flex h-10 min-w-10 items-center justify-center gap-1.5 rounded-xl border px-3 text-sm font-semibold transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-roomie-night';
        $idleClass = 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-roomie-surface dark:text-slate-300 dark:hover:bg-slate-800';
        $activeClass = 'border-indigo-600 bg-indigo-600 text-white';
        $disabledClass = 'pointer-events-none border-slate-200 bg-white text-slate-300 opacity-60 dark:border-slate-800 dark:bg-roomie-surface dark:text-slate-600';
    @endphp

    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
        @if ($isLengthAware)
            <p class="order-2 text-xs text-slate-500 sm:order-1 dark:text-slate-400">
                Menampilkan
                <span class="font-semibold tabular-nums text-slate-700 dark:text-slate-200">{{ $paginator->firstItem() }}</span>–<span class="font-semibold tabular-nums text-slate-700 dark:text-slate-200">{{ $paginator->lastItem() }}</span>
                dari
                <span class="font-semibold tabular-nums text-slate-700 dark:text-slate-200">{{ $paginator->total() }}</span>
                catatan
            </p>
        @endif

        <div class="order-1 flex items-center gap-1.5 sm:order-2">
            @if ($paginator->onFirstPage())
                <span class="{{ $linkClass }} {{ $disabledClass }}" aria-disabled="true">
                    <x-ui.icon name="chevron-left" :size="16" />
                    <span class="sr-only">Halaman sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $linkClass }} {{ $idleClass }}">
                    <x-ui.icon name="chevron-left" :size="16" />
                    <span>Sebelumnya</span>
                </a>
            @endif

            @for ($page = $start; $page <= $end; $page++)
                @if ($page === $current)
                    <span class="{{ $linkClass }} {{ $activeClass }}" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="{{ $linkClass }} {{ $idleClass }}">{{ $page }}</a>
                @endif
            @endfor

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $linkClass }} {{ $idleClass }}">
                    <span>Berikutnya</span>
                    <x-ui.icon name="chevron-right" :size="16" />
                </a>
            @else
                <span class="{{ $linkClass }} {{ $disabledClass }}" aria-disabled="true">
                    <span class="sr-only">Halaman berikutnya</span>
                    <x-ui.icon name="chevron-right" :size="16" />
                </span>
            @endif
        </div>
    </nav>
@endif