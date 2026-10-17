@props([
    'size' => 19,
    'class' => '',
])

{{--
    The Roomie brand mark.

    A drawn letterform, not a stock glyph. Every admin template ships a house or
    building pictogram in a coloured chip, and that reads as "unconfigured" no
    matter how good the rest of the UI is. Pictorial alternatives were tried and
    all collapsed into other people's symbols — a rounded room outline with a
    door gap just became an image icon, and an arched doorway became a
    tombstone. A monogram has no such collision.

    The counter is a rounded rectangle rather than a semicircle, so it echoes the
    app's own corner language instead of looking borrowed from a geometric
    typeface. The solid module inside it is the one deliberate detail: it stands
    for a room with someone in it, and it survives down to 17px.

    Sizing: cap height 15 units (y 4.4-19.8), ink spanning x 6.4-17.8, so the
    optical centre lands on the grid once the stroke width is counted. The
    glyph is drawn for a stroke around 2.2 — bolder than the 1.75 used by the
    UI icon set, because a logo has to hold its own against a solid fill.

    This lives outside <x-ui.icon> on purpose: the icon component is stroke-only
    with `fill="none"`, and the counter module needs to be filled.
--}}
<svg viewBox="0 0 24 24" width="{{ $size }}" height="{{ $size }}" fill="none" stroke="currentColor"
     stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
     class="shrink-0 {{ $class }}" aria-hidden="true" focusable="false">
    <path d="M6.4 19.8V4.4h7.1a2.8 2.8 0 0 1 2.8 2.8v2.4a2.8 2.8 0 0 1-2.8 2.8H6.4" />
    <path d="m11.7 12.4 6.1 7.4" />
    <rect x="10.4" y="7.15" width="2.4" height="2.4" rx="0.55" fill="currentColor" stroke="none" />
</svg>