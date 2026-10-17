@props([
    'name',
    'size' => 20,
    'class' => '',
])

{{--
    The single icon source for the whole app. One 24x24 grid, 1.75px stroke,
    round caps/joins, `currentColor`. Inline so it inherits colour, themes
    itself, and needs no build step (no npm in this repo).

    Icons are decorative by default: the control that wraps them carries the
    accessible name. Pass no name and you get nothing; pass a wrong name and you
    get a visible dashed placeholder instead of a silent blank.
--}}

@php
    $icons = [
        'house' => ['M3.75 10.25 12 3.75l8.25 6.5v9.1a1 1 0 0 1-1 1h-5.1v-6.1h-4.3v6.1h-5.1a1 1 0 0 1-1-1v-9.1Z'],
        'dashboard' => ['M4 4h6v6H4z', 'M14 4h6v6h-6z', 'M4 14h6v6H4z', 'M14 14h6v6h-6z'],
        'scan' => ['M3 7V5a2 2 0 0 1 2-2h2', 'M17 3h2a2 2 0 0 1 2 2v2', 'M21 17v2a2 2 0 0 1-2 2h-2', 'M7 21H5a2 2 0 0 1-2-2v-2', 'M7 12h10'],
        'qr' => ['M4 4h6v6H4z', 'M14 4h6v6h-6z', 'M4 14h6v6H4z', 'M14 14h2.5v2.5H14z', 'M17.5 17.5H20V20h-2.5z', 'M14 19.5h2.5V22H14z'],
        'history' => ['M3 12a9 9 0 1 0 2.6-6.36', 'M3 4v5h5', 'M12 8v4l3 2'],
        'printer' => ['M6 9V4h12v5', 'M6 18H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2', 'M6 14h12v6H6z'],
        'camera' => ['M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3Z', 'M12 17a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z'],
        'zap' => ['M13 2 4 14h7l-1 8 9-12h-7l1-8Z'],
        'keyboard' => ['M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z', 'M6 9h.01M10 9h.01M14 9h.01M18 9h.01M6 13h.01M18 13h.01M9 13h6M8 16h8'],
        'search' => ['M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z', 'm21 21-4.3-4.3'],
        'bell' => ['M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9', 'M13.73 21a2 2 0 0 1-3.46 0'],
        'sun' => ['M12 17a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z', 'M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42'],
        'moon' => ['M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z'],
        'menu' => ['M4 6h16M4 12h16M4 18h16'],
        'close' => ['M6 18 18 6M6 6l12 12'],
        'logout' => ['M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15', 'M12 9l3 3m0 0-3 3m3-3H9'],
        'chevron-down' => ['M6 9l6 6 6-6'],
        'chevron-left' => ['M15 6l-6 6 6 6'],
        'chevron-right' => ['M9 6l6 6-6 6'],
        'arrow-left' => ['M19 12H5', 'm12 19-7-7 7-7'],
        'arrow-right' => ['M5 12h14', 'm12 5 7 7-7 7'],
        'refresh' => ['M20.5 12a8.5 8.5 0 1 1-2.6-6.1', 'M21 4v5h-5'],
        'pause' => ['M10 5v14', 'M14 5v14'],
        'play' => ['m8 5 11 7-11 7V5Z'],
        'pause-circle' => ['M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'M10 9v6', 'M14 9v6'],
        'play-circle' => ['M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'm10 8.5 6 3.5-6 3.5v-7Z'],
        'filter' => ['M3 5h18l-7 8v6l-4 2v-8L3 5Z'],
        'check' => ['m5 12.5 4.5 4.5L19 7'],
        'check-circle' => ['M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'm8 12.5 2.5 2.5 5-5.5'],
        'x-circle' => ['M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'm9.5 9.5 5 5M14.5 9.5l-5 5'],
        'alert-triangle' => ['M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z', 'M12 9v4', 'M12 17h.01'],
        'info' => ['M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'M12 11v5', 'M12 8h.01'],
        'clock' => ['M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'M12 7.5V12l3 2'],
        'activity' => ['M22 12h-4l-3 9L9 3l-3 9H2'],
        'signal-off' => ['M2 2l20 20', 'M8.6 15.4a5 5 0 0 1 6.8 0', 'M5.2 12.2A10 10 0 0 1 8 10.6', 'M15 10.6a10 10 0 0 1 3.8 1.6', 'M2 9a15 15 0 0 1 3.4-2.3', 'M18.4 6.8A15 15 0 0 1 22 9', 'M12 20h.01'],
        'users' => ['M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2', 'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z', 'M22 21v-2a4 4 0 0 0-3-3.87', 'M16 3.13a4 4 0 0 1 0 7.75'],
        'user' => ['M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2', 'M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z'],
        'hash' => ['M4 9h16', 'M4 15h16', 'M10 3 8 21', 'M16 3l-2 18'],
        'building' => ['M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16', 'M16 9h2a2 2 0 0 1 2 2v10', 'M2 21h20', 'M8 7h2M8 11h2M8 15h2'],
        'layers' => ['m12 3 9 5-9 5-9-5 9-5Z', 'm3 13 9 5 9-5', 'm3 17 9 5 9-5'],
        'door' => ['M13 4h7v16h-7', 'M3 20V6l10-2v18l-10-2Z', 'M7.5 12h.01'],
        'thermometer' => ['M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z'],
        'inbox' => ['M22 12h-6l-2 3h-4l-2-3H2', 'M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z'],
        'calendar' => ['M3 5h18v16H3z', 'M16 3v4', 'M8 3v4', 'M3 11h18'],
        'file-text' => ['M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z', 'M14 2v6h6', 'M8 13h8', 'M8 17h5'],
        'eye' => ['M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z', 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z'],
        'eye-off' => ['m3 3 18 18', 'M10.6 10.6a2 2 0 0 0 2.8 2.8', 'M9.9 5.2A9.8 9.8 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-3.4 4.3', 'M6.6 6.6A17.7 17.7 0 0 0 2 12s3.6 7 10 7a9.7 9.7 0 0 0 4.2-.9'],
    ];

    $paths = $icons[$name] ?? null;
@endphp

<svg viewBox="0 0 24 24" width="{{ $size }}" height="{{ $size }}" fill="none" stroke="currentColor"
     stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
     class="shrink-0 {{ $class }}" aria-hidden="true" focusable="false">
    @if ($paths)
        @foreach ($paths as $path)
            <path d="{{ $path }}" />
        @endforeach
    @else
        {{-- Unknown icon name: show it rather than rendering nothing. --}}
        <rect x="3" y="3" width="18" height="18" rx="3" stroke-dasharray="3 3" />
    @endif
</svg>