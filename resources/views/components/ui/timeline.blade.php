@props([
    'start',
    'end',
    'label' => null,
    'caption' => null,
    'tone' => 'indigo',
    'showRemaining' => true,
])

{{--
    Session progress bar: how far through a room session we are, and how long is
    left. Server-rendered so the first paint is already correct, then driven live
    by [data-end-at] when the countdown element is present.

    Times come from the server. Nothing here computes duration from user input.
--}}

@php
    $startAt = \Illuminate\Support\Carbon::parse($start);
    $endAt = \Illuminate\Support\Carbon::parse($end);

    // Plain timestamp arithmetic on purpose: Carbon's diffIn*() sign convention
    // is easy to invert, and an inverted progress bar is a silent lie.
    $startTs = $startAt->getTimestamp();
    $endTs = $endAt->getTimestamp();
    $nowTs = now()->getTimestamp();

    $total = max($endTs - $startTs, 1);
    $elapsed = min(max($nowTs - $startTs, 0), $total);
    $percent = (int) round(($elapsed / $total) * 100);
    $remaining = max($endTs - $nowTs, 0);

    $fills = [
        'indigo' => '#4F46E5',
        'rose' => '#E11D48',
        'emerald' => '#059669',
    ];
    $fill = $fills[$tone] ?? $fills['indigo'];

    $remainingLabel = $remaining <= 0
        ? 'habis'
        : ($remaining >= 3600
            ? intdiv($remaining, 3600).'j '.intdiv($remaining % 3600, 60).'m'
            : max(intdiv($remaining, 60), 1).'m');
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }} style="--timeline-fill: {{ $fill }}">
    @if ($label || $caption)
        <div class="mb-2 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
            @if ($label)
                <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $label }}</p>
            @else
                <span></span>
            @endif

            @if ($showRemaining)
                <p class="shrink-0 font-mono text-xs font-semibold tabular-nums text-slate-500 dark:text-slate-400"
                   data-countdown="compact" data-end-at="{{ $endAt->toIso8601String() }}">{{ $remainingLabel }}</p>
            @endif
        </div>
    @endif

    <div class="timeline__track" role="progressbar" data-start-at="{{ $startAt->getTimestamp() * 1000 }}"
         aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percent }}"
         aria-label="Kemajuan penggunaan ruangan">
        <div class="timeline__fill" data-countdown-fill style="width: {{ $percent }}%"></div>
    </div>

    <div class="mt-1.5 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 font-mono text-[11px] tabular-nums text-slate-500 dark:text-slate-400">
        <span>{{ $startAt->format('H:i') }}</span>
        @if ($caption)
            <span class="font-sans text-xs">{{ $caption }}</span>
        @endif
        <span>{{ $endAt->format('H:i') }}</span>
    </div>
</div>