@props([
    'room',
])

{{--
    Bento tile for one classroom (DESIGN.md §4.B / §5.1).

    A room with a live session gets a double-width tile because it is the only
    state that needs room for a course name, an owner, a progress bar and a
    countdown. Available rooms stay compact. The span is data-driven so the live
    poller can change it without re-rendering the grid.

    DOM hooks the dashboard poller writes to:
      [data-availability-badge] [data-availability] [data-sensor-badge] [data-sensor]
      [data-updated] [data-session-block] [data-bento] [data-scan-label]
    Every [data-end-at] node is swept by the shared countdown ticker.
--}}

@php
    $session = $room['session'];
    $occupied = $room['availability'] === 'occupied';
@endphp

<article data-room-code="{{ $room['code'] }}"
         data-search="{{ \Illuminate\Support\Str::lower($room['code'].' '.$room['name'].' '.$room['location'].' '.($room['capacity'] ?? '')) }}"
         data-building="{{ $room['building'] ?? '' }}"
         data-availability="{{ $room['availability'] }}"
         data-sensor-fresh="{{ $room['sensor_fresh'] ? '1' : '0' }}"
         data-bento="{{ $occupied ? 'wide' : 'base' }}"
         class="room-tile flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-tile transition-shadow duration-200 hover:shadow-tile-hover dark:border-slate-800 dark:bg-roomie-surface"
         @if ($room['availability'] === 'occupied') data-occupied="true" @else data-occupied="false" @endif>

    <div class="flex items-start justify-between gap-3">
        <div class="flex min-w-0 items-start gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 font-mono text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-200"
                  aria-hidden="true">{{ mb_substr($room['code'], 0, 3) }}</span>

            <div class="min-w-0">
                <h3 class="text-[15px] font-semibold leading-snug text-slate-900 dark:text-slate-50">{{ $room['name'] }}</h3>
                <p class="mt-1 font-mono text-[11px] text-slate-500 dark:text-slate-400">{{ $room['code'] }}</p>
                <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">{{ $room['location'] }}</p>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            {{-- Pulses only while the device feed is genuinely fresh, so the
                 dot cannot claim "live" on a stale or missing report. --}}
            <span class="live-dot text-emerald-500" data-room-pulse
                  data-pulse="{{ $room['sensor_fresh'] ? 'true' : 'false' }}"
                  aria-hidden="true"></span>
            <x-ui.badge :state="$occupied ? 'occupied' : 'available'" data-availability-badge>
                <span data-availability>{{ $occupied ? 'Dipakai' : 'Tersedia' }}</span>
            </x-ui.badge>
        </div>
    </div>

    {{-- Active session: course, owner, progress and remaining time. This is
         what earns the tile its double width. --}}
    <div data-session-block @if (! $session) hidden @endif
         class="rounded-xl border border-rose-200/80 bg-rose-50/50 p-4 dark:border-rose-500/30 dark:bg-rose-500/5">
        @if ($session)
            <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                <p class="min-w-0 truncate text-[15px] font-semibold text-rose-950 dark:text-rose-100">{{ $session['course'] }}</p>
                <p class="shrink-0 font-mono text-[11px] tabular-nums text-rose-700/80 dark:text-rose-300/80">
                    {{ $session['sks'] }} SKS
                </p>
            </div>
            <p class="mt-0.5 truncate text-xs text-rose-900/70 dark:text-rose-100/70">
                {{ $session['mine'] ? 'Sesi kamu' : 'Dipakai '.($session['owner'] ?? 'pengguna lain') }}
            </p>

            <div class="mt-3.5 border-t border-rose-200/70 pt-3.5 dark:border-rose-500/20">
                <x-ui.timeline
                    :start="$session['start']"
                    :end="$session['end']"
                    :caption="($session['mine'] ? 'Sesi kamu' : 'Sesi berjalan').' · batas '.\Illuminate\Support\Carbon::parse($session['end'])->format('H:i')"
                    tone="rose" />
            </div>
        @endif
    </div>

    <div class="mt-auto flex flex-wrap items-center gap-x-3 gap-y-2">
        <x-ui.badge :state="$room['sensor']" :mono="true" data-sensor-badge>
            <span data-sensor>{{ $room['sensor'] }}</span>
        </x-ui.badge>

        @if (! empty($room['capacity']))
            <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                <x-ui.icon name="users" :size="14" />
                {{ $room['capacity'] }} orang
            </span>
        @endif

        <span class="ml-auto font-mono text-[11px] tabular-nums text-slate-500 dark:text-slate-400"
              data-updated>{{ $room['updated'] }}</span>
    </div>

    <div class="flex gap-2">
        {{-- Rescanning your own session IS the check-out (AGENTS.md), so the
             label says so instead of making the user remember the rule. --}}
        <x-ui.button href="{{ route('scanner') }}" variant="primary" size="md"
                    :icon="$session && $session['mine'] ? 'logout' : 'camera'" class="flex-1">
            <span data-scan-label>{{ $session && $session['mine'] ? 'Check-out' : 'Scan' }}</span>
        </x-ui.button>
        <x-ui.button type="button" variant="secondary" size="md"
                    data-room-open="{{ $room['code'] }}" class="flex-1">
            Detail
        </x-ui.button>
    </div>
</article>