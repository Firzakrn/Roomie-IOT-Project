@extends('auth.layout')
@section('title', 'Masuk')
@section('content')
  <h1 class="text-2xl font-bold">Selamat datang 👋</h1>
  <p class="mb-7 mt-1 text-sm text-slate-500">Masuk untuk reservasi dan scan QR ruangan.</p>
  @if($errors->any())<div role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
  <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
    @csrf
    <div><label for="email" class="mb-1.5 block text-sm font-semibold">Email</label><input id="email" type="email" name="email" autocomplete="username" value="{{ old('email') }}" required autofocus class="w-full rounded-xl border border-slate-300 p-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="nama@kampus.ac.id"></div>
    <div><label for="password" class="mb-1.5 block text-sm font-semibold">Kata sandi</label><input id="password" type="password" name="password" autocomplete="current-password" required class="w-full rounded-xl border border-slate-300 p-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="••••••••"></div>
    <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remember" value="1" class="rounded"> Ingat saya</label>
    <button class="w-full rounded-xl bg-blue-600 py-3 font-bold text-white hover:bg-blue-700">Masuk</button>
  </form>
  <p class="mt-6 text-center text-sm text-slate-600">Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-blue-700 hover:underline">Daftar</a></p>
@endsection
