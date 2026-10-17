{{--
    Shared component styles for the Roomie UI kit.
    Updated based on DESIGN.md palette (Indigo/Slate), Bento Grid UI, and WCAG AA contrast standards.
--}}

<style>
:root {
    /* Brand & Primary Colors (Indigo Theme) */
    --roomie-primary: #4F46E5;        /* Indigo 600 */
    --roomie-primary-hover: #4338CA;  /* Indigo 700 */
    --roomie-primary-subtle: #EEF2FF; /* Indigo 50 */
    
    /* Neutral Palette (Slate Base) */
    --roomie-ink: #0F172A;            /* Slate 900 */
    --roomie-ink-muted: #475569;      /* Slate 600 */
    --roomie-ink-weak: #64748B;       /* Slate 500 */
    --roomie-line: #E2E8F0;           /* Slate 200 */
    --roomie-canvas: #F8FAFC;         /* Slate 50 */
    --roomie-surface: #FFFFFF;
    
    /* Semantic Status Colors */
    --roomie-available: #10B981;      /* Emerald 500 */
    --roomie-occupied: #EF4444;       /* Rose 500 */
    --roomie-attention: #F59E0B;      /* Amber 500 */
    --roomie-unknown: #64748B;        /* Slate 500 */
    --roomie-focus: #4F46E5;
}

/* ---------- Skip link ---------- */
.skip-link {
    position: absolute;
    left: -9999px;
    top: 0;
    z-index: 100;
    background: var(--roomie-primary);
    color: #fff;
    padding: 12px 20px;
    border-radius: 0 0 12px 0;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
}
.skip-link:focus {
    left: 0;
}

/* ---------- Buttons ---------- */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 44px;
    padding: 0 20px;
    border: 1px solid transparent;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.2;
    text-align: center;
    cursor: pointer;
    transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
}
.btn:focus-visible {
    outline: 2px solid var(--roomie-focus);
    outline-offset: 2px;
}
.btn:active {
    transform: scale(0.98);
}
.btn--primary {
    background: var(--roomie-primary);
    color: #fff;
    box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.25);
}
.btn--primary:hover {
    background: var(--roomie-primary-hover);
    box-shadow: 0 6px 16px -2px rgba(79, 70, 229, 0.35);
}
.btn--secondary {
    background: var(--roomie-surface);
    color: var(--roomie-ink);
    border-color: var(--roomie-line);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.btn--secondary:hover {
    background: var(--roomie-canvas);
    border-color: #CBD5E1;
    color: var(--roomie-ink);
}
.btn--ghost {
    background: transparent;
    color: var(--roomie-ink-muted);
}
.btn--ghost:hover {
    background: var(--roomie-primary-subtle);
    color: var(--roomie-primary);
}
.btn--sm {
    min-height: 40px;
    padding: 0 14px;
    font-size: 13px;
    border-radius: 10px;
}
.btn[disabled], .btn[aria-disabled="true"] {
    opacity: .55;
    cursor: not-allowed;
    transform: none !important;
}

/* ---------- Status badges ---------- */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 5px 12px;
    border: 1px solid transparent;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.25;
    white-space: nowrap;
    transition: background-color .2s ease, border-color .2s ease;
}
.badge__dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    flex: none;
}

.badge[data-state="available"] {
    background: #ECFDF5;
    color: #047857;
    border-color: #A7F3D0;
}
.badge[data-state="available"] .badge__dot {
    background: var(--roomie-available);
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.badge[data-state="occupied"] {
    background: #FEF2F2;
    color: #B91C1C;
    border-color: #FECACA;
}
.badge[data-state="occupied"] .badge__dot {
    background: var(--roomie-occupied);
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
}

.badge[data-state="unknown"] {
    background: #F1F5F9;
    color: var(--roomie-ink-muted);
    border-color: #CBD5E1;
}
.badge[data-state="unknown"] .badge__dot {
    background: var(--roomie-unknown);
}

.badge[data-state="attention"] {
    background: #FFFBEB;
    color: #B45309;
    border-color: #FDE68A;
}
.badge[data-state="attention"] .badge__dot {
    background: var(--roomie-attention);
    box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
}

/* Sensor monospaced tokens */
.badge--sensor {
    font-family: ui-monospace, SFMono-Regular, "JetBrains Mono", "Fira Code", monospace;
    letter-spacing: .02em;
    font-weight: 700;
}
.badge--sensor[data-state="TERTUTUP"] {
    background: #ECFDF5;
    color: #047857;
    border-color: #A7F3D0;
}
.badge--sensor[data-state="TERBUKA"] {
    background: #FEF2F2;
    color: #B91C1C;
    border-color: #FECACA;
}
.badge--sensor[data-state="UNKNOWN"] {
    background: #F1F5F9;
    color: var(--roomie-ink-muted);
    border-color: #CBD5E1;
}

/* ---------- Stat Bento tiles ---------- */
.stat-card {
    background: var(--roomie-surface);
    border: 1px solid var(--roomie-line);
    border-radius: 16px;
    padding: 18px;
    box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.04);
    transition: transform .2s ease, box-shadow .2s ease;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.06);
}
@media (min-width: 640px) {
    .stat-card { padding: 20px; }
}
.stat-card__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
}
.stat-card__label {
    font-size: 13px;
    font-weight: 600;
    color: var(--roomie-ink-muted);
    line-height: 1.35;
}
.stat-card__icon {
    display: inline-flex;
    flex: none;
    width: 36px;
    height: 36px;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: var(--roomie-canvas);
    color: var(--roomie-ink-muted);
    border: 1px solid var(--roomie-line);
}
.stat-card__value {
    margin-top: 12px;
    font-size: 32px;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -.03em;
    color: var(--roomie-ink);
    font-variant-numeric: tabular-nums;
}
.stat-card__caption {
    margin-top: 8px;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.35;
    color: var(--roomie-ink-weak);
}

