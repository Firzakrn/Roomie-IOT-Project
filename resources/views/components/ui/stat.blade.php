@props([
    'label',
    'value',
    'caption' => null,
    'tone' => 'neutral',
    'stat' => null,
    'icon' => null,
])

{{--
    Summary metric tile.

    Pass `stat` to expose the value node to the dashboard poller via
    [data-stat="<key>"], so a live refresh can update the number without
    re-rendering the tile.

    Tones: neutral, available, occupied, attention.
--}}
<article {{ $attributes->merge(['class' => 'stat-tile rounded-2xl border border-slate-200 bg-white p-5 shadow-tile transition-shadow duration-200 hover:shadow-tile-hover dark:border-slate-800 dark:bg-roomie-surface']) }}
         data-tone="{{ $tone }}">
    <div class="flex items-start justify-between gap-3">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">{{ $label }}</p>
        @if ($icon)
            <span class="stat-tile__icon" aria-hidden="true">
                <x-ui.icon :name="$icon" :size="18" />
            </span>
        @endif
    </div>

    <p class="stat-tile__value mt-3 text-3xl font-bold leading-none tracking-tight tabular-nums dark:text-slate-50"
       @if ($stat) data-stat="{{ $stat }}" @endif>{{ $value }}</p>

    @if ($caption)
        <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $caption }}</p>
    @endif
</article>