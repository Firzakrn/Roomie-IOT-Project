# Roomie — UI/UX Audit

**Scope:** Frontend prototype only. Visual quality, usability, accessibility.
**Date:** 2026-10-17
**Auditor role:** Senior UI/UX Designer & Frontend Engineer (audit only, no code changed)

---

## 1. Executive Summary

Roomie is **functionally complete and structurally sound, but visually incoherent.** The interface does not look un-designed — individual pages have deliberate hierarchy, correct focus rings, and sensible empty-state copy. The problem is that it was built page-by-page without a shared system, so the app currently ships **four different application shells** and **two parallel color languages** that a user can feel but cannot name.

Three root causes, in order of impact:

1. **Two parallel design systems.** `dashboard.blade.php` defines a custom `roomie-*` token set inline; `auth/layout`, `history`, `scanner`, `scan-result`, and `qr-labels` all use raw Tailwind `blue-600/700`. Neither matches `DESIGN.md` §6, which mandates dark teal `#0F766E`. So there are three color systems in play: teal (spec), blue-token (dashboard), raw-blue (everything else).
2. **No shared component layer.** 9 Blade templates, exactly one real component. Status badges, buttons, cards, and form inputs are hand-copied and have already drifted apart.
3. **Not mobile-first, despite mobile being the primary use case.** `DESIGN.md` §7 and the PRD both frame QR scanning as the core mobile journey, but touch targets measure 36px tall and the room table collapses into unreadable stubs at 800px.

**What is genuinely good and must be preserved:** focus-visible rings are correct throughout, no emoji is used as a structural icon, flash messages use `role="status"`/`role="alert"`, the history empty state is well written, and the SKS duration calculator is properly built with `aria-live`. Lighthouse scores **0.95 accessibility** on the dashboard — the only failure is color contrast. This is a polish-and-coherence problem, not a rebuild.

**Recommendation:** Do not redesign page by page. Build the token layer and shared shell first (§6, §7), then rebuild pages on top of it. Redesigning pages before components exist will produce the same drift a third time.

---

## 2. Existing Design & Frontend Architecture

### 2.1 Build and styling

| Aspect | Observed reality |
|---|---|
| CSS delivery | `https://cdn.tailwindcss.com` in all 6 full-page views. No `@vite`, no compiled CSS. |
| Custom theme | `tailwind.config` defined **inline, once**, inside `dashboard.blade.php` only |
| Tokens | `roomie.ink #17243A`, `roomie.blue #2563EB`, `roomie.canvas #F5F7FB`, `roomie.line #E7EBF2`, `shadow-soft` |
| Typography | **No webfont loaded anywhere.** System default everywhere. |
| JS | Inline `<script>` per view. No bundler, no modules. |
| Icons | Hand-inlined SVG on dashboard; **text glyphs** on auth (`⌂`) and scanner (`▦`) |

**Evidence:** `resources/views/dashboard.blade.php:9-27` (inline config); `grep '@vite' resources/` → **0 matches**; `resources/css/app.css` → **empty**.

### 2.2 Templates (9 total, all full-page documents)

| File | Shell | Design tokens used |
|---|---|---|
| `dashboard.blade.php` | Header + nav + profile menu | `roomie-*` |
| `history.blade.php` | Bare: "← Dashboard" + button | raw `blue-*` |
| `scanner.blade.php` | Bare: two text links | raw `blue-*` |
| `scan-result.blade.php` | Bare | raw `blue-*` |
| `admin/qr-labels.blade.php` | Bare + print button | raw `blue-*` |
| `auth/layout.blade.php` | Centered card | raw `blue-*` |
| `auth/login.blade.php` | `@extends(auth.layout)` | inherits raw `blue-*` |
| `auth/register.blade.php` | `@extends(auth.layout)` | inherits raw `blue-*` |
| `components/room-modal.blade.php` | Injected partial | `roomie-*` |

### 2.3 Data and interaction

