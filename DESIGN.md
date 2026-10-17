# DESIGN.md - Roomie Smart Classroom & Monitoring System

> ## 0. Status Implementasi (per 18 Oktober 2026)
>
> Dokumen ini adalah brief desain, bukan spesifikasi teknis. Beberapa bagiannya
> **tidak diimplementasikan**, dan alasannya dicatat di sini supaya pembaca
> berikutnya tidak salah mengira bagian itu hilang atau terlewat.
>
> | Bagian | Status | Catatan |
> |---|---|---|
> | §1 Dark mode | ✅ | `darkMode: 'class'` + toggle + `localStorage` |
> | §2 Palet & token | ✅ | Semua pasangan fg/bg lolos kontras AA di kedua tema |
> | §3 Tipografi | ✅ | Plus Jakarta Sans + JetBrains Mono, skala 24/18/15/14/12 |
> | §4.A Sidebar + topbar | ✅ | 240px · rail 64px · drawer < 1024px · topbar glass |
> | §4.A Notifications center | ✅ | Diturunkan dari snapshot live, **tanpa** store notifikasi |
> | §4.B Bento card | ✅ | Ruangan terpakai dapat lebar 2 kolom |
> | §4.B Ikon fasilitas (LCD/AC/Wifi) | ❌ | `rooms` tidak punya kolom facilities. Memalsukannya berarti menampilkan data yang tidak ada — kartu memakai kapasitas + lokasi + timeline sebagai gantinya. |
> | §4.C Scanner | ✅ | Reticle + laser, torch, ganti kamera, retry, fallback token manual |
> | §4.D Tabel riwayat | ✅ | `<table>` semantik ≥ 768px, kartu < 768px, header sticky |
> | §4.D Export PDF/CSV (BAP) | ❌ | PDF butuh library yang tidak tersedia dan repo ini tidak punya build step (`npm` dilarang). CSV belum diprioritaskan. |
> | §5.1 Metrik "Penalty Alerts" | ❌ → diganti | Tidak ada konsep penalti di domain: tidak ada tabel, aturan, maupun field yang bisa dihitung. Diganti **Sensor Stale** (> 90 detik), yang datanya benar-benar ada. |
> | §5.2 Room Booking Flow 3 langkah | ❌ → diganti | **Bertentangan dengan aturan domain.** `reservations` adalah catatan pemakaian, bukan booking; tidak ada endpoint pemesanan dan tidak akan ada. Alur nyata adalah scan dua langkah (lihat `AGENTS.md`). Form SKS + preview durasi di halaman hasil scan yang menggantikan langkah 2–3. |
> | §5.3 Input token manual | ✅ | Disclosure di halaman scanner, memakai `POST /scan` yang sudah ada |
> | §5.4 Admin Control Panel | ❌ | Master data ruangan, regenerate token, dan log telemetri butuh endpoint + tabel baru. Tidak dikerjakan pada tahap ini. Yang ada: sheet cetak label QR. |
> | Filter chip pada dashboard | ❌ | Sudah dibuat lalu **dihapus**. Dengan 3 ruang kelas, 9 tombol filter memakan ruang vertikal lebih besar dari yang dihemat; tiap kartu sudah membawa badge status dan pulse-nya. Hanya search bar yang tersisa. |
>
> Prinsip yang dipegang: jangan tampilkan kontrol yang tidak bisa bekerja, dan jangan
> tampilkan angka yang tidak berasal dari data.

---

## 1. Visual Identity & Theme Strategy

- **Design Philosophy**: Modern, High-Density SaaS Dashboard mixed with Real-time Industrial Monitoring. Clean, precise, and human-centric.
- **Theme**: Light Mode default with Dark Mode support (Slate/Zinc neutral base).
- **Core Aesthetic**: Bento-grid dashboard, soft borders (`border-slate-200/80` or `border-zinc-800`), subtle backdrop blurs, crisp status badges, and live pulse indicators for IoT feeds.

---

