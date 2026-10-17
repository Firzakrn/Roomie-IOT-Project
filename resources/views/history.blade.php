<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Riwayat Penggunaan Kelas · Roomie</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="min-h-screen bg-slate-50 p-4 text-slate-800 sm:p-8">
<main class="mx-auto max-w-5xl">
<nav class="mb-6 flex items-center justify-between gap-4"><a href="{{ route('dashboard') }}" class="font-bold text-blue-700">← Dashboard</a><a href="{{ route('scanner') }}" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white">Scan QR</a></nav>
<h1 class="text-3xl font-bold">Riwayat Penggunaan Kelas</h1><p class="mt-1 text-sm text-slate-500">Waktu masuk dan keluar otomatis dicatat dari scan QR. Tidak ada booking di muka.</p>
<div class="mt-6 grid gap-4">
@forelse($reservations as $record)
  @php
    $session = $record->roomSession;
    $oldBooking = !$session;
    $expired = $session && $session->status === 'ACTIVE' && $record->end_at->lte(now());
    $isActive = $session && $session->status === 'ACTIVE' && !$expired;
    $state = $oldBooking ? 'Data reservasi lama' : ($expired || $session->status === 'EXPIRED' ? 'Melewati batas waktu' : ($isActive ? 'Sedang dipakai' : 'Selesai'));
  @endphp
  <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="text-xs font-semibold uppercase tracking-wide text-blue-700">{{ $record->room?->code ?? '-' }}</p><h2 class="mt-1 text-lg font-bold">{{ $record->room?->name ?? 'Ruangan terhapus' }}</h2><p class="mt-1 text-sm text-slate-500">{{ $record->course_name }} · {{ $record->sks }} SKS</p></div>
    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $isActive ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-700' }}">{{ $state }}</span></div>
    <p class="mt-4 text-sm font-medium">@if($session)Check-in {{ $session->checked_in_at?->format('d M Y H:i') ?? '-' }} WIB @else Jadwal lama: {{ $record->start_at->format('d M Y H:i') }} WIB @endif</p>
    @if($session)<p class="mt-1 text-sm text-slate-500">Check-out {{ $session->checked_out_at?->format('d M Y H:i') ?? ($expired ? 'Otomatis selesai (batas waktu)' : 'Belum check-out') }} WIB</p>@endif
    @if($session && $record->start_at)
      <p class="mt-1 text-sm text-slate-500">Durasi kuliah {{ $record->sks * (int) config('roomie.minutes_per_sks', 50) }} menit + tambahan {{ (int) config('roomie.grace_minutes', 60) }} menit; batas penggunaan {{ $record->end_at->format('d M Y H:i') }} WIB</p>
    @endif
    @if($isActive)<p class="mt-3 text-sm font-semibold text-blue-700">Scan QR yang sama lagi untuk check-out lebih awal. Jika waktu habis, kelas otomatis tersedia.</p>@endif
  </article>
@empty
  <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center"><p class="text-lg font-bold">Belum ada riwayat</p><p class="mt-2 text-sm text-slate-500">Datang ke kelas, scan QR yang ditempel di pintu, lalu isi mata kuliah dan SKS untuk check-in.</p></div>
@endforelse
</div>
<div class="mt-6">{{ $reservations->links() }}</div>
</main>
</body>
</html>