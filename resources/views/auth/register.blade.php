{{--
    Sign-up.

    Role is never a form field: AuthController always writes `mahasiswa`
    (AGENTS.md), so the form does not pretend otherwise.
--}}
<x-layouts.guest title="Daftar · Roomie">

    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">Buat akun</h1>
    <p class="mt-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
        Akun baru terdaftar sebagai mahasiswa. Peran lain diatur administrator.
    </p>

    @if ($errors->any())
        <div class="mt-5">
            <x-ui.error-summary :fields="[
                'name' => 'name',
                'email' => 'email',
                'password' => 'password',
                'password_confirmation' => 'password_confirmation',
            ]" />
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
        @csrf

        <x-ui.field name="name" label="Nama lengkap" :autofocus="! $errors->has('name')" :maxlength="255"
                   autocomplete="name"
                   placeholder="Nama mahasiswa" />

        <x-ui.field name="email" label="Email" type="email" :maxlength="255"
                   autocomplete="email"
                   placeholder="nama@kampus.ac.id" />

        <x-ui.field name="password" label="Kata sandi" type="password" :minlength="8"
                   autocomplete="new-password"
                   hint="Minimal 8 karakter." />

        <x-ui.field name="password_confirmation" label="Ulangi kata sandi" type="password" :minlength="8"
                   autocomplete="new-password" />

        <x-ui.button type="submit" size="lg" class="w-full">Buat akun</x-ui.button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">
        Sudah punya akun?
        <a href="{{ route('login') }}"
           class="rounded font-semibold text-indigo-700 underline underline-offset-2 hover:text-indigo-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-indigo-300 dark:hover:text-indigo-200">Masuk</a>
    </p>

    <script>
        document.querySelector('[data-error-summary]')?.focus();
    </script>
</x-layouts.guest>