@props([
    'title',
    'description' => null,
])

{{--
    Page header: one h1 per page (DESIGN.md §3 — 24px / 700, tracking tight),
    a supporting line, and an optional action cluster on the right.

    Pages must not add their own <h1>; this owns it.
--}}
<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">{{ $title }}</h1>

        @if ($description)
            <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif

        @if (! $slot->isEmpty())
            <div class="mt-4">{{ $slot }}</div>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2.5">
            {{ $actions }}
        </div>
    @endisset
</div>