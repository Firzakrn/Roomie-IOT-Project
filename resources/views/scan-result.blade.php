<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Ruangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 p-6 font-sans text-slate-800">

    <div class="mx-auto max-w-md rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h1 class="text-xl font-bold text-blue-600">Scan Berhasil!</h1>
        <p class="mt-1 text-sm text-slate-500">Silakan isi form reservasi di bawah ini.</p>

        <div class="mt-4 rounded-lg bg-blue-50 p-4">
            <p class="text-sm font-semibold text-blue-900">Ruangan Terpilih:</p>
            <p class="text-2xl font-bold text-blue-700">{{ $room->name }}</p>
            <p class="text-xs text-blue-600">{{ $room->code }} - Gedung {{ $room->building }}</p>
        </div>
        
        @if ($errors->any())
            <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 p-4">
                <p class="text-sm font-bold text-rose-800">Oops! Ada yang salah:</p>
                <ul class="mt-1 list-inside list-disc text-sm text-rose-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('reservation.store') }}" method="POST" class="mt-6 flex flex-col gap-4">
            @csrf
            
            <!-- Input Ruangan ID (Disembunyikan, karena sudah terisi otomatis dari hasil scan) -->
            <input type="hidden" name="room_id" value="{{ $room->id }}">

            <div>
                <label class="mb-1 block text-sm font-medium">Nama Mata Kuliah</label>
                <input type="text" name="course_name" required class="w-full rounded-lg border border-slate-300 p-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Jumlah SKS</label>
                <input type="number" name="sks" min="1" max="6" required class="w-full rounded-lg border border-slate-300 p-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Waktu Mulai</label>
                    <input type="datetime-local" name="start_at" required class="w-full rounded-lg border border-slate-300 p-2 text-sm outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Waktu Selesai</label>
                    <input type="datetime-local" name="end_at" required class="w-full rounded-lg border border-slate-300 p-2 text-sm outline-none">
                </div>
            </div>

            <button type="submit" class="mt-4 w-full rounded-xl bg-blue-600 p-3 text-sm font-bold text-white transition hover:bg-blue-700">
                Konfirmasi Booking
            </button>
            
            <a href="{{ route('dashboard') }}" class="text-center text-sm font-medium text-slate-500 hover:text-slate-700">
                Batal & Kembali
            </a>
        </form>
    </div>

</body>
</html>