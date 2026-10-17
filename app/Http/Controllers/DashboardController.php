<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomSession;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function activeSessionQuery(Room $room, Carbon $now)
    {
        return RoomSession::where('room_id', $room->id)
            ->where('status', 'ACTIVE')
            ->whereHas('reservation', function ($query) use ($now) {
                $query->where('status', 'CONFIRMED')
                    ->where('end_at', '>', $now);
            });
    }

    private function roomSnapshot(Room $room, Carbon $now): array
    {
        $occupied = $this->activeSessionQuery($room, $now)->exists();

        $fresh = $room->sensor_updated_at &&
            $room->sensor_updated_at->greaterThan(
                $now->copy()->subSeconds(90)
            );

        return [
            'code' => $room->code,
            'name' => $room->name,
            'building' => 'Gedung ' . $room->building .
                ' · Lantai ' . $room->floor,
            'availability' => $occupied ? 'occupied' : 'available',
            'sensor' => $fresh
                ? ($room->sensor_status ?? 'UNKNOWN')
                : 'UNKNOWN',
            'updated' => $room->sensor_updated_at
                ? $room->sensor_updated_at->diffForHumans()
                : 'Belum ada data',
        ];
    }

    private function snapshots()
    {
        $now = now();

        return Room::where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(fn (Room $room) =>
                $this->roomSnapshot($room, $now)
            );
    }

    public function index()
    {
        $rooms = $this->snapshots();

        $totalRooms = $rooms->count();

        $availableRooms = $rooms
            ->where('availability', 'available')->count();

        $occupiedRooms = $rooms
            ->where('availability', 'occupied')->count();

        $unknownRooms = $rooms
            ->where('sensor', 'UNKNOWN')->count();

        return view('dashboard', compact(
            'rooms',
            'totalRooms',
            'availableRooms',
            'occupiedRooms',
            'unknownRooms'
        ));
    }

    public function live()
    {
        $rooms = $this->snapshots();

        return response()->json([
            'rooms' => $rooms,
            'summary' => [
                'total' => $rooms->count(),
                'available' => $rooms
                    ->where('availability', 'available')->count(),
                'occupied' => $rooms
                    ->where('availability', 'occupied')->count(),
                'unknown' => $rooms
                    ->where('sensor', 'UNKNOWN')->count(),
            ],
        ]);
    }

    public function scanner()
    {
        return view('scanner');
    }

    // Menampilkan hasil scan tanpa membuat reservasi
public function scanResult(string $code)
{
    // Mengenali kelas dari hasil scan QR
    $room = Room::where('code', $code)
        ->where('is_active', true)
        ->firstOrFail();

    $now = now();

    // Mengecek apakah kelas sedang digunakan
    $activeSession = $this->activeSessionQuery($room, $now)
        ->with(['reservation', 'user'])
        ->first();

    // Mengecek apakah pengguna adalah pemilik sesi
    $mine = $activeSession &&
        $activeSession->user_id === auth()->id();

    // Mengambil bukti scan QR dari session Laravel
    $pending = session('roomie_pending_scan');

    // Form hanya muncul setelah scan QR yang valid
    // dan ketika kelas belum digunakan
    $showCheckinForm = !$activeSession
        && is_array($pending)
        && ($pending['room_id'] ?? null) == $room->id
        && ($pending['user_id'] ?? null) == auth()->id()
        && ($pending['expires_at'] ?? 0) >= now()->timestamp;

    // Mengirim seluruh variabel ke halaman Blade
    return view('scan-result', compact(
        'room',
        'activeSession',
        'mine',
        'showCheckinForm'
    ));
}


    // Riwayat penggunaan ruangan
    public function history()
    {
        $reservations = Reservation::with([
            'room',
            'roomSession'
        ])
            ->where('user_id', auth()->id())
            ->orderByDesc('start_at')
            ->paginate(10);

        return view('history', compact('reservations'));
    }

    // Detail ruangan untuk modal dashboard
    public function getRoomModal(string $code)
    {
        $room = Room::where('code', $code)->firstOrFail();

        $now = now();

        $activeSession = $this->activeSessionQuery($room, $now)
            ->with(['reservation', 'user'])
            ->first();

        $reservation = $activeSession?->reservation;

        if ($reservation) {
            $reservation->setRelation(
                'user',
                $activeSession->user
            );
        }

        $sensorFresh = $room->sensor_updated_at &&
            $room->sensor_updated_at->greaterThan(
                $now->copy()->subSeconds(90)
            );

        return view('components.room-modal', compact(
            'room',
            'reservation',
            'sensorFresh'
        ));
    }

    // Halaman cetak QR khusus admin
    public function qrLabels()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $rooms = Room::where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('admin.qr-labels', compact('rooms'));
    }
}