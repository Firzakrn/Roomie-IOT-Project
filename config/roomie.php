<?php

return [
    // Kunci rahasia API Wokwi (tetap simpan nilai aslinya di .env).
    'device_api_key' => env('ROOMIE_DEVICE_API_KEY', ''),

    // Durasi otomatis: jumlah SKS x 50 menit, lalu +60 menit masa tambahan.
    // Waktu dimulai ketika form check-in dikonfirmasi, bukan ketika QR dibaca.
    'minutes_per_sks' => 50,
    'grace_minutes' => 60,

    // Konfigurasi lama dipertahankan agar tidak merusak pemanggil yang sudah ada.
    // Alur check-in otomatis tidak menggunakan opsi durasi manual ini.
    'max_session_hours' => env('ROOMIE_MAX_SESSION_HOURS', 8),
];
