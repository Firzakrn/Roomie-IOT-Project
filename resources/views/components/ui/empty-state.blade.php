@props([
    'title',
    'body' => null,
    'icon' => null,
])

{{--
    Empty state for a data region. Currently used by the dashboard when a PJ
    has no rooms registered; kept generic so other list pages can adopt it.
--}}
<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <span class="empty-state__icon" aria-hidden="true">
        {{ $icon ?? '' }}
    </span>

    <h3 class="empty-state__title">{{ $title }}</h3>

    @if ($body)
        <p class="empty-state__body">{{ $body }}</p>
    @endif

    {{ $slot }}
</div>