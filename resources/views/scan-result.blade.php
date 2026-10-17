
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Data Kelas dan Hasil Scan · Roomie</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-slate-50 p-4 font-sans text-slate-800 sm:p-8">

<main class="mx-auto max-w-xl">

  <nav class="mb-5 flex justify-between gap-3 text-sm font-bold text-blue-600">
    <a href="{{ route('dashboard') }}">← Dashboard</a>
    <a href="{{ route('reservation.history') }}">Riwayat Penggunaan</a>
  </nav>

  <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">

    @if(session('roomie_scan_action') === 'checkin')
      <div role="status" class="rounded-xl bg-emerald-50 p-4 text-emerald-900">
        <h1 class="text-xl font-bold">✓ Check-in Berhasil!</h1>
        <p class="mt-1 text-sm">
          Data mata kuliah berhasil disimpan.
          Ruangan sekarang sedang digunakan.
          Scan QR yang sama untuk check-out.
        </p>
      </div>

    @elseif(session('roomie_scan_action') === 'checkout')
      <div role="status" class="rounded-xl bg-blue-50 p-4 text-blue-900">
        <h1 class="text-xl font-bold">✓ Check-out Berhasil!</h1>
        <p class="mt-1 text-sm">
          Penggunaan kelas selesai dan riwayat tersimpan.
        </p>
      </div>

    @elseif($showCheckinForm)
      <h1 class="text-2xl font-extrabold">Isi Data Kelas</h1>
      <p class="mt-1 text-sm text-slate-500">
        QR berhasil dibaca. Lengkapi data mata kuliah
        untuk melakukan check-in.
      </p>

    @else
      <h1 class="text-xl font-bold">Informasi Ruangan</h1>
      <p class="mt-1 text-sm text-slate-500">
        Informasi penggunaan ruangan berdasarkan QR kelas.
      </p>
    @endif

    <!-- INFORMASI KELAS OTOMATIS -->
    <div class="mt-5 rounded-xl bg-slate-50 p-5">
      <p class="text-xs font-bold uppercase text-slate-500">
        Ruangan Terdeteksi
      </p>

      <p class="mt-1 text-2xl font-extrabold">
        {{ $room->name }}
      </p>

      <p class="mt-1 text-sm text-slate-500">
        {{ $room->code }} ·
        Gedung {{ $room->building }} ·
        Lantai {{ $room->floor }}
      </p>
    </div>

    @if($errors->any())
      <div role="alert" class="mt-4 rounded-xl bg-rose-50 p-4 text-sm text-rose-700">
        {{ $errors->first() }}
      </div>
    @endif

    @if($showCheckinForm)

      <!-- FORM CHECK-IN -->
      <form method="POST"
            action="{{ route('scan.checkin') }}"
            class="mt-5 space-y-4">

        @csrf

        <!-- NAMA MATA KULIAH -->
        <div>
          <label for="course_name"
                 class="mb-1.5 block text-sm font-semibold">
            Nama Mata Kuliah
          </label>

          <input
            id="course_name"
            name="course_name"
            type="text"
            maxlength="255"
            required
            autofocus
            value="{{ old('course_name') }}"
            placeholder="Contoh: Pemrograman Web"
            class="w-full rounded-xl border border-slate-300 p-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
        </div>

        <!-- JUMLAH SKS -->
        <div>
          <label for="sks"
                 class="mb-1.5 block text-sm font-semibold">
            Jumlah SKS
          </label>

          <select
            id="sks"
            name="sks"
            required
            data-minutes-per-sks="{{ (int) config('roomie.minutes_per_sks', 50) }}"
            data-grace-minutes="{{ (int) config('roomie.grace_minutes', 60) }}"
            class="w-full rounded-xl border border-slate-300 bg-white p-3">

            <option value="">Pilih SKS</option>

            @for($i = 1; $i <= 6; $i++)
              <option value="{{ $i }}" @selected(old('sks') == $i)>
                {{ $i }} SKS
              </option>
            @endfor

          </select>
        </div>

        <!-- PERHITUNGAN DURASI OTOMATIS -->
        <div class="rounded-xl bg-blue-50 p-4 text-sm text-blue-900"
             aria-live="polite">

          <p class="font-semibold">
            Perhitungan Waktu Otomatis
          </p>

          <div class="mt-3 space-y-2">

            <p>
              Durasi Perkuliahan:
              <strong id="class-minutes">—</strong>
            </p>

            <p>
              Tambahan Waktu:
              <strong>
                {{ (int) config('roomie.grace_minutes', 60) }} menit
              </strong>
            </p>

            <hr class="border-blue-200">

            <p class="font-bold">
              Total Waktu Penggunaan:
              <strong id="total-minutes">Pilih SKS</strong>
            </p>

          </div>

          <p class="mt-3 text-xs">
            Rumus: (Jumlah SKS × 50 menit) + 60 menit.
          </p>

          <p class="mt-2 text-xs">
            Waktu check-in dimulai saat tombol konfirmasi
            ditekan. Batas waktu ditentukan otomatis
            oleh server.
          </p>
        </div>

        <!-- TOMBOL CHECK-IN -->
        <button
          type="submit"
          class="w-full rounded-xl bg-blue-600 px-5 py-3.5 font-bold text-white hover:bg-blue-700">

          Konfirmasi Check-in

        </button>

      </form>

      <!-- JAVASCRIPT PERHITUNGAN SKS -->
      <script>
        (() => {
          const sksSelect = document.getElementById('sks');
          const classMinutes = document.getElementById('class-minutes');
          const totalMinutes = document.getElementById('total-minutes');

          const minutesPerSks = Number(
            sksSelect.dataset.minutesPerSks
          );

          const graceMinutes = Number(
            sksSelect.dataset.graceMinutes
          );

          function preview() {
            const sks = Number(sksSelect.value);

            if (!sks) {
              classMinutes.textContent = '—';
              totalMinutes.textContent = 'Pilih SKS';
              return;
            }

            const durasiKuliah = sks * minutesPerSks;
            const totalDurasi = durasiKuliah + graceMinutes;

            classMinutes.textContent = durasiKuliah + ' menit';
            totalMinutes.textContent =
              totalDurasi + ' menit (' +
              Math.floor(totalDurasi / 60) + ' jam ' +
              (totalDurasi % 60) + ' menit)';
          }

          sksSelect.addEventListener('change', preview);

          preview();
        })();
      </script>

    @else

      <!-- STATUS RUANGAN -->
      <div class="mt-5 rounded-xl p-4 {{ $activeSession ? 'bg-rose-50 text-rose-800' : 'bg-emerald-50 text-emerald-800' }}">

        <p class="font-bold">
          {{ $activeSession ? 'Sedang Digunakan' : 'Ruangan Tersedia' }}
        </p>

        @if($activeSession)

          <p class="mt-2 text-sm">
            {{ $mine ? 'Kamu sedang menggunakan kelas ini.' : 'Ruangan sedang digunakan pengguna lain.' }}
          </p>

          <p class="mt-2 text-sm">
            <strong>Mata Kuliah:</strong>
            {{ $activeSession->reservation?->course_name }}
          </p>

          <p class="mt-1 text-sm">
            <strong>Jumlah SKS:</strong>
            {{ $activeSession->reservation?->sks }}
          </p>

          <p class="mt-1 text-sm">
            <strong>Waktu Check-in:</strong>
            {{ $activeSession->checked_in_at?->format('H:i') }}
          </p>

          <p class="mt-1 text-sm">
            <strong>Kuliah Selesai:</strong>
            @if($activeSession->checked_in_at && $activeSession->reservation)
              {{ $activeSession->checked_in_at->copy()->addMinutes($activeSession->reservation->sks * (int) config('roomie.minutes_per_sks', 50))->format('H:i') }}
            @else
              -
            @endif
          </p>

          <p class="mt-1 text-sm">
            <strong>Batas Penggunaan:</strong>
            {{ $activeSession->reservation?->end_at?->format('H:i') }}
          </p>

          @if($mine)
            <p class="mt-3 text-xs">
              Scan QR yang sama untuk melakukan check-out
              sebelum batas waktu habis.
            </p>
          @endif

        @endif

      </div>

    @endif

    <!-- KEMBALI KE SCANNER -->
    <a href="{{ route('scanner') }}"
       class="mt-5 block rounded-xl border border-blue-200 bg-blue-50 p-3 text-center font-bold text-blue-700 hover:bg-blue-100">

      {{ $mine ? 'Scan Lagi untuk Check-out' : ($showCheckinForm ? 'Batal, Scan Ulang' : 'Buka Kamera untuk Scan') }}

    </a>

  </section>

</main>

</body>
</html>