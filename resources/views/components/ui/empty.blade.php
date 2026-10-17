@props([
    'title',
    'body' => null,
    'icon' => 'inbox',
    'level' => 2,
])

{{--
    Empty state for a data region: "nothing here yet" and, when useful, the one
    action that would change that.

    `level` keeps the document outline valid — pass 3 when the empty state sits
    under a section heading, so heading levels never skip.
--}}
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-dashed border-slate-300 bg-white/60 px-6 py-12 text-center dark:border-slate-700 dark:bg-roomie-surface/40']) }}>
    <span class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400"
          aria-hidden="true">
        <x-ui.icon :name="$icon" :size="22" />
    </span>

    <{{ 'h'.$level }} class="mt-4 text-[15px] font-semibold text-slate-900 dark:text-slate-100">{{ $title }}</{{ 'h'.$level }}>

    @if ($body)
        <p class="mx-auto mt-1.5 max-w-[46ch] text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $body }}</p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-5 flex flex-wrap justify-center gap-2">{{ $slot }}</div>
    @endif
</div>