- Dashboard live data: `fetch(route('rooms.live'))` every **10s**, paused when `document.hidden`. No websockets.
- Room detail: server-rendered Blade partial fetched into `#room-modal-container` and injected via `innerHTML`.
- Room rows matched to payload by scanning `[data-room-code]` attributes — correct, no unsafe selectors.
- Countdown timer ticks at 1s inside the modal only.

---

## 3. Verification Method — What Was Actually Checked

**Live rendering.** The app was started (`php artisan serve`) against the existing dev database (3 rooms, 2 users) and driven in a real browser. Screenshots captured at **800 × 527 CSS px** (`devicePixelRatio` 1.5):

- `/login` — unauthenticated
- `/` (dashboard) — authenticated as seeded `mahasiswa`
- `/scan` (scanner)
- `/riwayat` (history, empty state)

The server was stopped afterwards. **No application file was modified.**

**Automated.** Lighthouse (accessibility / best-practices / SEO), plus a custom in-page script that computed WCAG 2.1 contrast ratios for every text node and measured bounding boxes of all interactive elements.

**Code review.** All 9 Blade templates, 4 controllers, 4 models, both route files, `config/roomie.php`, and both spec documents.

> **Honesty note on the skill.** The `ui-ux-pro-max` skill was activated and its instructions confirmed available (Python 3.12.10 present, `search.py` present). Its first `--design-system` result returned **Glassmorphism + dark theme + Fira Code monospace headings**, which directly violates `DESIGN.md` §6 ("Hindari … glassmorphism … atau tampilan sci-fi IoT"). Per the skill's own instruction to verify results before use, I retried with narrower queries. The second run returned **Minimalism & Swiss** (accepted); the aggregated dark/mono color and typography payloads were rejected in favour of `DESIGN.md` §6. Convergent support came from `--domain color` (LMS teal `#0D9488`) and `--domain typography` (Inter / "Minimal Swiss"). Only verified findings are used below.

**Not checked:** no true mobile device, no physical iOS/Android touch testing, no real camera, no Safari/Firefox rendering, no dark mode (none exists), and **no authenticated admin session** — the admin QR sheet was reviewed in code only.

---

## 4. Prioritized Problems

### P0 — Fix before any visual work

#### P0-1 · Indonesian UI renders an English date — **OBSERVED**

The dashboard header renders **"Saturday, 17 October 2026"** in an otherwise fully Indonesian interface.

- **Cause:** `dashboard.blade.php:72` calls `now()->translatedFormat('l, d F Y')`, but `config/app.php` sets `'locale' => 'en'`. `translatedFormat` therefore returns English.
- **Spec conflict:** `DESIGN.md` §9 mandates `16 Okt 2026, 09.00–10.40`.
- **Severity:** High. It is the first line of content on the primary screen and instantly signals an unfinished product.

#### P0-2 · Three competing color systems — **OBSERVED**

`DESIGN.md` §6 specifies dark teal `#0F766E`. The dashboard ships `#2563EB` via `roomie-blue`. The other five views use raw `blue-600`/`blue-700`. Nothing else in the app references teal.

**Consequence:** the scanner, history, and auth pages look like a different product from the dashboard. There is no single source of truth to restyle.

#### P0-3 · Text contrast below WCAG AA — **MEASURED**

Lighthouse accessibility = **0.95**, and the *only* failing audit is `color-contrast`.

| Text | FG | BG | Ratio | Required |
|---|---|---|---|---|
| "Terdaftar di kampus" (stat caption) | `#94A3B8` | `#FFFFFF` | **2.56:1** | 4.5 |
| "Ruangan" (column header) | `#94A3B8` | `#F8FAFC` | **2.45:1** | 4.5 |
| "Status ruangan diperbarui berkala" | `#64748B` | `#F5F7FB` | **4.44:1** | 4.5 |
| Hero body copy on blue banner | `#DBEAFE` | `#2563EB` | **4.24:1** | 4.5 |
| "Gedung A10 · Lantai 1" divider | `#CBD5E1` | `#FFFFFF` | **1.48:1** | 3.0 |

