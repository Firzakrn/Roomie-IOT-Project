@props([
    'title' => 'Roomie',
    'description' => 'Masuk ke Roomie untuk memindai QR ruang kelas dan melihat riwayat penggunaan.',
])

{{--
    Auth shell (sign-in / sign-up).

    Split layout: brand panel on the left from lg up, form on the right. No
    emoji, no decorative gradient — the left panel states what the product is
    and which rules actually apply, which is more useful than an illustration.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <x-layouts.head :title="$title" :description="$description" />
</head>
<body class="min-h-screen bg-slate-50 font-sans text-sm text-slate-600 antialiased dark:bg-roomie-night dark:text-slate-300">
    <a href="#main" class="skip-link">Lewati ke konten utama</a>

    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Brand panel: hidden below lg, where it would only push the form down. --}}
        <section class="hidden flex-col justify-between bg-slate-900 p-10 text-slate-300 lg:flex dark:bg-roomie-surface">
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-3 self-start rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400">
                <span class="flex size-10 items-center justify-center rounded-xl bg-indigo-600 text-white">
                    <x-ui.icon name="house" :size="20" />
                </span>
                <span>
                    <span class="block text-[15px] font-bold tracking-tight text-white">Roomie</span>
                    <span class="block text-xs text-slate-400">Smart Classroom Monitoring</span>
                </span>
            </a>

            <div class="max-w-md">
                <h2 class="text-2xl font-bold leading-snug tracking-tight text-white">
                    Gunakan kelas saat itu juga.
                </h2>
                <p class="mt-3 text-sm leading-relaxed text-slate-400">
                    Status tiap ruang kelas diperbarui dari sensor ESP32 di pintu dan dari pemindaian QR oleh mahasiswa.
                </p>

                <ul class="mt-8 space-y-4">
                    @foreach ([
                        ['icon' => 'scan', 'text' => 'Check-in hanya lewat QR yang ditempel di pintu kelas.'],
                        ['icon' => 'clock', 'text' => 'Batas waktu dihitung server dari jumlah SKS, bukan dari browser.'],
                        ['icon' => 'activity', 'text' => 'Status sensor ditampilkan apa adanya, termasuk saat UNKNOWN.'],
                    ] as $point)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-indigo-300" aria-hidden="true">
                                <x-ui.icon :name="$point['icon']" :size="16" />
                            </span>
                            <span class="text-sm leading-relaxed">{{ $point['text'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="text-xs text-slate-500">Roomie · Sistem monitoring dan pencatatan ruang kampus</p>
        </section>

        <section class="flex flex-col justify-center px-4 py-10 sm:px-8 lg:px-12">
            <main id="main" class="mx-auto w-full max-w-sm">
                <a href="{{ route('login') }}"
                   class="mb-8 inline-flex items-center gap-3 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 lg:hidden">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-indigo-600 text-white lg:hidden">
                        <x-ui.icon name="house" :size="20" />
                    </span>
                    <span class="text-[15px] font-bold tracking-tight text-slate-900 dark:text-slate-50">Roomie</span>
                </a>

                {{ $slot }}

                <p class="mt-8 text-center text-xs text-slate-500 dark:text-slate-400">
                    Roomie · Sistem monitoring dan pencatatan ruang kampus
                </p>
            </main>
        </section>
    </div>

    <x-layouts.assets />
</body>
</html>