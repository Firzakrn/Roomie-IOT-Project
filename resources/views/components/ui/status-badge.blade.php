@props([
    'state' => 'unknown',
    'dot' => true,
    'sensor' => false,
])

{{--
    Status badge. Always renders a text label — colour is never the only carrier
    of meaning (DESIGN.md 1.1 / 3). JS updates the `data-state` attribute and the
    label text, never the class list, so live polling cannot desynchronise the
    styling from the state.
--}}
<span {{ $attributes->merge(['class' => 'badge'.($sensor ? ' badge--sensor' : '')]) }}
      data-state="{{ $state }}">
    @if ($dot)
        <span class="badge__dot" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>