`text-slate-400` / `text-slate-300` appear **12 times** across templates. `DESIGN.md` §1.6 requires sufficient contrast; the skill flags "Color Only" and "Color Contrast" as **High** severity.

#### P0-4 · Room table is unusable between 768px and ~1100px — **OBSERVED AT 800px**

At an 800px viewport the screenshot shows room names truncated to **"Rua…"** and **"Lab …"**, with "Gedung A10 · Lantai 1" wrapping onto four lines. The five-column grid is active but has ~250px for the room name.

**Cause:** `md:grid-cols-[minmax(0,1.5fr)_minmax(130px,.85fr)_minmax(130px,.85fr)_minmax(150px,.9fr)_auto]` activates at `md` (768px) but its fixed `minmax()` floors require ~1100px. Below that the tracks collapse past usability.

`DESIGN.md` §7 specifies table → cards on mobile and `DESIGN.md` §5-C requires no horizontal scrolling.

#### P0-5 · Touch targets below 44px on the primary mobile flow — **MEASURED**

| Element | Measured | Min |
|---|---|---|
| "Scan QR" button (×3, room rows) | 73 × **36** | 44 |
| "Detail" button (×3) | 61 × **36** | 44 |
| "Roomie" logo link | 115 × **36** | 44 |
| "Lihat riwayat penggunaan" | 221 × **37** | 44 |

Every per-room action fails the 44px minimum. The skill rates "Touch Target Size" and "Touch Friendly" as **High** severity, and `DESIGN.md` §7 requires comfortable touch heights.

---

### P1 — Fix during the redesign

#### P1-1 · No shared component layer — **OBSERVED**

One component exists (`components/room-modal.blade.php`), and it is loaded via `view('components.room-modal')` rather than as `<x-room-modal>`. Laravel stack guidance rates "Use Blade components for reusable UI" as **High** severity.

Concretely duplicated and already divergent:

- **Status badges** — the availability badge class string is written out **twice in `dashboard.blade.php`** (lines 172-175 static render, line 235 live-update JS) and a third variant in `room-modal.blade.php:30`. Any change requires three edits, one of them inside a JS string.
- **Form inputs** — `rounded-xl border border-slate-300 p-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100` appears verbatim in `login`, `register`, `scan-result`.
- **Card surfaces** — `rounded-2xl border … shadow-sm` in `history`, `rounded-3xl … shadow-xl` in `auth/layout`.
- **`<head>` block** — repeated in 6 files.

#### P1-2 · Four different application shells — **OBSERVED**

- **Dashboard:** logo, nav, search, profile `<details>` menu with logout.
- **Scanner:** two bare text links ("← Dashboard", "Riwayat"). No logo, no profile, **no logout**.
- **History:** "← Dashboard" + "Scan QR" button. No logo, no profile, **no logout**.
- **Auth:** centered card, no navigation (correct).

A user on `/riwayat` or `/scan` cannot log out without navigating back to the dashboard. `DESIGN.md` §4 specifies a persistent sidebar on desktop and compact mobile nav — neither exists.

#### P1-3 · Scanner is a dead end with no fallback — **OBSERVED**

The screenshot of `/scan` shows the camera container rendering as an **empty bordered box** with status text stuck at *"Menyiapkan kamera…"*. No error state, no retry, no alternative path.

**Cause:** `scanner.blade.php:48` awaits `Html5Qrcode.getCameras()`; when no camera exists the promise chain depends on `.catch()` at line 54, but the status text was never updated past the initial state.

> ⚠️ **This is a spec-vs-build conflict, not a styling issue.** `DESIGN.md` §E and `PRD.md` FR-05 both require a **manual token fallback** and explicit camera-unavailable / permission-denied states. The page copy at line 18 actively states *"Tidak ada input token manual"* — the implementation deliberately contradicts both specs.
>
> **Product decision required.** This audit does **not** propose adding manual token entry, because that would introduce a capability the current build intentionally removed and would touch the anti-bypass guarantees described in `AGENTS.md` (the pending-scan token exists partly to stop manual URL crafting). Flagged for the owner to decide.

#### P1-4 · No typography system — **OBSERVED**

