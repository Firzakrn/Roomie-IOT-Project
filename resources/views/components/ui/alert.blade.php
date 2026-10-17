@props([
    'tone' => 'info',
    'title' => null,
    'icon' => null,
])

{{--
    Inline status message.

    Tones: info, success, warning, danger. Colour comes from ui/tokens keyed by
    `data-tone` so both themes stay consistent. An alert always has an icon and,
    when it reports an error, `role="alert"` so it is announced on arrival.
--}}

@php
    $icons = [
        'info' => 'info',
        'success' => 'check-circle',
        'warning' => 'alert-triangle',
        'danger' => 'x-circle',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'alert']) }}
     data-tone="{{ $tone }}"
     @if ($tone === 'danger') role="alert" @endif>
    <span class="alert__icon" aria-hidden="true">
        <x-ui.icon :name="$icon ?? ($icons[$tone] ?? 'info')" :size="18" />
    </span>

    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="alert__title">{{ $title }}</p>
        @endif
        @if (! $slot->isEmpty())
            <div class="{{ $title ? 'mt-1' : '' }} alert__body">{{ $slot }}</div>
        @endif
    </div>
</div>