.stat-card--available .stat-card__icon {
    background: #ECFDF5;
    color: #059669;
    border-color: #A7F3D0;
}
.stat-card--available .stat-card__value { color: #059669; }

.stat-card--occupied .stat-card__icon {
    background: #FEF2F2;
    color: #E11D48;
    border-color: #FECACA;
}
.stat-card--occupied .stat-card__value { color: #E11D48; }

.stat-card--attention .stat-card__icon {
    background: #FFFBEB;
    color: #D97706;
    border-color: #FDE68A;
}
.stat-card--attention .stat-card__value { color: #D97706; }

/* ---------- Room list rows ---------- */
.room-item {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px 16px;
    align-items: start;
}
.room-item[hidden] { display: none; }
.room-item__identity {
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.room-item__icon {
    display: inline-flex;
    flex: none;
    width: 40px;
    height: 40px;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: var(--roomie-canvas);
    color: var(--roomie-ink-muted);
    border: 1px solid var(--roomie-line);
}
.room-item__updated { grid-column: 1 / -1; }
.room-item__actions {
    grid-column: 1 / -1;
    display: flex;
    gap: 10px;
}
.room-item__actions > * { flex: 1 1 0; }

.room-item--head { display: none; }
.room-item--head > span {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: var(--roomie-ink-weak);
}

@media (min-width: 1024px) {
    .room-item {
        grid-template-columns: minmax(0, 1.7fr) minmax(128px, .8fr) minmax(128px, .8fr) minmax(150px, .9fr) auto;
        align-items: center;
        gap: 20px;
    }
    .room-item__identity,
    .room-item__updated,
    .room-item__actions { grid-column: auto; }
    .room-item__actions { display: flex; gap: 8px; justify-content: flex-end; }
    .room-item__actions > * { flex: none; }
    .room-item--head { display: grid; }
}

/* ---------- Nav pills ---------- */
.nav-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 38px;
    padding: 0 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    color: var(--roomie-ink-muted);
    white-space: nowrap;
    transition: all .18s ease;
}
.nav-pill:hover {
    background: var(--roomie-canvas);
    color: var(--roomie-ink);
}
.nav-pill:focus-visible {
    outline: 2px solid var(--roomie-focus);
    outline-offset: 2px;
}
.nav-pill[aria-current="page"] {
    background: var(--roomie-primary-subtle);
    color: var(--roomie-primary);
    font-weight: 700;
}

/* ---------- Empty state ---------- */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
    padding: 56px 24px;
    border: 2px dashed var(--roomie-line);
    border-radius: 20px;
    background: var(--roomie-surface);
}
.empty-state__icon {
    display: inline-flex;
    width: 52px;
    height: 52px;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    background: var(--roomie-primary-subtle);
    color: var(--roomie-primary);
    margin-bottom: 4px;
}
.empty-state__title {
    font-size: 18px;
    font-weight: 700;
    color: var(--roomie-ink);
    letter-spacing: -.01em;
}
.empty-state__body {
    font-size: 14px;
    line-height: 1.6;
    color: var(--roomie-ink-weak);
    max-width: 48ch;
}

/* ---------- Live pulse dot ---------- */
.live-dot {
    position: relative;
    display: inline-flex;
    width: 8px;
    height: 8px;
}
.live-dot::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    background: var(--roomie-available);
    animation: roomie-ping 2s cubic-bezier(0, 0, .2, 1) infinite;
}
@keyframes roomie-ping {
    0%   { transform: scale(1);   opacity: .75; }
    80%  { transform: scale(2.5); opacity: 0; }
    100% { transform: scale(2.5); opacity: 0; }
}

/* ---------- Toast ---------- */
.toast {
    pointer-events: none;
    position: fixed;
    left: 50%;
    bottom: 24px;
    z-index: 70;
    transform: translateX(-50%);
    width: max-content;
    max-width: calc(100vw - 32px);
}
.toast[hidden] { display: none; }

/* ---------- Reduced motion ---------- */
@media (prefers-reduced-motion: reduce) {
    .live-dot::after { animation: none; opacity: .75; }
    .btn, .nav-pill, .stat-card { transition: none; }
    *, *::before, *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
    }
}
</style>