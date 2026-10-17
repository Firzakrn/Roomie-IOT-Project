{{--
    Riwayat penggunaan (BAP logs) for the signed-in user only.

    Two presentations of ONE dataset:

      * >=768px — a real <table>, so assistive tech gets column headers and
        row navigation instead of a list of loose boxes;
      * <768px — stacked cards, where a 6-column table would mean sideways
        scrolling.

    The state derivation below is unchanged from the original version: it encodes
    domain meaning, and changing it would change what the log claims. An ACTIVE
    session past end_at is expired, not in progress; a reservation with no
    session predates the scan flow. It is computed once into $rows and rendered
    twice, so the two presentations can never disagree.

    No booking exists: a reservation is a usage log written by check-in.
--}}
<x-layouts.app title="Riwayat Penggunaan · Roomie"
               description="Riwayat check-in dan check-out ruang kelas yang dicatat dari pemindaian QR.">

    @php
        $grace = (int) config('roomie.grace_minutes', 60);

        $rows = $reservations->map(function ($record) use ($grace) {
            $session = $record->roomSession;
            $legacy = !$session;
            $expired = $session && $session->status === 'ACTIVE' && $record->end_at->lte(now());
            $isActive = $session && $session->status === 'ACTIVE' && !$expired;
            $late = ! $legacy && ($expired || $session->status === 'EXPIRED');

            // Short labels: an uppercase chip cannot afford a sentence. The full
            // wording is kept for screen readers in the table's scope attribute.
            if ($legacy) {
                $badgeState = 'unknown';
                $badgeLabel = 'Lama';
                $badgeMeaning = 'Data reservasi lama tanpa sesi pemindaian';
            } elseif ($late) {
                $badgeState = 'occupied';
                $badgeLabel = 'Terlambat';
                $badgeMeaning = 'Melewati batas waktu pemakaian';
            } elseif ($isActive) {
                $badgeState = 'indigo';
                $badgeLabel = 'Aktif';
                $badgeMeaning = 'Sesi sedang berjalan';
            } else {
                $badgeState = 'available';
                $badgeLabel = 'Selesai';
                $badgeMeaning = 'Penggunaan selesai';
            }

            return [
                'record' => $record,
                'code' => $record->room?->code ?? '-',
                'name' => $record->room?->name ?? 'Ruangan terhapus',
                'course' => $record->course_name,
                'sks' => (int) $record->sks,
                'lecture' => (int) $record->sks * (int) config('roomie.minutes_per_sks', 50),
                'in' => $session ? $session->checked_in_at : null,
                'out' => $session ? $session->checked_out_at : null,
                'out_text' => $session
                    ? ($session->checked_out_at
                        ? $session->checked_out_at->format('d M Y H:i')
                        : ($late ? 'Otomatis (batas waktu)' : 'Belum check-out'))
                    : '—',
                'limit' => $session ? $record->end_at : null,
                'is_active' => (bool) $isActive,
                'badge_state' => $badgeState,
                'badge_label' => $badgeLabel,
                'badge_meaning' => $badgeMeaning,
                'grace' => $grace,
            ];
        });
    @endphp

    <x-layouts.page-header title="Riwayat penggunaan"
                           description="Waktu masuk dan keluar dicatat otomatis dari scan QR di pintu kelas. Tidak ada pemesanan jarak jauh." />

    @if ($rows->isEmpty())
        <div class="mt-6">
            <x-ui.empty title="Belum ada riwayat"
                        body="Datang ke kelas, scan QR yang ditempel di pintu, lalu isi mata kuliah dan SKS untuk check-in."
                        icon="history">
                <x-ui.button href="{{ route('scanner') }}" variant="primary" size="md" icon="camera">Buka scanner</x-ui.button>
            </x-ui.empty>
        </div>
    @else
        {{-- Wide: real table. No overflow-hidden on the wrapper — that would make
             it a scroll container and silently break the sticky header — so the
             rounded corners are applied to the corner cells instead. --}}
        <div class="mt-6 hidden rounded-2xl border border-slate-200 bg-white shadow-tile md:block dark:border-slate-800 dark:bg-roomie-surface">
            <table class="w-full border-collapse text-left text-sm">
                <caption class="sr-only">Riwayat penggunaan ruang kelas, terbaru lebih dulu</caption>

                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800">
                        <th scope="col"
                            class="sticky top-16 z-10 rounded-tl-2xl bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:bg-slate-900/90 dark:text-slate-400">
                            Ruangan
                        </th>
                        <th scope="col"
                            class="bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:bg-slate-900/90 dark:text-slate-400">
                            Mata kuliah
                        </th>
                        <th scope="col"
                            class="hidden bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 lg:table-cell dark:bg-slate-900/90 dark:text-slate-400">
                            Check-in
                        </th>
                        <th scope="col"
                            class="hidden bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 lg:table-cell dark:bg-slate-900/90 dark:text-slate-400">
                            Check-out
                        </th>
                        <th scope="col"
                            class="hidden bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 xl:table-cell dark:bg-slate-900/90 dark:text-slate-400">
                            Batas
                        </th>
                        <th scope="col"
                            class="rounded-tr-2xl bg-slate-50 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:bg-slate-900/90 dark:text-slate-400">
                            Status
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($rows as $row)
                        <tr class="align-top transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                            <th scope="row" class="px-4 py-3.5 font-normal">
                                <span class="block font-mono text-xs font-semibold text-indigo-700 dark:text-indigo-300">{{ $row['code'] }}</span>
                                <span class="mt-0.5 block max-w-[16rem] truncate text-[15px] font-semibold text-slate-900 dark:text-slate-50">{{ $row['name'] }}</span>
                            </th>

                            <td class="px-4 py-3.5">
                                <span class="block max-w-[16rem] truncate text-slate-900 dark:text-slate-100">{{ $row['course'] }}</span>
                                <span class="mt-0.5 block font-mono text-[11px] tabular-nums text-slate-500 dark:text-slate-400">
                                    {{ $row['sks'] }} SKS · {{ $row['lecture'] }} menit
                                </span>
                            </td>

                            <td class="hidden px-4 py-3.5 font-mono text-xs tabular-nums text-slate-600 lg:table-cell dark:text-slate-300">
                                @if ($row['in'])
                                    {{ $row['in']->format('d M Y H:i') }}
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">{{ $row['record']->start_at->format('d M Y H:i') }}</span>
                                @endif
                            </td>

                            <td class="hidden px-4 py-3.5 font-mono text-xs tabular-nums text-slate-600 lg:table-cell dark:text-slate-300">
                                {{ $row['out_text'] }}
                            </td>

                            <td class="hidden px-4 py-3.5 font-mono text-xs tabular-nums text-slate-600 xl:table-cell dark:text-slate-300">
                                @if ($row['limit'])
                                    {{ $row['limit']->format('d M Y H:i') }}
                                    <span class="mt-0.5 block text-[11px] text-slate-500 dark:text-slate-400">+{{ $row['grace'] }} mnt</span>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-4 py-3.5 text-right">
                                <x-ui.badge :state="$row['badge_state']">{{ $row['badge_label'] }}</x-ui.badge>
                                <span class="sr-only">{{ $row['badge_meaning'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Narrow: stacked cards, which is where the full detail has room. --}}
        <div class="mt-4 space-y-3.5 md:hidden">
            @foreach ($rows as $row)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-tile dark:border-slate-800 dark:bg-roomie-surface">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-xs font-semibold text-indigo-700 dark:text-indigo-300">{{ $row['code'] }}</p>
                            <h2 class="mt-1 text-[15px] font-semibold text-slate-900 dark:text-slate-50">{{ $row['name'] }}</h2>
                            <p class="mt-1 truncate text-sm text-slate-500 dark:text-slate-400">{{ $row['course'] }}</p>
                        </div>

                        <x-ui.badge :state="$row['badge_state']">{{ $row['badge_label'] }}</x-ui.badge>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-3 border-t border-slate-100 pt-4 text-sm dark:border-slate-800">
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Check-in</dt>
                            <dd class="mt-1 font-mono text-xs tabular-nums text-slate-700 dark:text-slate-200">
                                {{ $row['in'] ? $row['in']->format('d M Y H:i') : $row['record']->start_at->format('d M Y H:i') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Check-out</dt>
                            <dd class="mt-1 font-mono text-xs tabular-nums text-slate-700 dark:text-slate-200">{{ $row['out_text'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Durasi kuliah</dt>
                            <dd class="mt-1 font-mono text-xs tabular-nums text-slate-700 dark:text-slate-200">
                                {{ $row['sks'] }} SKS · {{ $row['lecture'] }} menit
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">Batas penggunaan</dt>
                            <dd class="mt-1 font-mono text-xs tabular-nums text-slate-700 dark:text-slate-200">
                                {{ $row['limit'] ? $row['limit']->format('d M Y H:i').' WIB' : '—' }}
                            </dd>
                        </div>
                    </dl>

                    @if ($row['is_active'])
                        <p class="mt-4 flex items-start gap-2.5 border-t border-slate-100 pt-3 text-xs leading-relaxed text-slate-600 dark:border-slate-800 dark:text-slate-300">
                            <x-ui.icon name="info" :size="15" class="mt-px shrink-0 text-indigo-500" />
                            Scan QR yang sama lagi untuk check-out lebih awal. Jika waktu habis, kelas otomatis tersedia kembali.
                        </p>
                    @endif
                </article>
            @endforeach
        </div>

        @if ($reservations->hasPages())
            <div class="mt-6">
                {{ $reservations->links() }}
            </div>
        @endif
    @endif
</x-layouts.app>