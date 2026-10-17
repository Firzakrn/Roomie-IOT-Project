@props([
    'size' => 24,
    'class' => '',
])

{{--
    The Roomie brand mark.

    A drawn letterform, not a stock glyph. Every admin template ships a house or
    building pictogram in a coloured chip, and that reads as "unconfigured" no
    matter how good the rest of the UI is.

    Pictorial alternatives were drawn and rendered side by side before this was
    chosen. All of them collided with other people's symbols: a rounded room
    outline with a door gap became an image icon, an arched doorway became a
    tombstone, a doorway with a floor line became a bell. A letterform has no
    such collision, and it is the one shape that scales to a favicon.

    Solid rather than outlined. Without a background tile there is nothing to
    prop it up, and a stroked monogram thins out to nothing by 18px — this one
    still reads at a glance on both the light app surface and the dark guest
    panel. The counter is a rounded slot so it echoes the app's own corner
    language instead of looking borrowed from a geometric typeface.

    Geometry: ink spans x 6.4-17.7 and y 4.4-20, so the optical centre lands on
    the grid. The counter is a hole cut with fill-rule="evenodd", which is why
    this lives outside <x-ui.icon> — that component is stroke-only.

    Colour comes from the surrounding element via currentColor. On light
    surfaces use indigo-600, on the dark guest panel use white; there is no
    colour baked in here.
--}}
<svg viewBox="0 0 24 24" width="{{ $size }}" height="{{ $size }}" fill="currentColor" fill-rule="evenodd"
     class="shrink-0 {{ $class }}" aria-hidden="true" focusable="false">
    <path d="M6.4 20V4.4h6.8a3.5 3.5 0 0 1 3.5 3.5v.8a3.5 3.5 0 0 1-3.5 3.5h-1.7l6.2 7.8h-3.3L9.9 12.9V20Z
             M9.9 7.4h2.8a1.1 1.1 0 0 1 0 2.2H9.9Z" />
</svg>