{{--
    Room detail modal.

    Injected into the dashboard by openModal(), so it inherits the dashboard's
    token set. Keep these hooks intact — dashboard.blade.php depends on them:
      #room-modal            (toggles the `hidden` class)
      #countdown-timer       (with data-end-at, drives the countdown)
      #modal-title           (aria-labelledby target)
      closeModal()           (called via onclick)
      [data-modal-close]     (receives focus on open)
--}}
<div id="room-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal()"></div>

    <div class="flex min-h-screen items-end justify-center p-4 sm:items-center">
        <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white text-left shadow-2xl ring-1 ring-slate-900/10">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-3 border-b border-slate-200/80 bg-slate-50/80 px-5 py-4 sm:px-6">
                <div class="min-w-0">
                    <h2 id="modal-title" class="text-lg font-bold tracking-tight text-slate-900">Detail Ruangan</h2>
                    <p class="mt-0.5 truncate text-xs font-medium text-slate-500">
                        {{ $room->code }} · Gedung {{ $room->building ?? '-' }} · Lantai {{ $room->floor ?? '-' }}
                    </p>
                </div>
                <button
                    type="button"
                    data-modal-close
                    onclick="closeModal()"
                    class="-mr-1 flex size-9 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-200/60 hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-roomie-primary"
                >
                    <span class="sr-only">Tutup detail ruangan</span>
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="space-y-4 px-5 py-5 sm:px-6">

                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-lg font-extrabold leading-snug text-slate-900">{{ $room->name }}</p>
                        <p class="mt-0.5 text-xs font-medium text-slate-500">Kapasitas {{ $room->capacity ?? '-' }} orang</p>
                    </div>
                    <x-ui.status-badge :state="$reservation ? 'occupied' : 'available'">
                        {{ $reservation ? 'Sedang dipakai' : 'Tersedia' }}
                    </x-ui.status-badge>
                </div>

                @if ($reservation)
                    <div class="rounded-2xl border border-rose-200/80 bg-rose-50/60 p-4 shadow-xs">
                        <div class="mb-3 border-b border-rose-200/60 pb-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Mata Kuliah Berlangsung</p>
                            <p class="mt-1 text-base font-bold text-rose-950">{{ $reservation->course_name }}</p>
                            <p class="mt-0.5 text-xs text-rose-800">Dipesan oleh: <span class="font-semibold">{{ $reservation->user?->name ?? 'Pengguna tidak diketahui' }}</span></p>
                        </div>

                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-semibold text-rose-700">Waktu penggunaan</p>
                                <p class="mt-0.5 font-mono text-sm font-bold text-rose-950">
                                    {{ $reservation->start_at->format('H:i') }}–{{$reservation->end_at->format('H:i') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] font-semibold text-rose-700">Sisa waktu</p>
                                <p class="mt-0.5 font-mono text-2xl font-extrabold leading-none text-rose-600 tabular-nums"
                                   id="countdown-timer"
                                   data-end-at="{{ $reservation->end_at->toIso8601String() }}">--:--:--</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-4">
                        <p class="text-sm font-medium leading-relaxed text-slate-600">Tidak ada sesi penggunaan aktif untuk ruangan ini.</p>
                    </div>
                @endif

                {{-- IoT sensor panel. States plainly that this is a device
                     indication, not proof of room occupancy (DESIGN.md 3). --}}
                <div class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-slate-500">Laporan sensor perangkat</p>
                            <div class="mt-1.5 flex items-center gap-2">
                                <span class="font-mono text-base font-bold text-slate-900" id="modal-sensor-status">
                                    {{ $sensorFresh ? ($room->sensor_status ?? 'UNKNOWN') : 'UNKNOWN' }}
                                </span>
                                <x-ui.status-badge :state="$sensorFresh ? 'available' : 'unknown'" :sensor="true">
                                    {{ $sensorFresh ? 'Terbaru' : 'Tidak diketahui' }}
                                </x-ui.status-badge>
                            </div>
                            <p class="mt-2 text-xs leading-relaxed text-slate-500">
                                @if ($sensorFresh)
                                    Dilaporkan {{ $room->sensor_updated_at?->diffForHumans() }}.
                                @else
                                    Belum ada laporan ESP32 terbaru. Status ini bukan bukti ruangan kosong.
                                @endif
                            </p>
                        </div>
                        <span class="shrink-0 rounded-lg bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500 ring-1 ring-slate-200 shadow-2xs">IoT</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>