@props([
    'title' => 'Roomie',
    'description' => 'Roomie — monitoring ruang kelas dan pencatatan penggunaan lewat QR.',
])

{{--
    Focused shell for screens where navigation is noise: sign-in, sign-up and
    the QR scanner. Keeps the same head, theme and live region as the app shell,
    but drops the sidebar and topbar and centres a single column.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <x-layouts.head :title="$title" :description="$description">
        <x-slot:head>{{ $head ?? '' }}</x-slot:head>
    </x-layouts.head>
</head>
<body class="min-h-screen bg-slate-50 font-sans text-sm text-slate-600 antialiased dark:bg-roomie-night dark:text-slate-300">
    <a href="#main" class="skip-link">Lewati ke konten utama</a>

    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col px-4 py-6 sm:justify-center sm:py-10">
        <main id="main" class="w-full">
            {{ $slot }}
        </main>
    </div>

    <x-layouts.assets />
</body>
</html>