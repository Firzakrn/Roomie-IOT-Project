<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Cetak QR Ruangan · Roomie</title><script src="https://cdn.tailwindcss.com"></script><script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<style>@media print{.no-print{display:none!important}body{background:#fff!important}.qr-card{break-inside:avoid;box-shadow:none!important}}</style></head>
<body class="bg-slate-50 p-5 text-slate-800 sm:p-8"><main class="mx-auto max-w-5xl">
<nav class="no-print mb-6 flex justify-between"><a href="{{ route('dashboard') }}" class="font-bold text-blue-600">← Dashboard</a><button onclick="window.print()" class="rounded-lg bg-blue-600 px-4 py-2 font-bold text-white">Cetak QR</button></nav>
<h1 class="text-2xl font-bold">QR Kelas Roomie</h1><p class="no-print mt-2 text-sm text-slate-600">Tempel QR ini pada masing-masing kelas. Isinya token ruangan, tidak terikat alamat Cloudflare yang dapat berubah. Pengguna perlu login pada website Roomie untuk memindainya.</p>
<div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
@foreach($rooms as $room)
<div class="qr-card rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm"><h2 class="text-xl font-bold">{{ $room->name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $room->code }} · Gedung {{ $room->building }}</p><div id="qr-{{ $room->id }}" class="mx-auto mt-5 flex min-h-[190px] items-center justify-center"></div><p class="mt-4 text-sm font-semibold text-blue-700">Scan melalui dashboard Roomie</p></div>
@endforeach
</div>
</main>
<script>
@if($rooms->count())
if(typeof QRCode !== 'undefined') {
@foreach($rooms as $room)
new QRCode(document.getElementById(@json('qr-'.$room->id)), {text: @json($room->qr_token), width: 190, height: 190, correctLevel: QRCode.CorrectLevel.M});
@endforeach
}
@endif
</script></body></html>
