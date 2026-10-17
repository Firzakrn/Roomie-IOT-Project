{{--
    Token layer + base styles. This is the ONLY inline <style> block in the app;
    everything else is Tailwind utilities driven by components/layouts/head.blade.php.

    State styling (badge / alert / stat tile / live dot) is expressed as CSS custom
    properties keyed off `data-state` and `data-tone`. That is deliberate: the
    dashboard poller only ever flips a data attribute, so there is exactly one
    source of truth for the colour mapping, and it covers light and dark at once.

    Palette follows DESIGN.md §2; every foreground/background pair below clears
    WCAG AA (>= 4.5:1) in its own theme.
--}}
<style>
    :root {
        color-scheme: light;
    }

    html {
        background-color: #F8FAFC;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }

    html.dark {
        color-scheme: dark;
        background-color: #090D16;
    }

    ::selection {
        background-color: #4F46E5;
        color: #FFFFFF;
    }

    /* ---------- Skip link ---------- */
    .skip-link {
        position: absolute;
        left: -9999px;
        top: 0;
        z-index: 100;
        background: #4F46E5;
        color: #FFFFFF;
        padding: 12px 20px;
        border-radius: 0 0 12px 0;
        font-size: 14px;
        font-weight: 600;
    }
    .skip-link:focus {
        left: 0;
        outline: 2px solid #4F46E5;
        outline-offset: 2px;
    }

    /* ---------- Badge ---------- */
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 9px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.3;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
        background: var(--badge-bg);
        color: var(--badge-fg);
        box-shadow: inset 0 0 0 1px var(--badge-ring);
    }
    .badge__dot {
        width: 6px;
        height: 6px;
        border-radius: 9999px;
        flex: none;
        background: currentColor;
    }
    .badge--mono {
        font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
        letter-spacing: .02em;
        text-transform: none;
    }

    .badge[data-state="available"],
    .badge[data-state="TERTUTUP"] {
        --badge-bg: #ECFDF5;
        --badge-fg: #047857;
        --badge-ring: #A7F3D0;
    }
    .badge[data-state="occupied"],
    .badge[data-state="TERBUKA"] {
        --badge-bg: #FEF2F2;
        --badge-fg: #B91C1C;
        --badge-ring: #FECACA;
    }
    .badge[data-state="attention"] {
        --badge-bg: #FFFBEB;
        --badge-fg: #B45309;
        --badge-ring: #FDE68A;
    }
    .badge[data-state="info"] {
        --badge-bg: #F0F9FF;
        --badge-fg: #0369A1;
        --badge-ring: #BAE6FD;
    }
    .badge[data-state="unknown"],
    .badge[data-state="UNKNOWN"] {
        --badge-bg: #F1F5F9;
        --badge-fg: #475569;
        --badge-ring: #CBD5E1;
    }

    html.dark .badge[data-state="available"],
    html.dark .badge[data-state="TERTUTUP"] {
        --badge-bg: #052E23;
        --badge-fg: #34D399;
        --badge-ring: #134E4A;
    }
    html.dark .badge[data-state="occupied"],
    html.dark .badge[data-state="TERBUKA"] {
        --badge-bg: #2B0D12;
        --badge-fg: #FB7185;
        --badge-ring: #7F1D1D;
    }
    html.dark .badge[data-state="attention"] {
        --badge-bg: #2A1D05;
        --badge-fg: #FBBF24;
        --badge-ring: #78350F;
    }
    html.dark .badge[data-state="info"] {
        --badge-bg: #082F49;
        --badge-fg: #38BDF8;
        --badge-ring: #075985;
    }
    html.dark .badge[data-state="unknown"],
    html.dark .badge[data-state="UNKNOWN"] {
        --badge-bg: #1E293B;
        --badge-fg: #CBD5E1;
        --badge-ring: #334155;
    }

    /* ---------- Alert ---------- */
    .alert {
        display: flex;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 12px;
        background: var(--alert-bg);
        color: var(--alert-fg);
        box-shadow: inset 3px 0 0 0 var(--alert-accent);
    }
    .alert__icon {
        flex: none;
        color: var(--alert-accent);
        margin-top: 1px;
    }
    .alert__title {
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
    }
    .alert__body {
        font-size: 14px;
        line-height: 1.6;
    }
    .alert__body a {
        font-weight: 600;
        text-decoration: underline;
    }

    .alert[data-tone="info"] {
        --alert-bg: #F0F9FF;
        --alert-fg: #0C4A6E;
        --alert-accent: #0EA5E9;
    }
    .alert[data-tone="success"] {
        --alert-bg: #ECFDF5;
        --alert-fg: #065F46;
        --alert-accent: #10B981;
    }
    .alert[data-tone="warning"] {
        --alert-bg: #FFFBEB;
        --alert-fg: #78350F;
        --alert-accent: #F59E0B;
    }
    .alert[data-tone="danger"] {
        --alert-bg: #FEF2F2;
        --alert-fg: #991B1B;
        --alert-accent: #EF4444;
    }

    html.dark .alert[data-tone="info"] {
        --alert-bg: #082F49;
        --alert-fg: #BAE6FD;
        --alert-accent: #38BDF8;
    }
    html.dark .alert[data-tone="success"] {
        --alert-bg: #052E23;
        --alert-fg: #A7F3D0;
        --alert-accent: #34D399;
    }
    html.dark .alert[data-tone="warning"] {
        --alert-bg: #2A1D05;
        --alert-fg: #FDE68A;
        --alert-accent: #FBBF24;
    }
    html.dark .alert[data-tone="danger"] {
        --alert-bg: #2B0D12;
        --alert-fg: #FECACA;
        --alert-accent: #FB7185;
    }

    /* ---------- Stat tile ---------- */
    .stat-tile__icon {
        display: inline-flex;
        flex: none;
        width: 36px;
        height: 36px;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: var(--tone-bg);
        color: var(--tone-fg);
        box-shadow: inset 0 0 0 1px var(--tone-ring);
    }
    .stat-tile__value {
        color: var(--tone-value, #0F172A);
    }

    .stat-tile[data-tone="neutral"] {
        --tone-bg: #F1F5F9;
        --tone-fg: #475569;
        --tone-ring: #E2E8F0;
    }
    .stat-tile[data-tone="available"] {
        --tone-bg: #ECFDF5;
        --tone-fg: #047857;
        --tone-ring: #A7F3D0;
        --tone-value: #047857;
    }
    .stat-tile[data-tone="occupied"] {
        --tone-bg: #FEF2F2;
        --tone-fg: #B91C1C;
        --tone-ring: #FECACA;
        --tone-value: #B91C1C;
    }
    .stat-tile[data-tone="attention"] {
        --tone-bg: #FFFBEB;
        --tone-fg: #B45309;
        --tone-ring: #FDE68A;
        --tone-value: #B45309;
    }

    html.dark .stat-tile[data-tone="neutral"] {
        --tone-bg: #1E293B;
        --tone-fg: #CBD5E1;
        --tone-ring: #334155;
    }
    html.dark .stat-tile[data-tone="available"] {
        --tone-bg: #052E23;
        --tone-fg: #34D399;
        --tone-ring: #134E4A;
        --tone-value: #34D399;
    }
    html.dark .stat-tile[data-tone="occupied"] {
        --tone-bg: #2B0D12;
        --tone-fg: #FB7185;
        --tone-ring: #7F1D1D;
        --tone-value: #FB7185;
    }
    html.dark .stat-tile[data-tone="attention"] {
        --tone-bg: #2A1D05;
        --tone-fg: #FBBF24;
        --tone-ring: #78350F;
        --tone-value: #FBBF24;
    }

    /* ---------- Live dot ----------
       Pulses only while the feed is genuinely fresh, so the indicator cannot
       sit there claiming "live" while polling is paused or failing. */
    .live-dot {
        position: relative;
        display: inline-flex;
        width: 8px;
        height: 8px;
        border-radius: 9999px;
        background: currentColor;
        flex: none;
    }
    .live-dot[data-pulse="true"]::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        background: currentColor;
        animation: roomie-ping 2s cubic-bezier(0, 0, .2, 1) infinite;
    }
    @keyframes roomie-ping {
        0%   { transform: scale(1);   opacity: .7; }
        80%  { transform: scale(2.6); opacity: 0; }
        100% { transform: scale(2.6); opacity: 0; }
    }

    /* ---------- Timeline (session progress) ---------- */
    .timeline__track {
        position: relative;
        height: 6px;
        border-radius: 9999px;
        background: #E2E8F0;
        overflow: hidden;
    }
    .timeline__fill {
        position: absolute;
        inset-block: 0;
        left: 0;
        border-radius: 9999px;
        background: var(--timeline-fill, #4F46E5);
    }
    html.dark .timeline__track {
        background: #1E293B;
    }

    /* ---------- Dialog ---------- */
    dialog::backdrop {
        background: rgb(15 23 42 / .6);
        backdrop-filter: blur(4px);
    }
    dialog.roomie-dialog {
        width: min(32rem, calc(100vw - 2rem));
        max-height: calc(100dvh - 2rem);
        margin: auto;
        padding: 0;
        border: 0;
        border-radius: 16px;
        background: #FFFFFF;
        color: #0F172A;
        box-shadow: 0 24px 48px -12px rgb(15 23 42 / .25);
    }
    html.dark dialog.roomie-dialog {
        background: #111827;
        color: #F8FAFC;
        box-shadow: 0 24px 48px -12px rgb(0 0 0 / .6);
    }
    dialog.roomie-dialog[open] {
        animation: roomie-dialog-in .18s cubic-bezier(.2, .8, .3, 1);
    }
    @keyframes roomie-dialog-in {
        from { opacity: 0; transform: translateY(8px) scale(.985); }
        to   { opacity: 1; transform: none; }
    }

    /* ---------- Scanner viewport ---------- */
    .roomie-corner {
        position: absolute;
        width: 34px;
        height: 34px;
        border: 2px solid #4F46E5;
        border-radius: 8px;
    }
    .roomie-laser {
        position: absolute;
        inset-inline: 10px;
        height: 2px;
        border-radius: 9999px;
        background: linear-gradient(90deg, transparent, #4F46E5 18%, #4F46E5 82%, transparent);
        animation: roomie-scan 2.4s cubic-bezier(.45, 0, .55, 1) infinite;
    }
    @keyframes roomie-scan {
        0%, 100% { top: 12px; opacity: .3; }
        50%      { top: calc(100% - 14px); opacity: 1; }
    }
    .roomie-shake {
        animation: roomie-shake .4s cubic-bezier(.36, .07, .19, .97);
    }
    @keyframes roomie-shake {
        10%, 90% { transform: translateX(-2px); }
        20%, 80% { transform: translateX(4px); }
        30%, 50%, 70% { transform: translateX(-6px); }
        40%, 60% { transform: translateX(6px); }
    }

    /* Theme switch icons swap in CSS, so the correct glyph is painted with the
       theme itself instead of being patched a frame later by script. */
    [data-theme-icon="moon"] {
        display: none;
    }
    html.dark [data-theme-icon="sun"] {
        display: none;
    }
    html.dark [data-theme-icon="moon"] {
        display: inline-flex;
    }
    [data-theme-icon="sun"] {
        display: inline-flex;
    }
    [data-nav-collapse] svg {
        transition: transform .2s cubic-bezier(.4, 0, .2, 1);
    }
    html[data-sidebar="collapsed"] [data-nav-collapse] svg {
        transform: rotate(180deg);
    }

    /* ---------- Live state chip ----------
       The dashboard used to show a permanently green "data diperbarui otomatis"
       pill that kept claiming live status while polling was paused or failing.
       This chip has three honest states and a real toggle. */
    .roomie-livechip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        background: var(--chip-bg);
        color: var(--chip-fg);
        box-shadow: inset 0 0 0 1px var(--chip-ring);
    }
    .roomie-livechip[data-live="live"] {
        --chip-bg: #ECFDF5;
        --chip-fg: #047857;
        --chip-ring: #A7F3D0;
    }
    .roomie-livechip[data-live="paused"] {
        --chip-bg: #F1F5F9;
        --chip-fg: #475569;
        --chip-ring: #CBD5E1;
    }
    .roomie-livechip[data-live="error"] {
        --chip-bg: #FEF2F2;
        --chip-fg: #B91C1C;
        --chip-ring: #FECACA;
    }
    html.dark .roomie-livechip[data-live="live"] {
        --chip-bg: #052E23;
        --chip-fg: #34D399;
        --chip-ring: #134E4A;
    }
    html.dark .roomie-livechip[data-live="paused"] {
        --chip-bg: #1E293B;
        --chip-fg: #CBD5E1;
        --chip-ring: #334155;
    }
    html.dark .roomie-livechip[data-live="error"] {
        --chip-bg: #2B0D12;
        --chip-fg: #FB7185;
        --chip-ring: #7F1D1D;
    }
    .roomie-livechip .live-dot {
        color: currentColor;
    }

    /* ---------- Segmented filter ----------
       Deliberately NOT using `peer-checked`: Tailwind's peer variant matches
       every later sibling, and one filter group holds several radios, so the
       checked state leaked onto all options. State lives in a data attribute
       instead — the same single-source idiom the badges and tones use. */
    .filter-option {
        display: inline-flex;
        align-items: center;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
        cursor: pointer;
        color: #475569;
        transition: color .2s ease, background-color .2s ease;
    }
    .filter-option:hover {
        color: #0F172A;
    }
    .filter-option[data-checked="true"] {
        background: #4F46E5;
        color: #FFFFFF;
    }
    .filter-option[data-checked="true"]:hover {
        color: #FFFFFF;
    }
    .filter-input:focus-visible + .filter-option {
        outline: 2px solid #4F46E5;
        outline-offset: 2px;
    }

    html.dark .filter-option {
        color: #94A3B8;
    }
    html.dark .filter-option:hover {
        color: #F8FAFC;
    }
    html.dark .filter-option[data-checked="true"] {
        background: #6366F1;
        color: #FFFFFF;
    }
    html.dark .filter-input:focus-visible + .filter-option {
        outline-color: #818CF8;
    }

    /* ---------- Bento grid (DESIGN.md §5.1) ----------
       A room with a live session earns a double-width tile: it is the only
       state that needs to show a course name, an owner, a progress bar and a
       countdown at once. Available rooms stay compact. */
    @media (min-width: 640px) {
        .room-bento > [data-bento="wide"] {
            grid-column: span 2;
        }
    }

    /* ---------- Notification rows ----------
       Rendered by script from the live snapshot, so they cannot use a Blade
       component. Scoped classes keep the styling out of the JS strings. */
    .roomie-notice {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        width: 100%;
        padding: 10px 12px;
        border-radius: 12px;
        text-align: left;
        font-size: 13px;
        line-height: 1.45;
        color: #475569;
        transition: background-color .2s ease;
    }
    .roomie-notice:hover {
        background: #F1F5F9;
    }
    .roomie-notice strong {
        font-weight: 600;
        color: #0F172A;
    }
    .roomie-notice__icon {
        flex: none;
        margin-top: 1px;
    }
    .roomie-notice[data-tone="info"] .roomie-notice__icon {
        color: #0369A1;
    }
    .roomie-notice[data-tone="warning"] .roomie-notice__icon {
        color: #B45309;
    }
    .roomie-notice[data-tone="danger"] .roomie-notice__icon {
        color: #B91C1C;
    }
    .roomie-notice--empty {
        justify-content: center;
        color: #64748B;
    }

    html.dark .roomie-notice {
        color: #CBD5E1;
    }
    html.dark .roomie-notice:hover {
        background: #1E293B;
    }
    html.dark .roomie-notice strong {
        color: #F8FAFC;
    }
    html.dark .roomie-notice[data-tone="info"] .roomie-notice__icon {
        color: #38BDF8;
    }
    html.dark .roomie-notice[data-tone="warning"] .roomie-notice__icon {
        color: #FBBF24;
    }
    html.dark .roomie-notice[data-tone="danger"] .roomie-notice__icon {
        color: #FB7185;
    }
    html.dark .roomie-notice--empty {
        color: #94A3B8;
    }

    /* ---------- App shell ----------
       Sidebar geometry lives here because it combines three media/attribute
       states (drawer / 240px rail / 64px collapsed rail) that would otherwise
       need three sets of Tailwind utilities fighting over one node. */
    .roomie-sidebar {
        position: fixed;
        inset-block: 0;
        left: 0;
        z-index: 50;
        width: 240px;
        display: flex;
        flex-direction: column;
        transform: translateX(-100%);
        transition: width .2s cubic-bezier(.4, 0, .2, 1), transform .2s cubic-bezier(.4, 0, .2, 1);
        background: #FFFFFF;
        border-right: 1px solid #E2E8F0;
    }
    .roomie-sidebar[data-open="true"] {
        transform: translateX(0);
        box-shadow: 0 0 0 100vmax rgb(15 23 42 / .4);
    }
    .roomie-content {
        transition: padding-left .2s cubic-bezier(.4, 0, .2, 1);
    }

    @media (min-width: 1024px) {
        .roomie-sidebar {
            transform: translateX(0);
        }
        .roomie-sidebar[data-open="true"] {
            box-shadow: none;
        }
        .roomie-content {
            padding-left: 240px;
        }
        html[data-sidebar="collapsed"] .roomie-content {
            padding-left: 64px;
        }
        html[data-sidebar="collapsed"] .roomie-sidebar {
            width: 64px;
        }
        html[data-sidebar="collapsed"] .roomie-sidebar .roomie-nav-label,
        html[data-sidebar="collapsed"] .roomie-sidebar .roomie-sidebar-foot {
            display: none;
        }
        html[data-sidebar="collapsed"] .roomie-sidebar .roomie-nav-item {
            justify-content: center;
            padding-inline: 0;
        }
        html[data-sidebar="collapsed"] .roomie-sidebar .roomie-nav-item .roomie-nav-indicator {
            display: none;
        }
        html[data-sidebar="collapsed"] .roomie-sidebar .roomie-brand-text {
            display: none;
        }
    }

    html.dark .roomie-sidebar {
        background: #111827;
        border-right-color: #1E293B;
    }

    .roomie-backdrop {
        position: fixed;
        inset: 0;
        z-index: 40;
        background: rgb(15 23 42 / .5);
    }
    .roomie-backdrop[hidden] {
        display: none;
    }

    /* ---------- Toast ---------- */
    .roomie-toast {
        transition: opacity .2s ease, transform .2s ease;
    }
    .roomie-toast[data-leaving="true"] {
        opacity: 0;
        transform: translateY(6px);
    }
    .roomie-toast[data-tone="info"] .roomie-toast__icon {
        color: #0369A1;
    }
    .roomie-toast[data-tone="success"] .roomie-toast__icon {
        color: #047857;
    }
    .roomie-toast[data-tone="warning"] .roomie-toast__icon {
        color: #B45309;
    }
    .roomie-toast[data-tone="danger"] .roomie-toast__icon {
        color: #B91C1C;
    }
    html.dark .roomie-toast[data-tone="info"] .roomie-toast__icon {
        color: #38BDF8;
    }
    html.dark .roomie-toast[data-tone="success"] .roomie-toast__icon {
        color: #34D399;
    }
    html.dark .roomie-toast[data-tone="warning"] .roomie-toast__icon {
        color: #FBBF24;
    }
    html.dark .roomie-toast[data-tone="danger"] .roomie-toast__icon {
        color: #FB7185;
    }

    /* ---------- Print ---------- */
    @page {
        margin: 12mm;
    }
    @media print {
        html, html.dark {
            background: #FFFFFF !important;
            color-scheme: light;
        }
        .no-print {
            display: none !important;
        }
        .print-keep {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }

    /* qrcodejs injects a fixed-size <img>; pin the print size and let it shrink
       on narrow labels instead of overflowing the sheet. */
    .roomie-qr img {
        width: 190px;
        max-width: 100%;
        height: auto;
    }

    /* ---------- Reduced motion ---------- */
    @media (prefers-reduced-motion: reduce) {
        .live-dot[data-pulse="true"]::after,
        .roomie-laser,
        .roomie-shake,
        dialog.roomie-dialog[open] {
            animation: none !important;
        }
        .live-dot[data-pulse="true"]::after {
            opacity: .7;
        }
        .roomie-laser {
            top: 50%;
            opacity: 1;
        }
        *, *::before, *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>