No font is loaded anywhere. Body copy renders in the OS default sans. Headings add ad-hoc `tracking-tight`. There is no defined scale, and `text-[10px]` / `text-[11px]` appear **19 times** in the dashboard alone — below any comfortable minimum for a data table.

`DESIGN.md` §6 mandates Inter with a clear page/section/label/helper hierarchy.

#### P1-5 · Modal is not keyboard-operable — **OBSERVED**

`room-modal.blade.php` correctly declares `role="dialog"`, `aria-modal="true"`, `aria-labelledby`, an `sr-only` close label, and a focus ring. But:

- **No Escape handler** — `grep 'Escape\|keydown' resources/views/` → **0 matches**.
- **No focus trap** — Tab escapes to the page behind.
- **No focus restore** on close, and focus is never moved into the dialog on open.
- **Error path uses native `alert()`** — `dashboard.blade.php:269`. A blocking, unstyled browser dialog in an otherwise designed flow.
- Background scroll lock is applied, but nothing else is managed.

#### P1-6 · Polling updates are invisible to screen readers — **OBSERVED**

The dashboard rewrites three text nodes per room every 10 seconds (availability, sensor, updated-at) plus four stat values. **No `aria-live` region exists** on the dashboard — `grep aria-live` returns exactly **1 match** app-wide, the SKS duration box in `scan-result`.

A screen-reader user has no way to learn that the room they are watching just became occupied. The skill's operations pattern explicitly calls for announcing live data with a stale state.

#### P1-7 · No reduced-motion support — **OBSERVED**

`grep 'prefers-reduced-motion'` → **0 matches**. The "Status ruangan diperbarui berkala" indicator uses `animate-ping`, which loops indefinitely. The skill's pre-delivery checklist requires `prefers-reduced-motion` to be respected.

---

### P2 — Polish

| # | Issue | Evidence |
|---|---|---|
| P2-1 | Dashboard has **no empty state** for zero rooms; history has a good one | `@empty` appears once app-wide (`history.blade.php`) |
| P2-2 | Icon language splits three ways: inline SVG (dashboard), `⌂` glyph (auth), `▦` glyph (scanner) | `auth/layout.blade.php:10`, `scanner.blade.php:13` |
| P2-3 | Title separator inconsistent — em-dash `—` on dashboard vs `·` elsewhere | Verified at codepoint level |
| P2-4 | Modal body locked at `max-w-lg` with `p-4`; no responsive refinement for small screens | `room-modal.blade.php:5-7` |
| P2-5 | Dashboard whitespace rhythm is loose — ~40px dead gap between hero banner and stat cards, and again before "Monitoring Ruangan" | Screenshot |
| P2-6 | Stat grid is 2-up until `lg` (1024px); at 800px four tiles stack into two rows and push content down | Screenshot |
| P2-7 | Pagination renders with unstyled Laravel default markup, unaligned to the design system | `history.blade.php:31` |

---

### 5. What Is Already Good — Preserve These

These are deliberate and correct. A redesign must not regress them.

1. **Focus-visible rings are correct.** Only one `focus:outline-none` exists app-wide and it is paired with `focus:ring-2`. The profile menu uses `focus-visible:ring-2`. Lighthouse confirms no focus-related failure.
2. **No emoji as structural icons.** The only emoji is the decorative `👋` in a login greeting (which reads as an inappropriate register for a campus ops tool). Text glyphs `⌂`/`▦` are glyphs, not emoji, but are still off-system.
3. **Flash messages are announced** — `role="status"` for success, `role="alert"` for errors, on dashboard and login.
4. **History empty state is genuinely good** — clear title, actionable guidance, no clutter.
5. **The SKS duration panel is well built** — `aria-live="polite"`, config values passed as `data-` attributes rather than hardcoded, and the formula stated in plain language.
6. **Live update stops when the tab is hidden** — `if (!document.hidden)` in the polling loop. Correct.
7. **DOM updates avoid unsafe selectors** — rows are matched by comparing `dataset.roomCode`, with an explanatory comment.
8. **`scanningDone` guard** prevents double-submission on rapid QR reads.
9. **Honest data handling** — stale sensors (>90s) correctly render as `UNKNOWN` / "Perlu diperiksa", matching `DESIGN.md` §3's requirement not to fake sensor data.

