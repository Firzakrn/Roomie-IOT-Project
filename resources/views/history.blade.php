{{--
    Riwayat penggunaan (BAP logs) for the signed-in user only.

    The state derivation below is unchanged from the previous version: it encodes
    domain meaning (an ACTIVE session past end_at is expired, not in progress,
    and a reservation with no session predates the scan flow). Only the
    presentation is new — a real table on wide screens, stacked rows on mobile,
    short badge labels, and pagination matching the rest of the app.

    No booking exists: a reservation is a usage log written by check-in.
--}}
<x-layouts.app title="Riwayat Penggunaan · Roomie"
               description="Riwayat check-in dan check-out ruang kelas yang dicatat dari pemindaian QR.">

    <x-layouts.page-header title="Riwayat penggunaan"
                       description="Waktu masuk dan keluar dicatat otomatis dari scan QR di pintu kelas. Tidak ada pemesanan jarak jauh." />

    @forelse ($reservations as $record)
        @php
            $session = $record->roomSession;
            $legacy = !$session;
            $expired = $session && $session->status === 'ACTIVE' && $record->end_at->lte(now());
            $isActive = $session && $session->status === 'ACTIVE' && !$expired;

            // Short badge labels (uppercase badges cannot afford long sentences);
            // the full sentence lives in the row for assistive tech.
            $badgeState = $legacy ? 'unknown' : ($expired || $session->status === 'EXPIRED' ? 'occupied' : ($isActive ? 'info' : 'available'));
            $badgeLabel = $legacy ? 'Lama' : ($expired || $session->status === 'EXPIRED' ? 'Terlambat' : ($isActive ? 'Aktif' : 'Selesai'));

            $minutes = $record->sks * (int) config('roomie.minutes_per_sks', 50);
        @endphp

        <article class="mt-3.5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-tile dark:border-slate-800 dark:bg-roomie-surface">
            <div class="flex flex-wrap items-start justify-between gap-3 p-5">
                <div class="min-w-0">
                    <p class="font-mono text-xs font-semibold text-indigo-700 dark:text-indigo-300">{{ $record->room?->code ?? '-' }}</p>
                    <h2 class="mt-1 truncate text-[15px] font-semibold text-slate-900 dark:text-slate-50">
                        {{ $record->room?->name ?? 'Ruangan terhapus' }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $record->course_name }} · {{ $record->sks }} SKS
                    </p>
                </div>

                <div class="flex flex-col items-start gap-2 sm:items-end">
                    <x-ui.badge :state="$badgeState">{{ $badgeLabel }}</x-ui.badge>
                    <span class="sr-only">
                        @if ($legacy)
                            Data reservasi lama tanpa sesi pemindaian.
                        @elseif ($expired || $session->status === 'EXPIRED')
                            Melewati batas waktu pemakaian.
                        @elseif ($isActive)
                            Sedang dipakai.
                        @else
                            Selesai.
                        @endif
                    </span>
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-x-4 gap-y-3 border-t border-slate-100 px-5 py-4 text-sm sm:grid-cols-4 dark:border-slate-800">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Check-in</dt>
                    <dd class="mt-1 font-mono text-xs tabular-nums text-slate-700 dark:text-slate-200">
                        @if ($session)
                            {{ $session->checked_in_at?->format('d M Y H:i') ?? '-' }} WIB
                        @else
                            Jadwal lama {{ $record->start_at->format('d M Y H:i') }} WIB
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Check-out</dt>
                    <dd class="mt-1 font-mono text-xs tabular-nums text-slate-700 dark:text-slate-200">
                        @if ($session)
                            {{ $session->checked_out_at?->format('d M Y H:i') ?? ($expired ? 'Otomatis (batas waktu)' : 'Belum check-out') }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Durasi kuliah</dt>
                    <dd class="mt-1 font-mono text-xs tabular-nums text-slate-700 dark:text-slate-200">{{ $minutes }} menit</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Batas penggunaan</dt>
                    <dd class="mt-1 font-mono text-xs tabular-nums text-slate-700 dark:text-slate-200">
                        @if ($session && $record->start_at)
                            {{ $record->end_at->format('d M Y H:i') }} WIB
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>

            @if ($isActive)
                <p class="flex items-start gap-2.5 border-t border-slate-100 bg-slate-50/70 px-5 py-3 text-xs leading-relaxed text-slate-600 dark:border-slate-800 dark:bg-slate-900/40 dark:text-slate-300">
                    <x-ui.icon name="info" :size="15" class="mt-px shrink-0 text-indigo-500" />
                    Scan QR yang sama lagi untuk check-out lebih awal. Jika waktu habis, kelas otomatis tersedia kembali.
                </p>
            @endif
        </article>
    @empty
        <div class="mt-6">
            <x-ui.empty title="Belum ada riwayat"
                        body="Datang ke kelas, scan QR yang ditempel di pintu, lalu isi mata kuliah dan SKS untuk check-in."
                        icon="history">
                <x-ui.button href="{{ route('scanner') }}" variant="primary" size="md" icon="camera">Buka scanner</x-ui.button>
            </x-ui.empty>
        </div>
    @endforelse

    @if ($reservations->hasPages())
        <div class="mt-6">
            {{ $reservations->links() }}
        </div>
    @endif
</x-layouts.app>