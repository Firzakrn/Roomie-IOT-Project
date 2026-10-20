<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedRooms();

        $this->call(HistorySeeder::class);
    }

    /**
     * Akun demo.
     *
     * firstOrCreate, bukan create: kalau seed dijalankan ulang, akun yang sudah
     * ada TIDAK ditimpa. Menimpanya berarti mengganti kata sandi orang yang
     * mungkin sudah berubah.
     */
    private function seedUsers(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@unesa.ac.id'],
            [
                'name' => 'Administrator Roomie',
                'password' => 'password123',
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'nadia@unesa.ac.id'],
            [
                'name' => 'Nadia Putri',
                'password' => 'password123',
                'role' => 'mahasiswa',
            ]
        );
    }

    /**
     * Ruang kelas + token QR.
     *
     * firstOrCreate dipakai DELIBERAT untuk room: qr_token dicetak ke kertas
     * dan ditempel di pintu. Kalau seeder mengacak ulang token pada penulisan
     * ulang, semua label yang sudah tercetak jadi mati tanpa sebab.
     */
    private function seedRooms(): void
    {
        $rooms = [
            ['code' => 'A10.01.01', 'name' => 'Ruang Kuliah 101', 'building' => 'A10', 'floor' => 1, 'capacity' => 40],
            ['code' => 'A10.01.02', 'name' => 'Ruang Kuliah 102', 'building' => 'A10', 'floor' => 1, 'capacity' => 45],
            ['code' => 'B08.01.03', 'name' => 'Lab Komputer 103', 'building' => 'B08', 'floor' => 1, 'capacity' => 30],
        ];

        foreach ($rooms as $room) {
            Room::firstOrCreate(
                ['code' => $room['code']],
                [
                    'name' => $room['name'],
                    'building' => $room['building'],
                    'floor' => $room['floor'],
                    'capacity' => $room['capacity'],
                    'qr_token' => Str::random(32),
                    'is_active' => true,
                ]
            );
        }
    }
}
