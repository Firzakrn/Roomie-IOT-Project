<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomSession;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Riwayat penggunaan demo untuk halaman "Riwayat penggunaan".
 *
 * Seeding riwayat jauh lebih sulit daripada seeding user atau room, karena
 * halaman ini menampilkan angka-angka yang harus saling cocok:
 *
 *   · end_at = start_at + (sks x 50 menit) + 60 menit toleransi — rumus yang
 *     sama dengan RoomSessionController::storeCheckin();
 *   · hanya boleh ada satu sesi berstatus ACTIVE per pengguna dan per ruangan,
 *     sesuai aturan pemakaian di AGENTS.md. Kalau dilanggar, dashboard
 *     menampilkan satu ruangan yang sebenarnya dipakai dua orang;
 *   · sesi EXPIRED berarti check-out-nya benar-benar terjadi SESUDAH batas
 *     waktu, kalau tidak label "Terlambat" jadi bohong;
 *   · baris tanpa room_sessions adalah data lama sebelum alur scan, bukan sesi
 *     yang gagal.
 *
 * Semua waktu dihitung relatif terhadap now() sehingga data tetap masuk akal
 * kapan pun seeder dijalankan.
 */
class HistorySeeder extends Seeder
{
    /** Sesi ACTIVE yang sengaja dibuat agar dashboard menampilkan timeline. */
    private const ACTIVE_COURSE = 'Basis Data Terapan';

    public function run(): void
    {
        $nadia = User::where('email', 'nadia@kampus.ac.id')->first();
        $admin = User::where('email', 'admin@kampus.ac.id')->first();
        $rooms = Room::orderBy('code')->get();

        if (! $nadia || ! $admin || $rooms->isEmpty()) {
            $this->command?->warn('HistorySeeder dilewati: user atau room belum ada. Jalankan DatabaseSeeder lebih dulu.');

            return;
        }

        // Idempotent. reservations tidak punya unique constraint, jadi seeding
        // kedua akan MENYALIN baris diam-diam — lebih berbahaya daripada gagal
        // keras. Karena itu: kalau user sudah punya riwayat, jangan sentuh.
        if (Reservation::where('user_id', $nadia->id)->exists()) {
            $this->command?->warn('Riwayat sudah ada, HistorySeeder dilewati (aman dijalankan ulang).');

            return;
        }

        $created = 0;

        foreach ($this->nadiaHistory() as $row) {
            $this->createUsage(
                user: $nadia,
                room: $rooms[$row['room'] % $rooms->count()],
                course: $row['course'],
                sks: $row['sks'],
                startedAgoMinutes: $row['ago'],
                sessionStatus: $row['session'],
                overshootMinutes: $row['overshoot'] ?? null
            );
            $created++;
        }

        foreach ($this->adminHistory() as $row) {
            $this->createUsage(
                user: $admin,
                room: $rooms[$row['room'] % $rooms->count()],
                course: $row['course'],
                sks: $row['sks'],
                startedAgoMinutes: $row['ago'],
                sessionStatus: $row['session'],
                overshootMinutes: $row['overshoot'] ?? null
            );
            $created++;
        }

        $this->command?->info("HistorySeeder: {$created} catatan riwayat dibuat.");
    }

