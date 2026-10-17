@props([
    'variant' => 'secondary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'icon' => null,
    'iconRight' => null,
])

{{--
    Button. Renders an <a> when `href` is given, otherwise a <button>.

    Variants: primary, secondary, ghost, danger, subtle-light.
    Sizes:    sm (36px), md (40px), lg (44px — the mobile touch target floor).

    Active feedback is `active:scale-[.98]` per DESIGN.md §6; it never changes
    layout bounds, only transform, so neighbouring elements cannot jitter.
--}}

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl font-semibold leading-none whitespace-nowrap'
        . ' transition-[background-color,border-color,color,box-shadow,transform] duration-200 ease-out'
        . ' active:scale-[.98]'
        . ' focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2'
        . ' dark:focus-visible:ring-offset-roomie-surface'
        . ' disabled:pointer-events-none disabled:opacity-55';

    $sizes = [
        'sm' => 'h-9 px-3 text-xs',
        'md' => 'h-10 px-4 text-sm',
        'lg' => 'h-11 px-5 text-sm',
    ];

    $variants = [
        'primary' => 'bg-indigo-600 text-white shadow-sm hover:bg-indigo-700',
        'secondary' => 'border border-slate-200 bg-white text-slate-700 shadow-tile hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-roomie-surface dark:text-slate-200 dark:hover:bg-slate-800',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100',
        'danger' => 'bg-rose-600 text-white shadow-sm hover:bg-rose-700',
        'subtle-light' => 'border border-white/20 bg-white/10 text-white backdrop-blur-sm hover:bg-white/20',
    ];

    $classes = $base.' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['secondary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="$size === 'sm' ? 14 : 16" />@endif
        {{ $slot }}
        @if ($iconRight)<x-ui.icon :name="$iconRight" :size="$size === 'sm' ? 14 : 16" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-ui.icon :name="$icon" :size="$size === 'sm' ? 14 : 16" />@endif
        {{ $slot }}
        @if ($iconRight)<x-ui.icon :name="$iconRight" :size="$size === 'sm' ? 14 : 16" />@endif
    </button>
@endif