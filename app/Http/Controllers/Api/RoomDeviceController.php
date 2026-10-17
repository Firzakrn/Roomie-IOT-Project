<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomDeviceController extends Controller
{
    public function checkStatus($code)
    {
        $room = Room::where('code', $code)->first();

        if (!$room) {
            return response()->json(['status' => 'NOT_FOUND'], 404);
        }

        $now = Carbon::now();

        // Cek apakah ada jadwal reservasi aktif di jam ini
        $reservation = Reservation::where('room_id', $room->id)
            ->where('status', 'CONFIRMED')
            ->where('start_at', '<=', $now)
            ->where('end_at', '>=', $now)
            ->first();

        if ($reservation) {
            return response()->json([
                'status' => 'OCCUPIED',
                'course' => $reservation->course_name,
                'until'  => Carbon::parse($reservation->end_at)->format('H.i'),
            ]);
        }

        return response()->json([
            'status' => 'UNOCCUPIED',
            'course' => '',
            'until'  => '',
        ]);
    }
    public function updateSensor(Request $request, $code)
    {
        // Tangkap data yang dikirim dari Wokwi
        $statusPintu = $request->input('pintu'); // isinya nanti 'TERBUKA' atau 'TERTUTUP'

        // Nanti di sini kita simpan ke database untuk mengubah tulisan UNKNOWN di dashboard.
        // Untuk sekarang, kita buat Laravel menjawab "Pesan Diterima"
        return response()->json([
            'message' => 'Laporan dari ruangan ' . $code . ' diterima!',
            'status_pintu' => $statusPintu
        ]);
    }
}