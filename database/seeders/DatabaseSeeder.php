<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Room;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DATA AKUN PENGGUNA (Untuk nanti ngetes fitur login)
        User::create([
            'name' => 'Administrator Roomie',
            'email' => 'admin@kampus.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Nadia Putri', // Mengikuti nama di desain HTML-mu
            'email' => 'nadia@kampus.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
        ]);

        // 2. DATA RUANG KELAS & TOKEN QR
        Room::create([
            'code' => 'A10.01.01',
            'name' => 'Ruang Kuliah 101',
            'building' => 'A10',
            'floor' => 1,
            'capacity' => 40,
            'qr_token' => Str::random(32), // Bikin token unik 32 karakter
            'is_active' => true,
        ]);

        Room::create([
            'code' => 'A10.01.02',
            'name' => 'Ruang Kuliah 102',
            'building' => 'A10',
            'floor' => 1,
            'capacity' => 45,
            'qr_token' => Str::random(32),
            'is_active' => true,
        ]);

        Room::create([
            'code' => 'B08.01.03',
            'name' => 'Lab Komputer 103',
            'building' => 'B08',
            'floor' => 1,
            'capacity' => 30,
            'qr_token' => Str::random(32),
            'is_active' => true,
        ]);
    }
}