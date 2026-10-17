{{--
    Sign-in.

    The error summary is focusable and the script below moves focus to it, so a
    screen reader hears why the attempt failed instead of landing on the first
    field. Validation is unchanged: AuthController still owns it.
--}}
<x-layouts.guest title="Masuk · Roomie">

    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Masuk</h1>
    <p class="mt-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
        Masuk untuk memindai QR ruangan dan melihat riwayat penggunaan.
    </p>

    @if ($errors->any())
        <div tabindex="-1" data-error-summary
             class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-4 focus-visible:outline-none dark:border-rose-500/40 dark:bg-rose-500/10">
            <p class="flex items-center gap-2 text-sm font-semibold text-rose-800 dark:text-rose-200">
                <x-ui.icon name="alert-triangle" :size="16" />
                Tidak dapat masuk
            </p>
            <ul class="mt-2 list-disc space-y-1 pl-6 text-sm leading-relaxed text-rose-700 dark:text-rose-300">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-4">
        @csrf

        <x-ui.field name="email" label="Email" type="email" :autofocus="true"
                   autocomplete="username"
                   placeholder="nama@kampus.ac.id" />

        <x-ui.field name="password" label="Kata sandi" type="password"
                   autocomplete="current-password" />

        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600 dark:text-slate-400">
            <input type="checkbox" name="remember" value="1"
                   class="size-4 cursor-pointer rounded border-slate-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-600 dark:bg-roomie-night">
            Ingat saya
        </label>

        <x-ui.button type="submit" size="lg" class="w-full">Masuk</x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">
        Belum punya akun?
        <a href="{{ route('register') }}"
           class="rounded font-semibold text-indigo-700 underline underline-offset-2 hover:text-indigo-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-indigo-300 dark:hover:text-indigo-200">Daftar</a>
    </p>

    <script>
        document.querySelector('[data-error-summary]')?.focus();
    </script>
</x-layouts.guest>