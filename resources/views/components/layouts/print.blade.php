@props([
    'title' => 'Roomie',
    'description' => 'Label QR pintu ruang kelas Roomie.',
])

{{--
    Print shell for the QR label sheet. No sidebar, no topbar, forced light
    colours so the sheet does not depend on the viewer's theme, and no screen
    gutter so the labels land exactly inside the @page margins.

    Chrome that must not print (nav, buttons, instructions) is marked
    `no-print` by the page itself.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <x-layouts.head :title="$title" :description="$description">
        <x-slot:head>{{ $head ?? '' }}</x-slot:head>
    </x-layouts.head>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-sm text-slate-700 antialiased">
    <main id="main" class="print-reset mx-auto w-full max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
        {{ $slot }}
    </main>

    <x-layouts.assets />
</body>
</html>