@props([
    'title' => 'Roomie',
    'description' => null,
])

{{-- Per-page assets (a page-specific vendor script, for example). --}}
@isset($head)
    {{ $head }}
@endisset

{{--
    The one and only <head> for the app. Every layout renders this, so tokens,
    fonts and Tailwind config can no longer drift between pages.

    Tailwind comes from the Play CDN on purpose: this repo has no build step and
    AGENTS.md forbids `npm install` / `npm run build`. The trade-off is a runtime
    JIT (a brief unstyled flash is unavoidable), which is why the dark-mode boot
    script below runs BEFORE the CDN so the correct theme paints immediately.
--}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#F8FAFC" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#090D16" media="(prefers-color-scheme: dark)">
@if ($description)
    <meta name="description" content="{{ $description }}">
@endif
<title>{{ $title }}</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

<script>
    // Runs before first paint: no light-mode flash for dark-mode users.
    (function () {
        try {
            var stored = localStorage.getItem('roomie-theme');
            var dark = stored
                ? stored === 'dark'
                : window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', dark);
        } catch (e) { /* private mode: fall back to light */ }
    })();
</script>

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                // DESIGN.md §3: Plus Jakarta Sans for UI, JetBrains Mono for
                // tokens, room codes and timestamps.
                fontFamily: {
                    sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', '-apple-system', '"Segoe UI"', 'sans-serif'],
                    mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace'],
                },
                // DESIGN.md §2 dark neutrals, which sit off the standard slate ramp.
                colors: {
                    roomie: {
                        night: '#090D16',
                        surface: '#111827',
                        sunken: '#0B1220',
                    },
                },
                boxShadow: {
                    tile: '0 1px 2px 0 rgb(15 23 42 / 0.04)',
                    'tile-hover': '0 10px 20px -8px rgb(15 23 42 / 0.12)',
                    pop: '0 12px 32px -8px rgb(15 23 42 / 0.18)',
                },
            },
        },
    };
</script>

<x-ui.tokens />