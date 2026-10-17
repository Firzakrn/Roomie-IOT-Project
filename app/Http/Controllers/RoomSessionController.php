<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoomSessionController extends Controller
{
    /**
     * Scan pertama: hanya menyiapkan form data mata kuliah, BELUM check-in.
     * Scan berikutnya oleh pemegang sesi aktif: langsung check-out.
     */
    public function scan(Request $request)
    {
        $data = $request->validate([
            'qr_token' => ['required', 'string', 'regex:/^[A-Za-z0-9]{16,128}$/'],
        ]);

        $result = DB::transaction(function () use ($data) {
            $user = User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
            $room = Room::where('qr_token', $data['qr_token'])->lockForUpdate()->first();
            if (!$room) {
                throw ValidationException::withMessages(['qr_token' => 'QR tidak dikenal oleh Roomie.']);
            }

            $now = now();
            $this->expireOldSessions($room, $now);

            $mine = RoomSession::with('reservation')
                ->where('room_id', $room->id)
                ->where('user_id', $user->id)
                ->where('status', 'ACTIVE')
                ->whereHas('reservation', fn ($q) => $q->where('status', 'CONFIRMED')->where('end_at', '>', $now))
                ->first();

            if ($mine) {
                // Check-out hanya dilakukan oleh pemilik saat QR yang sama di-scan lagi.
                $mine->update(['status' => 'COMPLETED', 'checked_out_at' => $now]);
                $mine->reservation->update(['status' => 'COMPLETED', 'end_at' => $now]);
                return ['room' => $room->code, 'action' => 'checkout'];
            }

            if (!$room->is_active) {
                throw ValidationException::withMessages(['qr_token' => 'Ruangan dinonaktifkan admin.']);
            }
            if ($this->userHasActiveSession($user->id, $now)) {
                throw ValidationException::withMessages(['qr_token' => 'Kamu masih check-in di kelas lain. Scan QR kelas tersebut untuk check-out dahulu.']);
            }
            if ($this->roomIsOccupied($room->id, $now)) {
                throw ValidationException::withMessages(['qr_token' => 'Kelas sedang digunakan mahasiswa lain.']);
            }

            // QR valid dan kelas kosong: tampilkan FORM, bukan membuat record.
            return ['room' => $room->code, 'room_id' => $room->id, 'action' => 'form'];
        }, 3);

        if ($result['action'] === 'form') {
            // Data sesi disimpan di server untuk mencegah membuka URL hasil secara manual.
            $request->session()->put('roomie_pending_scan', [
                'room_id' => $result['room_id'],
                'user_id' => auth()->id(),
                'token_hash' => hash('sha256', $data['qr_token']),
                'expires_at' => now()->addMinutes(5)->timestamp,
            ]);
        } else {
            $request->session()->forget('roomie_pending_scan');
        }

        return redirect()->route('scan.result', ['code' => $result['room']])
            ->with('roomie_scan_action', $result['action']);
    }

    /** Konfirmasi form setelah scan pertama; baru sekarang ruangan menjadi DIPAKAI. */
    public function storeCheckin(Request $request)
    {
        // Mahasiswa tidak boleh menentukan jam atau durasi penggunaan.
        // Durasi dihitung di SERVER, bukan dari input browser:
        // jumlah SKS x 50 menit + 60 menit tambahan.
        $data = $request->validate([
            'course_name' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        $pending = $request->session()->get('roomie_pending_scan');
        if (!is_array($pending) ||
            ($pending['user_id'] ?? null) !== auth()->id() ||
            ($pending['expires_at'] ?? 0) < now()->timestamp ||
            empty($pending['room_id']) || empty($pending['token_hash'])) {
            return redirect()->route('scanner')->with('error', 'Sesi scan sudah habis. Scan QR di depan kelas lagi.');
        }

        $roomCode = DB::transaction(function () use ($pending, $data) {
            $user = User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
            $room = Room::whereKey($pending['room_id'])->lockForUpdate()->first();
            if (!$room || !$room->is_active ||
                !hash_equals($pending['token_hash'], hash('sha256', $room->qr_token))) {
                throw ValidationException::withMessages(['course_name' => 'Data QR tidak valid. Silakan scan kembali.']);
            }

            $now = now();
            $this->expireOldSessions($room, $now);
            // Cek ulang saat submit agar tidak bentrok jika pengguna lain lebih cepat.
            if ($this->userHasActiveSession($user->id, $now)) {
                throw ValidationException::withMessages(['course_name' => 'Kamu masih memakai kelas lain.']);
            }
            if ($this->roomIsOccupied($room->id, $now)) {
                throw ValidationException::withMessages(['course_name' => 'Ruangan sudah dipakai pengguna lain. Silakan scan ruangan lain.']);
            }

            // Jam check-in dan batas akhir memakai waktu SERVER saat konfirmasi.
            // Bagian 60 menit adalah toleransi/perpanjangan otomatis, bukan opsi input.
            $minutesPerSks = (int) config('roomie.minutes_per_sks', 50);
            $graceMinutes = (int) config('roomie.grace_minutes', 60);
            $totalMinutes = (int) $data['sks'] * $minutesPerSks + $graceMinutes;

            // Kompatibel dengan skema lama: reservations dipakai sebagai catatan
            // penggunaan dari scan, TIDAK membuka reservasi sebelum datang.
            $record = Reservation::create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'course_name' => $data['course_name'],
                'sks' => (int) $data['sks'],
                'start_at' => $now,
                'end_at' => $now->copy()->addMinutes($totalMinutes),
                'status' => 'CONFIRMED',
            ]);
            RoomSession::create([
                'reservation_id' => $record->id,
                'room_id' => $room->id,
                'user_id' => $user->id,
                'checked_in_at' => $now,
                'status' => 'ACTIVE',
            ]);
            return $room->code;
        }, 3);

        $request->session()->forget('roomie_pending_scan');
        return redirect()->route('scan.result', ['code' => $roomCode])
            ->with('roomie_scan_action', 'checkin');
    }

    private function userHasActiveSession(int $userId, $now): bool
    {
        return RoomSession::where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->whereHas('reservation', fn ($q) => $q->where('status', 'CONFIRMED')->where('end_at', '>', $now))
            ->exists();
    }

    private function roomIsOccupied(int $roomId, $now): bool
    {
        return RoomSession::where('room_id', $roomId)
            ->where('status', 'ACTIVE')
            ->whereHas('reservation', fn ($q) => $q->where('status', 'CONFIRMED')->where('end_at', '>', $now))
            ->exists();
    }

    private function expireOldSessions(Room $room, $now): void
    {
        $stale = RoomSession::with('reservation')
            ->where('room_id', $room->id)
            ->where('status', 'ACTIVE')
            ->whereHas('reservation', fn ($q) => $q->where('end_at', '<=', $now))
            ->get();
        foreach ($stale as $session) {
            $session->update(['status' => 'EXPIRED']);
            $session->reservation?->update(['status' => 'COMPLETED']);
        }
    }
}