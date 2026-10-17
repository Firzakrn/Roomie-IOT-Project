<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $roomsData = Room::where('is_active', true)->get();
        $now = Carbon::now(); // Ambil waktu detik ini secara real-time

        // Format data ruangan untuk dashboard
        $rooms = $roomsData->map(function($room) use ($now) {
            
            // Cek apakah ada reservasi di ruangan ini yang waktunya mencakup JAM SAAT INI
            $isOccupied = Reservation::where('room_id', $room->id)
                ->where('status', 'CONFIRMED')
                ->where('start_at', '<=', $now)
                ->where('end_at', '>=', $now)
                ->exists();

            return [
                'code' => $room->code,
                'name' => $room->name,
                'building' => 'Gedung ' . $room->building . ' · Lantai ' . $room->floor,
                // Jika isOccupied TRUE, ganti status jadi occupied. Jika FALSE, available.
                'availability' => $isOccupied ? 'occupied' : 'available',
                'sensor' => 'UNKNOWN', 
                'updated' => 'Sistem aktif'
            ];
        });

        $totalRooms = $rooms->count();
        $availableRooms = $rooms->where('availability', 'available')->count();
        $occupiedRooms = $rooms->where('availability', 'occupied')->count();
        $unknownRooms = $rooms->where('availability', 'unknown')->count();

        return view('dashboard', compact(
            'rooms', 'totalRooms', 'availableRooms', 'occupiedRooms', 'unknownRooms'
        ));
    }

    public function handleScan($token)
    {
        // 1. Cari ruangan yang qr_token-nya cocok dengan hasil scan
        $room = Room::where('qr_token', $token)->first();

        // 2. Jika token tidak valid / ruangan tidak ada, tendang balik ke dashboard
        if (!$room) {
            // (Nanti kita bisa tambahkan alert error di dashboard)
            return redirect()->route('dashboard'); 
        }

        // 3. Jika ruangan ketemu, buka halaman form reservasi untuk ruangan tersebut
        return view('scan-result', compact('room'));
    }
    public function storeReservation(Request $request)
    {
        // 1. Validasi data yang diketik oleh mahasiswa
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'course_name' => 'required|string|max:255',
            'sks' => 'required|integer|min:1|max:6',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at', // Waktu selesai harus setelah waktu mulai
        ]);

        // 2. Simpan ke database
        // CATATAN PENTING: Karena kita belum mengaktifkan fitur Login, 
        // kita "paksa" dulu anggap yang memesan adalah User ID 2 (Akun Mahasiswa di seeder).
        // Nanti jika fitur login sudah ada, ini diganti menjadi: auth()->id()
        $userId = 2; 

        \App\Models\Reservation::create([
            'user_id' => $userId,
            'room_id' => $request->room_id,
            'course_name' => $request->course_name,
            'sks' => $request->sks,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'status' => 'CONFIRMED', // Langsung confirmed sesuai aturan PRD
        ]);

        // 3. Kembalikan pengguna ke Dashboard setelah sukses
        return redirect()->route('dashboard')->with('success', 'Reservasi mata kuliah ' . $request->course_name . ' berhasil dibuat!');
    }
}