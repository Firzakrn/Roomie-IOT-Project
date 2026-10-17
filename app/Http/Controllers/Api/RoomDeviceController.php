<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomDeviceController extends Controller
{
    private function verifyKey(Request $request): void
    {
        $configured = (string) config(
            'roomie.device_api_key', ''
        );

        if ($configured === '') {
            abort(response()->json([
                'message' => 'Device API key belum diatur.'
            ], 503));
        }

        if (!hash_equals(
            $configured,
            (string) $request->header('X-Device-Key', '')
        )) {
            abort(response()->json([
                'message' => 'Device API key tidak valid.'
            ], 401));
        }
    }

    // WOKWI MEMBACA STATUS KELAS
    public function checkStatus(Request $request, string $code)
    {
        $this->verifyKey($request);

        $room = Room::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$room) {
            return response()->json([
                'status' => 'NOT_FOUND'
            ], 404);
        }

        $session = RoomSession::with('reservation')
            ->where('room_id', $room->id)
            ->where('status', 'ACTIVE')
            ->whereHas('reservation', function ($q) {
                $q->where('status', 'CONFIRMED')
                  ->where('end_at', '>', now());
            })
            ->first();

        return response()->json([
            'status' => $session ? 'OCCUPIED' : 'UNOCCUPIED',
            'session_id' => $session?->id,
            'course' => $session?->reservation?->course_name ?? '',
            'until' => $session?->reservation?->end_at?->format('H:i') ?? '',
        ]);
    }

    // MENERIMA SENSOR DAN AUTO CHECK-OUT
    public function updateSensor(Request $request, string $code)
    {
        $this->verifyKey($request);

        $data = $request->validate([
            'pintu' => ['required', 'in:TERBUKA,TERTUTUP'],
            'gerak' => ['sometimes', 'boolean'],
            'auto_checkout' => ['sometimes', 'boolean'],
            'session_id' => ['nullable', 'integer', 'min:1'],
            'suhu' => ['sometimes', 'numeric', 'between:-40,125'],
        ]);

        // Auto check-out hanya boleh dikirim
        // jika PIR tidak mendeteksi gerakan.
        if (!empty($data['auto_checkout']) &&
            (($data['gerak'] ?? null) !== false ||
             empty($data['session_id']))) {

            return response()->json([
                'message' => 'Data auto check-out tidak valid.'
            ], 422);
        }

        $result = DB::transaction(function () use ($code, $data) {

            $room = Room::where('code', $code)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$room) {
                return null;
            }

            // Simpan pembacaan sensor
            $room->update([
                'sensor_status' => $data['pintu'],
                'sensor_payload' => $data,
                'sensor_updated_at' => now(),
            ]);

            $autoCheckoutDone = false;

            if (!empty($data['auto_checkout'])) {

                // Hanya boleh menutup sesi yang
                // sebelumnya dibaca oleh ESP32.
                $session = RoomSession::with('reservation')
                    ->whereKey($data['session_id'])
                    ->where('room_id', $room->id)
                    ->where('status', 'ACTIVE')
                    ->lockForUpdate()
                    ->first();

                if ($session &&
                    $session->reservation &&
                    $session->reservation->status === 'CONFIRMED') {

                    $now = now();

                    // Akhiri sesi penggunaan
                    $session->update([
                        'status' => 'EXPIRED',
                        'checked_out_at' => $now,
                    ]);

                    // Catat waktu selesai sebenarnya
                    $session->reservation->update([
                        'status' => 'COMPLETED',
                        'end_at' => $now,
                    ]);

                    $autoCheckoutDone = true;
                }
            }

            return [
                'room' => $room->code,
                'auto_checkout_done' => $autoCheckoutDone,
            ];

        }, 3);

        if (!$result) {
            return response()->json([
                'message' => 'Ruangan tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'message' => 'Sensor tersimpan.',
            'room' => $result['room'],
            'auto_checkout_done' => $result['auto_checkout_done'],
        ]);
    }
}
