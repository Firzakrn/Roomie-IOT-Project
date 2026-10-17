<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Scan QR untuk Check-in / Check-out · Roomie</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="min-h-screen bg-slate-50 p-4 text-slate-800 sm:p-8">
  <main class="mx-auto max-w-xl">
    <nav class="mb-5 flex justify-between"><a href="{{ route('dashboard') }}" class="font-semibold text-blue-600">← Dashboard</a><a href="{{ route('reservation.history') }}" class="font-semibold text-blue-600">Riwayat</a></nav>
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
      <div class="mb-5 text-center"><div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-3xl">▦</div><h1 class="text-2xl font-bold">Scan QR Kelas</h1><p class="mt-1 text-sm text-slate-500">Datang ke depan kelas, arahkan kamera ke QR yang ditempel di pintu. Scan pertama = check-in, scan kedua = check-out.</p></div>
      @if(session('error'))<div role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div>@endif
      @if($errors->any())<div role="alert" class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
      <div id="reader" class="overflow-hidden rounded-xl border border-slate-200"></div>
      <p id="scan-message" role="status" class="mt-4 text-center text-sm text-slate-500">Menyiapkan kamera… Izinkan kamera saat diminta browser.</p>
      <p class="mt-3 text-center text-xs text-slate-400">Gunakan HTTPS pada HP (contoh: Cloudflare Tunnel). Tidak ada input token manual atau booking dari dashboard.</p>
      <form action="{{ route('scan.process') }}" id="scan-form" method="POST" class="hidden">@csrf<input type="hidden" id="qr-token" name="qr_token"></form>
    </section>
  </main>
  <script>
    const statusText = document.getElementById('scan-message');
    const form = document.getElementById('scan-form');
    let scanningDone = false;
    function tokenFromQR(value) {
      const raw = String(value || '').trim();
      // QR Roomie dicetak admin sebagai token acak, bukan URL.
      return /^[A-Za-z0-9]{16,128}$/.test(raw) ? raw : null;
    }
    function handleResult(value) {
      if (scanningDone) return;
      const token = tokenFromQR(value);
      if (!token) {
        statusText.textContent = 'QR ini tidak sesuai format Roomie. Gunakan QR dari kelas.';
        statusText.className = 'mt-4 text-center text-sm text-red-600';
        return;
      }
      scanningDone = true;
      statusText.textContent = 'QR terbaca! Memproses check-in atau check-out…';
      document.getElementById('qr-token').value = token;
      form.submit();
    }
    if (typeof Html5Qrcode === 'undefined') {
      statusText.textContent = 'Komponen scanner tidak dimuat. Periksa internet lalu muat ulang.';
    } else {
      const reader = new Html5Qrcode('reader');
      Html5Qrcode.getCameras().then(cameras => {
        if (!cameras.length) throw new Error('Tidak ada kamera yang tersedia.');
        const preferred = cameras.find(c => /back|rear|environment|belakang/i.test(c.label)) || cameras[0];
        return reader.start(preferred.id, {fps: 10, qrbox: {width: 220, height: 220}}, handleResult, () => {});
      }).then(() => {
        statusText.textContent = 'Kamera siap. Arahkan ke QR yang ada di pintu kelas.';
      }).catch(error => {
        statusText.textContent = 'Tidak dapat membuka kamera. Pastikan izin kamera aktif dan website dibuka dengan HTTPS. ' + (error?.message || '');
      });
    }
  </script>
</body>
</html>