---

## 6. Proposed Visual Direction and Design Tokens

### 6.1 Direction: "Calm Campus Operations Console"

Light, neutral, information-dense, mobile-first. Restrained accent color for primary actions; **status colors reserved exclusively for status** so they never compete with the primary CTA. This satisfies `DESIGN.md` §6 ("modern campus operations dashboard … Hindari … glassmorphism, gradient besar, atau tampilan sci-fi IoT") and the skill's Minimalism & Swiss style result.

Explicitly **not** doing: glassmorphism, dark mode, neon/gradient IoT theming, monospace body copy, oversized hero typography.

### 6.2 Color tokens

Adopting `DESIGN.md` §6 as authoritative, nudged to sit on Tailwind's teal ramp for consistency with the rest of the utility palette. Corroborated by the skill's LMS result (`#0D9488` primary + amber accent).

```css
/* Brand */
--roomie-primary:        #0F766E;   /* was #2563EB — aligns to DESIGN.md §6 */
--roomie-primary-hover:  #115E59;
--roomie-primary-subtle: #CCFBF1;

/* Surfaces */
--roomie-canvas: #F8FAFC;   /* was #F5F7FB */
--roomie-surface: #FFFFFF;
--roomie-line:    #E2E8F0;   /* was #E7EBF2 */

/* Text — every pair below verified ≥4.5:1 */
--roomie-ink:      #0F172A;   /* 17.4:1 on canvas */
--roomie-ink-muted:#475569;   /*  7.5:1 on canvas  — replaces slate-500 on canvas */
--roomie-ink-weak: #64748B;   /*  4.7:1 on white   — replaces slate-400        */

/* Status — always paired with text + icon, never color alone */
--roomie-available: #15803D;  /* 4.6:1 on #ECFDF5 */
--roomie-occupied:  #B91C1C;  /* 5.9:1 on #FEF2F2 */
--roomie-attention: #B45309;  /* 4.8:1 on #FFFBEB */
--roomie-unknown:   #475569;  /* neutral — never green (DESIGN.md §6)      */
--roomie-destructive: #B91C1C;
```

**Hard rule:** `#94A3B8` (slate-400) and `#CBD5E1` (slate-300) are **banned as text colors** — they produced every contrast failure in §4 P0-3.

### 6.3 Typography

Inter (skill: "Minimal Swiss", `DESIGN.md` §6), self-hosted or via CDN with a system fallback.

```css
--font-sans: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;

/* Scale */
--text-display:  30px / 1.2  / -0.02em / 700   /* page title        */
--text-title:    20px / 1.3  / -0.01em / 600   /* section title     */
--text-body:     15px / 1.55 / 0     / 400   /* default           */
--text-label:    13px / 1.4  / 0.01em / 600   /* field labels      */
--text-meta:     13px / 1.4  / 0     / 400   /* helper/timestamps */
--text-badge:    12px / 1.2  / 0.02em / 600   /* status badges     */
```

**Minimum body/meta size 13px.** Replaces all `text-[10px]` / `text-[11px]` usage. Monospace (`font-mono`) stays **only** for the countdown timer and room codes, where digit alignment matters.

### 6.4 Spacing, radius, elevation

- 4px base grid; section rhythm `24 / 32 / 48`.
- Radius: card `14px`, control `10px`, badge `999px`.
- **One** shadow token (`--shadow-card`). Current spread of `shadow-sm` / `shadow-soft` / `shadow-xl` is inconsistent.
- Touch target floor: `min-height: 44px` on every interactive element.

### 6.5 Motion

- Transitions `150–200ms`, `ease-out`. Entrance animations minimal.
- `prefers-reduced-motion: reduce` disables `animate-ping`, countdown animation, and all transitions.

---

## 7. Shared Component Improvement Plan

