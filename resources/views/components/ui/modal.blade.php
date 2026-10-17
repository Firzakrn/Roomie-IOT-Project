@props([
    'id' => 'roomie-dialog',
    'title' => null,
    'titleId' => null,
    'subtitle' => null,
])

{{--
    Modal built on the native <dialog> element.

    showModal() gives focus trapping, Escape-to-close, inert background content
    and a real ::backdrop for free — which is why the hand-rolled focus trap that
    used to live in the dashboard's inline script is gone.

    Open with window.roomieOpenDialog(id), close with the [data-dialog-close]
    button, Escape, or a click on the backdrop (wired once in layouts/assets).
--}}

@php
    $headingId = $titleId ?? ($id.'-title');
@endphp

<dialog {{ $attributes->merge(['class' => 'roomie-dialog text-left']) }}
        id="{{ $id }}"
        @if ($title) aria-labelledby="{{ $headingId }}" @endif>
    <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-800">
        <div class="min-w-0">
            <h2 id="{{ $headingId }}" class="text-lg font-semibold tracking-tight text-slate-900 dark:text-slate-50">
                {{ $title ?? 'Detail' }}
            </h2>
            @if ($subtitle)
                <p class="mt-0.5 truncate font-mono text-xs text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
            @endif
        </div>

        <button type="button" data-dialog-close
                class="-mr-1 flex size-9 shrink-0 items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:hover:bg-slate-800 dark:hover:text-slate-200">
            <span class="sr-only">Tutup</span>
            <x-ui.icon name="close" :size="18" />
        </button>
    </div>

    <div class="px-5 py-5">{{ $slot }}</div>

    @isset($footer)
        <div class="flex flex-wrap justify-end gap-2 border-t border-slate-200 px-5 py-4 dark:border-slate-800">
            {{ $footer }}
        </div>
    @endisset
</dialog>