<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title') · Roomie</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 flex items-center justify-center p-5">
  <main class="w-full max-w-md">
    <a href="{{ route('login') }}" class="mx-auto mb-6 flex items-center justify-center gap-2 text-3xl font-extrabold tracking-tight text-blue-700"><span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-600 text-white">⌂</span>Roomie</a>
    <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-xl shadow-slate-200/40 sm:p-9">
      @yield('content')
    </div>
    <p class="mt-5 text-center text-xs text-slate-400">Roomie · Sistem reservasi ruang kampus</p>
  </main>
</body>
</html>
