@props([
    'size' => 26,
    'class' => '',
])

{{--
    The Roomie brand mark.

    A framed module grid: six squares in a 3x3 field, inside a rounded frame.
    No letterform, no literal room, no person.

    Sixteen alternatives were drawn and rendered at 30/24/18px on both surfaces
    before this was picked. Every one of them collided with a symbol that
    already exists somewhere - a house icon is what every dashboard template
    ships; a room outline with a door gap reads as a photo; an arch reads as a
    tombstone; a ring with a dot reads as an eye; nested squares read as
    Instagram; three concentric arcs read as WiFi; two capsules crossing read as
    a medical cross; and three dots under an arc read as the people icon the app
    already uses on its own stat cards. Being un-ownable was the failure mode,
    not being ugly.

    What survives is a module grid, because it is the one shape that is native
    to this product: Roomie is entered by scanning a code on a classroom door.
    It is also the only candidate still legible at 18px.

    The module pattern is what keeps it from being a generic "data" glyph. Six
    cells in a 180-degree rotationally symmetric arrangement reads as designed
    rather than random; the four other patterns tried were lopsided or read as
    a dice face.

    Geometry: the frame is 18.6 units square on a 24 grid with 2.3 stroke, and
    the module field runs 5.4-18.6, so both centre on 12 with equal 1.55-unit
    clearance inside the frame. Colour comes from the surrounding element via
    currentColor - indigo-600 on light surfaces, indigo-400 in dark, white on
    the dark guest panel. Nothing is baked in here.
--}}
<svg viewBox="0 0 24 24" width="{{ $size }}" height="{{ $size }}" fill="none" stroke="currentColor"
     class="shrink-0 {{ $class }}" aria-hidden="true" focusable="false">
    <rect x="2.7" y="2.7" width="18.6" height="18.6" rx="5.2" stroke-width="2.3" />
    <rect x="5.4" y="5.4" width="3.6" height="3.6" rx="1.05" fill="currentColor" stroke="none" />
    <rect x="10.2" y="5.4" width="3.6" height="3.6" rx="1.05" fill="currentColor" stroke="none" />
    <rect x="5.4" y="10.2" width="3.6" height="3.6" rx="1.05" fill="currentColor" stroke="none" />
    <rect x="15" y="10.2" width="3.6" height="3.6" rx="1.05" fill="currentColor" stroke="none" />
    <rect x="10.2" y="15" width="3.6" height="3.6" rx="1.05" fill="currentColor" stroke="none" />
    <rect x="15" y="15" width="3.6" height="3.6" rx="1.05" fill="currentColor" stroke="none" />
</svg>