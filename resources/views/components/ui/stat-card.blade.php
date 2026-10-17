@props([
    'label',
    'value',
    'caption' => null,
    'tone' => 'neutral',
    'stat' => null,
])

{{--
    Summary statistic tile. Pass `stat` to expose the value node to the 10s
    dashboard poller via [data-stat="<key>"].
--}}
<article {{ $attributes->merge(['class' => 'stat-card stat-card--'.$tone]) }}>
    <div class="stat-card__top">
        <p class="stat-card__label">{{ $label }}</p>
        <span class="stat-card__icon" aria-hidden="true">
            {{ $icon ?? '' }}
        </span>
    </div>

    <p class="stat-card__value" @if ($stat) data-stat="{{ $stat }}" @endif>{{ $value }}</p>

    @if ($caption)
        <p class="stat-card__caption">{{ $caption }}</p>
    @endif
</article>