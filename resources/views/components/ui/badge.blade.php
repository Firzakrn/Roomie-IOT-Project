@props([
    'state' => 'unknown',
    'dot' => true,
    'mono' => false,
])

{{--
    Status badge (DESIGN.md §4.D).

    Colour is keyed off `data-state` in ui/tokens, so there is exactly one
    mapping for light and dark and the dashboard poller only has to flip an
    attribute — it can never desynchronise class lists from state.

    States: available, occupied, attention, info, unknown — plus the raw device
    values TERTUTUP / TERBUKA / UNKNOWN for the ESP32 sensor column.

    Always carries a text label. Colour is never the only signal.
--}}
<span {{ $attributes->merge(['class' => 'badge'.($mono ? ' badge--mono' : '')]) }}
      data-state="{{ $state }}">
    @if ($dot)
        <span class="badge__dot" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>