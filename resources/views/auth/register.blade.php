@extends('auth.layout')
@section('title', 'Daftar')
@section('content')
  <h1 class="text-2xl font-bold">Buat akun Roomie ✨</h1>
  <p class="mb-6 mt-1 text-sm text-slate-500">Daftar sebagai mahasiswa untuk mulai reservasi.</p>
  @if($errors->any())<div role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-700"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
    @csrf
    <div><label for="name" class="mb-1.5 block text-sm font-semibold">Nama lengkap</label><input id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required class="w-full rounded-xl border border-slate-300 p-3 focus:border-blue-500" placeholder="Nama mahasiswa"></div>
    <div><label for="email" class="mb-1.5 block text-sm font-semibold">Email</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required class="w-full rounded-xl border border-slate-300 p-3 focus:border-blue-500" placeholder="nama@kampus.ac.id"></div>
    <div><label for="password" class="mb-1.5 block text-sm font-semibold">Kata sandi (minimal 8 karakter)</label><input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required class="w-full rounded-xl border border-slate-300 p-3 focus:border-blue-500"></div>
    <div><label for="password_confirmation" class="mb-1.5 block text-sm font-semibold">Ulangi kata sandi</label><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required class="w-full rounded-xl border border-slate-300 p-3 focus:border-blue-500"></div>
    <button class="w-full rounded-xl bg-blue-600 py-3 font-bold text-white hover:bg-blue-700">Buat Akun</button>
  </form>
  <p class="mt-6 text-center text-sm text-slate-600">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-blue-700 hover:underline">Masuk</a></p>
@endsection
