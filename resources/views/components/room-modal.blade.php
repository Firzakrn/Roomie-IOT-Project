<div id="room-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Latar belakang gelap transparan -->
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal()"></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <!-- Kotak Modal Utama -->
            <div class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all">
                
                <!-- Header Modal -->
                <div class="border-b border-roomie-line bg-slate-50 px-6 py-4 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-roomie-ink" id="modal-title">Detail Ruangan</h3>
                        <p class="text-sm text-slate-500">{{ $room->code }} · Gedung {{ $room->building ?? '-' }} · Lantai {{ $room->floor ?? '-' }}</p>
                    </div>
                    <button type="button" onclick="closeModal()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-200 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-roomie-blue">
                        <span class="sr-only">Tutup</span>
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Isi Konten Modal -->
                <div class="px-6 py-5">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <p class="text-2xl font-bold text-roomie-ink">{{ $room->name }}</p>
                            <p class="text-sm text-slate-500">Kapasitas {{ $room->capacity ?? '-' }} orang</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ring-inset {{ $reservation ? 'bg-rose-50 text-rose-700 ring-rose-600/15' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/15' }}">
                            <span class="size-1.5 rounded-full {{ $reservation ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                            {{ $reservation ? 'Sedang dipakai' : 'Tersedia' }}
                        </span>
                    </div>

                    @if ($reservation)
                        <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-4">
                            <div class="mb-3 border-b border-rose-100 pb-3">
                                <p class="text-xs font-medium uppercase tracking-wider text-rose-500">Mata Kuliah Berlangsung</p>
                                <p class="mt-1 text-lg font-bold text-rose-900">{{ $reservation->course_name }}</p>
                                <p class="text-sm text-rose-700">Dipesan oleh: {{ $reservation->user?->name ?? 'Pengguna tidak diketahui' }}</p>
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs text-rose-500">Waktu reservasi</p>
                                    <p class="font-mono text-sm font-bold text-rose-800">{{ $reservation->start_at->format('H:i') }} - {{ $reservation->end_at->format('H:i') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-rose-500">Sisa waktu</p>
                                    <p class="font-mono text-xl font-black text-rose-600" id="countdown-timer" data-end-at="{{ $reservation->end_at->toIso8601String() }}">--:--:--</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-600">Tidak ada reservasi aktif untuk ruangan ini.</p>
                        </div>
                    @endif

                    <div class="mt-4 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div>
                            <p class="text-xs font-medium text-slate-500">Status Sensor Pintu</p>
                            <p id="modal-sensor-status" class="font-bold text-roomie-ink">{{ $sensorFresh ? ($room->sensor_status ?? 'UNKNOWN') : 'UNKNOWN' }}</p><p class="mt-1 text-xs text-slate-500">{{ $room->sensor_updated_at?->diffForHumans() ?? 'Belum ada laporan Wokwi' }}</p>
                        </div>
                        <span class="text-xs text-slate-400">IoT</span>
                    </div>
                </div>

            </div>
        </div>
    </div>