    /**
     * Riwayat mahasiswa utama. 16 baris supaya paginasi 10/halaman ikut terlihat.
     *
     * Kolom: course, sks, mulai N menit yang lalu, status sesi, menit lewat batas.
     * null pada sesi = baris lama tanpa room_sessions ("Lama").
     */
    private function nadiaHistory(): array
    {
        return [
            // Masih berjalan: bento wide, timeline, notifikasi "sesi kamu".
            ['course' => self::ACTIVE_COURSE, 'sks' => 4, 'ago' => 25, 'session' => 'ACTIVE', 'room' => 1],

            ['course' => 'Pemrograman Web', 'sks' => 3, 'ago' => 300, 'session' => 'COMPLETED', 'room' => 0],
            ['course' => 'Jaringan Komputer', 'sks' => 3, 'ago' => 700, 'session' => 'EXPIRED', 'overshoot' => 60, 'room' => 2],
            ['course' => 'Kalkulus II', 'sks' => 4, 'ago' => 1100, 'session' => 'COMPLETED', 'room' => 1],
            ['course' => 'Pemrograman Berorientasi Objek', 'sks' => 3, 'ago' => 1560, 'session' => 'COMPLETED', 'room' => 0],

            // Tanpa room_sessions: data sebelum alur scan.
            ['course' => 'Basis Data (lama)', 'sks' => 2, 'ago' => 2900, 'session' => null, 'room' => 0],

            ['course' => 'Struktur Data', 'sks' => 4, 'ago' => 3300, 'session' => 'COMPLETED', 'room' => 2],
            ['course' => 'Sistem Operasi', 'sks' => 3, 'ago' => 4300, 'session' => 'EXPIRED', 'overshoot' => 50, 'room' => 1],
            ['course' => 'Jaringan Komputer', 'sks' => 3, 'ago' => 5800, 'session' => 'COMPLETED', 'room' => 0],
            ['course' => 'Pemrograman Web', 'sks' => 2, 'ago' => 7000, 'session' => 'COMPLETED', 'room' => 2],

            ['course' => 'Basis Data Terapan', 'sks' => 4, 'ago' => 8500, 'session' => 'COMPLETED', 'room' => 1],
            ['course' => 'Aljabar Linear', 'sks' => 2, 'ago' => 9000, 'session' => null, 'room' => 2],

            ['course' => 'Struktur Data', 'sks' => 4, 'ago' => 11500, 'session' => 'COMPLETED', 'room' => 0],
            ['course' => 'Kalkulus II', 'sks' => 4, 'ago' => 14400, 'session' => 'COMPLETED', 'room' => 2],
            ['course' => 'Sistem Operasi', 'sks' => 3, 'ago' => 20160, 'session' => 'COMPLETED', 'room' => 1],
            ['course' => 'Pemrograman Web', 'sks' => 2, 'ago' => 30240, 'session' => 'COMPLETED', 'room' => 0],
        ];
    }

    /** Riwayat pendek untuk admin, tanpa sesi ACTIVE supaya demo scan tidak terblokir. */
    private function adminHistory(): array
    {
        return [
            ['course' => 'Audit TI', 'sks' => 2, 'ago' => 240, 'session' => 'COMPLETED', 'room' => 0],
            ['course' => 'Rapat WORKS', 'sks' => 2, 'ago' => 1500, 'session' => 'COMPLETED', 'room' => 1],
            ['course' => 'Pelatihan QR', 'sks' => 2, 'ago' => 6000, 'session' => 'EXPIRED', 'overshoot' => 40, 'room' => 2],
        ];
    }

    /**
     * Membuat satu catatan penggunaan yang konsisten dengan aturan domain.
     */
    private function createUsage(
        User $user,
        Room $room,
        string $course,
        int $sks,
        int $startedAgoMinutes,
        ?string $sessionStatus,
        ?int $overshootMinutes = null
    ): void {
        $minutesPerSks = (int) config('roomie.minutes_per_sks', 50);
        $graceMinutes = (int) config('roomie.grace_minutes', 60);

        $startAt = now()->subMinutes($startedAgoMinutes);

        // Rumus identik dengan RoomSessionController::storeCheckin().
        $endAt = $startAt->copy()->addMinutes($sks * $minutesPerSks + $graceMinutes);

        $isActive = $sessionStatus === 'ACTIVE';

        if ($isActive && $endAt->lte(now())) {
            // Jangan pernah seed sesi "aktif" yang sudah lewat batas: dashboard
            // akan menandai ruangan terpakai padahal pemakainya sudah selesai.
            $endAt = now()->addMinutes($minutesPerSks * $sks + $graceMinutes);
            $startAt = now();
        }

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'course_name' => $course,
            'sks' => $sks,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => match ($sessionStatus) {
                // Baris lama belum pernah melewati alur scan, jadi masih
                // berstatus CONFIRMED persis seperti data sebelum sistem ini.
                null => 'CONFIRMED',
                'ACTIVE' => 'CONFIRMED',
                default => 'COMPLETED',
            },
        ]);

        if ($sessionStatus === null) {
            // Baris lama: reservasi tanpa sesi pemindaian sama sekali.
            return;
        }

        if ($isActive) {
            $checkedOutAt = null;
        } elseif ($sessionStatus === 'EXPIRED') {
            // Sesi lewat batas: check-out benar-benar setelah end_at.
            $checkedOutAt = $endAt->copy()->addMinutes($overshootMinutes ?? 30);
        } else {
            // Sesi rapi: check-out sedikit sebelum end_at.
            $checkedOutAt = $endAt->copy()->subMinutes(12);
        }

        RoomSession::create([
            'reservation_id' => $reservation->id,
            'room_id' => $room->id,
            'user_id' => $user->id,
            'checked_in_at' => $startAt,
            'checked_out_at' => $checkedOutAt,
            'status' => $sessionStatus,
        ]);
    }
}
