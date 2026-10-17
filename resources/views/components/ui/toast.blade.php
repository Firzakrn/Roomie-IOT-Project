@props([
    'tone' => 'info',
    'message' => null,
])

{{--
    Single toast. Rendered once inside the layout for flash messages and once
    more inside #roomie-toast-template, which the script clones so JS-created
    toasts are pixel-identical to server-rendered ones.

    Tones: info, success, warning, danger. `message` is optional so the template
    copy stays empty.
--}}

@php
    $icons = [
        'info' => 'info',
        'success' => 'check-circle',
        'warning' => 'alert-triangle',
        'danger' => 'x-circle',
    ];
@endphp

<div data-toast data-tone="{{ $tone }}"
     class="roomie-toast pointer-events-auto flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3.5 shadow-pop dark:border-slate-700 dark:bg-roomie-surface"
     role="status">
    <span class="roomie-toast__icon shrink-0" aria-hidden="true">
        <x-ui.icon :name="$icons[$tone] ?? 'info'" :size="18" />
    </span>
    <p class="min-w-0 flex-1 text-sm leading-relaxed text-slate-700 dark:text-slate-200"
       data-toast-message>{{ $message }}</p>
</div>