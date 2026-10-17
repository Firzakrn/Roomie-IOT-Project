{{--
    Notification centre (DESIGN.md §4.A: "notifications center" in the topbar).

    There is no notification store in this app and PR2 deliberately adds no
    endpoint, so every item is DERIVED from the live room snapshot the dashboard
    already polls:

      · a room whose ESP32 report is older than 90s
      · a session that ends within the next 15 minutes
      · a session of the signed-in user (it needs a check-out)

    The empty state is a real state, not a placeholder. A control that can never
    be dismissed or acted on would be worse than no control, so each row links
    to the room detail or the scanner.
--}}
<div class="relative shrink-0" data-menu data-notifications>
    <button type="button" data-menu-trigger aria-expanded="false" aria-haspopup="true"
            aria-label="Pusat notifikasi"
            class="relative inline-flex size-10 items-center justify-center rounded-xl text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100">
        <x-ui.icon name="bell" :size="18" />

        {{-- WCAG "Label in Name": the visible count has to appear in the
             button's accessible name, so the name is rebuilt in script whenever
             the count changes. --}}
        <span data-notification-count aria-hidden="true"
              class="absolute -right-0.5 -top-0.5 hidden min-w-[18px] rounded-full bg-rose-600 px-1 text-center font-mono text-[10px] font-bold leading-[18px] text-white"></span>
    </button>

    <div data-menu-panel hidden
         class="absolute right-0 top-full z-50 mt-2 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-pop dark:border-slate-700 dark:bg-roomie-surface">
        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-50">Notifikasi</h2>
            <span class="font-mono text-[11px] tabular-nums text-slate-500 dark:text-slate-400" data-notification-meta>0 catatan</span>
        </div>

        <div data-notification-list class="max-h-80 overflow-y-auto p-1.5"></div>
    </div>
</div>