## 2. Color Palette & Semantic Tokens

### Neutral Palette (Slate/Zinc Base)

- **Background App**: `#F8FAFC` (Slate-50) / `#090D16` (Dark)
- **Card / Surface**: `#FFFFFF` (White) / `#111827` (Dark Surface)
- **Borders**: `#E2E8F0` (Slate-200) / `#1E293B` (Slate-800)
- **Text Primary**: `#0F172A` (Slate-900) / `#F8FAFC` (Slate-50)
- **Text Secondary**: `#64748B` (Slate-500) / `#94A3B8` (Slate-400)

### Primary & Accent Colors

- **Primary Brand**: `#4F46E5` (Indigo-600) — Primary actions, active navigation, main CTA buttons.
- **Primary Hover**: `#4338CA` (Indigo-700)
- **Primary Subtle**: `#EEF2FF` (Indigo-50) — Highlight backgrounds, selected tabs.

### Status & State Colors

- **Success / Available**: `#10B981` (Emerald-500) — Room available, Check-in active, Sensor PIR active.
- **Warning / Indicated**: `#F59E0B` (Amber-500) — Pending checkout, Unoccupied indicated timeout, Warning state.
- **Danger / Occupied**: `#EF4444` (Rose-500) — Room unavailable/blocked, overdue session, expired room session.
- **Info / Reserved**: `#0EA5E9` (Sky-500) — Reserved upcoming, QR Code scanned successfully.

---

## 3. Typography & Hierarchy

- **Font Family**: Primary: `Plus Jakarta Sans`, `Inter`, or `Geist Sans`, sans-serif. Monospace for Tokens/Log ID: `JetBrains Mono` or `Fira Code`.
- **Scale**:
    - `Display / Page Title`: 24px (1.5rem) | Bold (700) | Tracking tight
    - `Section Heading`: 18px (1.125rem) | SemiBold (600)
    - `Card Title`: 15px (0.9375rem) | SemiBold (600)
    - `Body Text`: 14px (0.875rem) | Regular (400) / Medium (500)
    - `Small / Badge Text`: 12px (0.75rem) | Medium (500) / SemiBold (600) | Uppercase tracking wider

---

## 4. Component Design System & Patterns

### A. Navigation & Topbar

- **Sidebar**: Fixed vertical sidebar (64px collapsed icon-only, 240px expanded).
    - Active item: Indigo-600 background indicator pill on the left border with smooth transition.
- **Topbar**: Sticky glassmorphism bar (`backdrop-blur-md bg-white/80`).
    - Contains Quick QR Scanner CTA button (`Indigo-600` filled with camera icon), notifications center, and User Profile menu (PJ Kelas / Admin badge).

### B. Classroom & Monitoring Bento Cards

> ⚠️ Baris "Capacity & Facilities (LCD/AC/Wifi)" **tidak diimplementasikan** — tabel
> `rooms` tidak punya kolom facilities. Kartu memakai kapasitas, lokasi
> (gedung/lantai), dan timeline sesi sebagai gantinya.

- **Structure**: Rounded-2xl (`16px`), padding `p-5`, subtle shadow `shadow-sm hover:shadow-md transition-all duration-200`.
- **Live IoT Status Indicator**:
    - Top right corner of each room card features a live status pill.
    - Includes a pulsing dot (`animate-ping`) when sensor data (PIR / Door Reed) updates in real-time.
    - Green pulse for active occupancy/motion, Yellow for unoccupied indicated, Red for locked/maintenance.
- **Classroom Quick Meta**:
    - Room Code & Name (e.g., `A-302 (Lab Computer)`).
    - ~~Capacity & Facilities (Icons for LCD, AC, Wifi, SKS capacity).~~ → kapasitas, gedung, lantai.
    - Current Schedule / Active Reservation timeline bar.

### C. QR Code Scanner Component

