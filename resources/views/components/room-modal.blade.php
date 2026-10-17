{{--
    Room detail modal.

    Fetched into the dashboard by openModal() and opened via
    window.roomieOpenDialog('room-modal'), so it needs no inline onclick
    handlers. It renders inside a native <dialog>: focus trapping, Escape and
    the scrim come from the platform, not from a hand-rolled focus trap.

    Dependency contract with dashboard.blade.php:
      #room-modal                        the dialog itself
      #modal-title                       aria-labelledby target
      #countdown-timer                   with data-end-at, drives the ticker
      data-countdown-fill                progress bar, kept in sync on tick
--}}

<x-ui.modal id="room-modal" title="Detail ruangan" title-id="modal-title"
            :subtitle="$room->code.' · Gedung '.($room->building ?? '-').' · Lantai '.($room->floor ?? '-')">
    <div class="space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[15px] font-semibold leading-snug text-slate-900 dark:text-slate-50">{{ $room->name }}</p>
                <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                    <x-ui.icon name="users" :size="14" />
                    Kapasitas {{ $room->capacity ?? '-' }} orang
                </p>
            </div>
            <x-ui.badge :state="$reservation ? 'occupied' : 'available'">
                {{ $reservation ? 'Sedang dipakai' : 'Tersedia' }}
            </x-ui.badge>
        </div>

        @if ($reservation)
            <div class="rounded-2xl border border-rose-200/80 bg-rose-50/50 p-4 dark:border-rose-500/30 dark:bg-rose-500/5">
                <p class="text-xs font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-300">Mata kuliah berlangsung</p>
                <p class="mt-1.5 text-[15px] font-semibold text-rose-950 dark:text-rose-100">{{ $reservation->course_name }}</p>
                <p class="mt-0.5 text-xs text-rose-800/80 dark:text-rose-200/70">
                    Dipakai oleh <span class="font-semibold">{{ $reservation->user?->name ?? 'Pengguna tidak diketahui' }}</span>
                </p>

                <div class="mt-4 border-t border-rose-200/70 pt-4 dark:border-rose-500/20">
                    <x-ui.timeline
                        :start="$reservation->start_at->toIso8601String()"
                        :end="$reservation->end_at->toIso8601String()"
                        :caption="$reservation->sks.' SKS'"
                        tone="rose" />
                </div>
            </div>

            <div class="flex items-end justify-between gap-4">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Sisa waktu dihitung server. Saat habis, kelas otomatis tersedia kembali.
                </p>
                <p id="countdown-timer"
                   data-countdown="clock"
                   data-end-at="{{ $reservation->end_at->toIso8601String() }}"
                   class="shrink-0 font-mono text-2xl font-bold leading-none tracking-tight tabular-nums text-rose-600 dark:text-rose-400">--:--:--</p>
            </div>
        @else
            <x-ui.alert tone="success" title="Ruangan tersedia">
                Tidak ada sesi penggunaan aktif. Scan QR yang ditempel di pintu untuk check-in.
            </x-ui.alert>
        @endif

        {{-- Device telemetry. States plainly that this is an indication from the
             ESP32, not proof of who is in the room. --}}
        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-900/40">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Laporan sensor ESP32</p>
                    <p class="mt-1.5 font-mono text-[15px] font-semibold text-slate-900 dark:text-slate-50">
                        {{ $sensorFresh ? ($room->sensor_status ?? 'UNKNOWN') : 'UNKNOWN' }}
                    </p>
                </div>
                <x-ui.badge :state="$sensorFresh ? 'available' : 'unknown'" :mono="true">
                    {{ $sensorFresh ? 'Terbaru' : 'Tidak diketahui' }}
                </x-ui.badge>
            </div>

            <p class="mt-2.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                @if ($sensorFresh)
                    Dilaporkan {{ $room->sensor_updated_at?->diffForHumans() }}. Status ini indikasi perangkat (PIR & door switch), bukan bukti fisik ruangan.
                @else
                    Belum ada laporan ESP32 dalam 90 detik terakhir. Status tidak diketahui, bukan berarti ruangan kosong.
                @endif
            </p>
        </div>
    </div>

    <x-slot:footer>
        <x-ui.button href="{{ route('scanner') }}" variant="primary" size="md" icon="camera">Scan QR</x-ui.button>
    </x-slot:footer>
</x-ui.modal>