Build these as **anonymous Blade components** in `resources/views/components/` (Laravel auto-registers them as `<x-*>`). Do this before touching any page.

| Component | Props | Fixes |
|---|---|---|
| `x-app-shell` | `$title`, `$activeNav` | P1-2 — one header, nav, profile menu, logout for every authenticated page |
| `x-page-header` | `$title`, `$subtitle`, `$eyebrow` | Consistency; hosts the date (fixes P0-1 placement) |
| `x-button` | `$variant` (primary/secondary/ghost/destructive), `$size`, `$href` | P0-5 — enforces 44px min and one focus-ring style |
| `x-status-badge` | `$dimension` (availability/usage/sensor/device), `$value` | P1-1 — kills the triplicated badge string; forces the 4-dimension model from `DESIGN.md` §3 |
| `x-stat-card` | `$label`, `$value`, `$tone`, `$caption` | Dashboard tiles; removes inline `$stats` array |
| `x-room-card` | `$room`, `$snapshot` | **Mobile** room presentation (P0-4) |
| `x-room-row` | `$room`, `$snapshot` | **Desktop** row (P0-4) |
| `x-form-field` | `$label`, `$name`, `$type`, `$hint`, `$error` | P1-1 — one input style across login/register/scan-result |
| `x-alert` | `$variant` (success/error/info) | Replaces 5 ad-hoc flash blocks |
| `x-empty-state` | `$title`, `$body`, `$action` | P2-1 — gives the dashboard the state history already has |
| `x-modal` | `$title` | P1-5 — Escape, focus trap, focus restore, `<dialog>`-style semantics |

**Token delivery:** move the inline `tailwind.config` out of `dashboard.blade.php` into a single shared location (a Blade partial included by every view, or a committed `tailwind.config.js` if the build is ever adopted). Until then there is no way to restyle globally.

**Not in scope:** a CSS build pipeline. `DESIGN.md` targets Blade + Tailwind, and the constraint is to keep the existing stack. Note as a follow-up that CDN Tailwind is a dev-grade choice with a FOUC cost.

---

## 8. Page-by-Page Redesign Roadmap

Ordered by dependency, then by user impact. **Do not start phase 2 before phase 1 is merged.**

### Phase 0 — Foundations *(no page changes)*
1. Extract shared token config out of `dashboard.blade.php`.
2. Load Inter with a system fallback; add the reduced-motion media query.
3. Fix locale so `translatedFormat` returns Indonesian (P0-1).
4. Ban `text-slate-400`/`300` as text colors; retune the muted-text token (P0-3).

**Exit:** a token change propagates app-wide; date renders as "Sabtu, 17 Oktober 2026".

### Phase 1 — Shared shell & components
5. `x-app-shell` + `x-page-header`; migrate dashboard, then scanner, history, scan-result, QR labels.
6. `x-button`, `x-badge`, `x-alert`, `x-form-field`.
7. `x-modal` with full keyboard support; replace the `alert()` call.

**Exit:** all six authenticated pages share one shell; every control ≥44px; no raw `blue-*` classes remain.

### Phase 2 — Dashboard *(highest traffic)*
8. Fix the responsive room list: card layout `<768px`, table `≥1024px`, and **no table layout between those bounds** (P0-4).
9. Rework stat tiles onto `x-stat-card`; tighten the whitespace rhythm (P2-5, P2-6).
10. Add an `aria-live="polite"` region announcing occupancy changes (P1-6).
11. Add `x-empty-state` for zero rooms (P2-1).

### Phase 3 — QR flow *(mobile-first, core journey)*
12. Give the camera container a defined aspect ratio and a real error state (permission denied / no camera / QR invalid / inactive room) per `DESIGN.md` §E.
13. Large full-width touch targets; safe-area padding for the phone-in-hand case.
14. Keep `x-form-field` + the SKS duration panel exactly as they are — they already work well.

> Blocked on the P1-3 product decision regarding manual token fallback.

### Phase 4 — Room detail modal & history
15. Migrate modal to `x-modal`; make the countdown respect reduced motion.
16. History: restyle cards onto tokens; align pagination markup.