- **Modal Overlay**: Dark backdrop overlay (`bg-slate-900/60 backdrop-blur-sm`).
- **Camera Viewport**:
    - Square frame with glowing rounded corner brackets (Indigo/Emerald animated border).
    - Laser line scanning animation moving vertically (`animate-scan`).
    - Real-time feedback state (Green checkmark flash on successful scan, Red shake on error/unauthorized).

### D. Data Tables & History Lists (BAP Logs)

- **Header**: Clean sticky header with subtle bottom border.
- **Badges**:
    - `Completed`: Soft Emerald bg (`bg-emerald-50 text-emerald-700 border-emerald-200`)
    - `Active`: Soft Indigo bg (`bg-indigo-50 text-indigo-700 border-indigo-200`)
    - `Cancelled / Late`: Soft Rose bg (`bg-rose-50 text-rose-700 border-rose-200`)
- **Export Actions**: Quick PDF / CSV download button with icon for BAP reports.
    > ⚠️ **Tidak diimplementasikan.** PDF butuh library yang tidak tersedia, dan repo
    > ini sengaja tidak punya build step (`npm` dilarang). Butuh endpoint baru juga.

---

## 5. Layout Architecture (Page Views)

1. **Dashboard (Overview)**:
    - Top Metrics Cards (Total Active Classes, Today's Reservations, IoT Sensor Health, **Sensor Stale**).
        > ⚠️ "Penalty Alerts" diganti **Sensor Stale**. Tidak ada konsep penalti di domain
        > — tidak ada tabel, aturan, atau field yang bisa dihitung.
    - Bento Grid of Room Cards, **with a search bar only**.
        > ⚠️ Filter chip by Building / Availability / Sensor Status **dihapus dengan
        > sengaja**: dengan 3 ruang kelas, 9 tombol filter memakan ruang vertikal lebih
        > besar dari yang dihemat, dan tiap kartu sudah membawa badge status sendiri.
2. **Room Booking Flow (PJ Kelas)**:
    > ⚠️ **Bagian ini dibatalkan** — bertentangan dengan aturan domain. `reservations`
    > adalah catatan pemakaian, bukan booking; tidak ada endpoint pemesanan dan tidak
    > akan ada (lihat komentar di `routes/web.php`). Alur yang benar: **scan dua
    > langkah** (`POST /scan` → konfirmasi → `POST /scan/checkin`). Form SKS beserta
    > preview durasi di halaman hasil scan yang menggantikan langkah 2–3.
    - ~~Step 1: Select Room & Date.~~ → Step 1: pindai QR di pintu.
    - Step 2: Input SKS (auto-calculates duration end time: 1 SKS = 50 min). ✅
    - ~~Step 3: Confirmation modal with instant reservation lock.~~ → digabung ke step 2.
3. **Check-in / Checkout Workspace**:
    - High-contrast mobile-friendly view with Camera QR Reader and manual fallback token input.
    - ~~Pre-checkout verification checklist (Ensure AC off, LCD off, Doors locked).~~
        > ⚠️ **Tidak diimplementasikan** — tidak ada endpoint untuk menyimpan checklist,
        > dan tidak ada data apakah AC/LCD benar-benar mati. Checklist yang tidak bisa
        > diverifikasi hanya menambahkan UI yang berbohong. Yang tersedia: status pintu
        > dan gerak dari sensor ESP32.
4. **Admin Control Panel**:
    > ⚠️ **Tidak diimplementasikan** — master data ruangan, regenerate token, dan log
    > telemetri butuh endpoint + tabel baru. Yang tersedia sekarang hanya **sheet cetak
    > label QR** (`/admin/qr-ruangan`).

---

## 6. Micro-Interactions & Animation Rules

- **Hover Effects**: Subtly scale cards by `1.01` with `ease-out duration-200`.
- **Status Pulse**: `animate-pulse` for live streaming data from ESP32.
- **Button Feedback**: Active click state `scale-95 transition-transform`.
- **Transitions**: Page/Tab switching uses fade-in slide-up (`opacity-0 translate-y-2` to `opacity-100 translate-y-0`).