### Phase 5 — Auth & admin
17. Login/register onto `x-form-field` and the shared button; replace the `⌂` glyph with the dashboard's SVG mark; drop the `👋` (inappropriate register for a campus ops tool).
18. QR print sheet: verify print CSS at A4 and keep the existing `qrcodejs` integration untouched.

### Phase 6 — Hardening
19. Re-run Lighthouse; target **accessibility ≥1.00**.
20. Full keyboard pass: tab order, modal trap, focus visibility.
21. Cross-browser check (Chrome/Safari/Firefox) — **currently unverified**.

---

## 9. Acceptance Criteria

### Accessibility
- [ ] Lighthouse accessibility **≥ 1.00** (currently 0.95; only failure is color contrast).
- [ ] All body text ≥ **4.5:1**; large text and UI borders ≥ **3:1**. No `text-slate-400`/`300` as text.
- [ ] Every interactive element ≥ **44 × 44px**, verified by bounding-box measurement.
- [ ] Modal: Escape closes, focus is trapped while open, focus returns to the trigger, background is inert.
- [ ] Occupancy changes are announced via an `aria-live="polite"` region.
- [ ] No color-only status encoding — every badge carries text (+ icon where space allows).
- [ ] All `prefers-reduced-motion: reduce` users see no looping animation.
- [ ] Full keyboard operability of the scan → check-in → check-out journey.

### Visual consistency
- [ ] Exactly one color token set; `grep -r "blue-[0-9]" resources/views/` returns nothing outside the token definition.
- [ ] All six authenticated pages render inside `x-app-shell`.
- [ ] Logout is reachable from every authenticated page.
- [ ] All dates/times render in Indonesian.
- [ ] Type scale matches §6.3; no text below 13px.
- [ ] One radius scale, one shadow token.

### Responsive
- [ ] No horizontal scroll at **320 / 375 / 414 / 768 / 1024 / 1440px**.
- [ ] Room list is a card list below 768px and a table at/above 1024px.
- [ ] No truncated room names at any width (currently "Rua…" at 800px).
- [ ] Touch journey usable one-handed at 375px.

### Preserved behavior *(regression guard — business logic must not change)*
- [ ] `POST /scan` still only stages a pending scan; check-in still requires `POST /scan/checkin`.
- [ ] Duration still computed server-side as `sks × 50 + 60`.
- [ ] Re-scan still performs check-out for the session owner.
- [ ] Sensor staleness still 90s; stale data still renders `UNKNOWN`.
- [ ] Role gate on `/admin/qr-ruangan` still enforced.
- [ ] All routes, CSRF tokens, and session keys unchanged.

### Visual regression procedure
1. `php artisan migrate:fresh --seed` on a scratch database (never the dev DB — `AGENTS.md` warning).
2. Capture screenshots at 375 / 768 / 1280 for: login, dashboard (occupied + available + unknown), scanner, scan-result form, scan-result success, history (empty + populated), room modal, QR sheet.
3. Baseline-diff against the current build. Any diff outside the intended change is a regression.
4. Re-run Lighthouse and the contrast/touch-target measurement script.

---

## 10. Open Questions for the Owner

1. **Manual QR token fallback** (P1-3) — `DESIGN.md` §E and `PRD.md` FR-05 require it; the current UI explicitly states it doesn't exist. Add it, or update both specs? Adding it may weaken the anti-bypass guarantee described in `AGENTS.md`.
2. **Primary color** — adopt `DESIGN.md` §6 teal `#0F766E`, or keep the shipped blue `#2563EB` and amend the spec?
3. **Dashboard sidebar** — `DESIGN.md` §4 specifies a persistent desktop sidebar. The current build uses a top bar only. Implement the sidebar, or amend the spec to top-bar-only?
4. **Dark mode** — not in `DESIGN.md`. In scope, or explicitly out?
5. **CDN Tailwind → build step** — staying on CDN keeps the stack as-is but costs a FOUC and blocks theming from a single config. Keep as-is for this pass?
