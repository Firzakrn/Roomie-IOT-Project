# DESIGN.md - Roomie Smart Classroom & Monitoring System

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
- **Danger / Occupied**: `#EF4444` (Rose-500) — Room unavailable/blocked, Missed check-in penalty, Overdue session.
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

- **Structure**: Rounded-2xl (`16px`), padding `p-5`, subtle shadow `shadow-sm hover:shadow-md transition-all duration-200`.
- **Live IoT Status Indicator**:
    - Top right corner of each room card features a live status pill.
    - Includes a pulsing dot (`animate-ping`) when sensor data (PIR / Door Reed) updates in real-time.
    - Green pulse for active occupancy/motion, Yellow for unoccupied indicated, Red for locked/maintenance.
- **Classroom Quick Meta**:
    - Room Code & Name (e.g., `A-302 (Lab Computer)`).
    - Capacity & Facilities (Icons for LCD, AC, Wifi, SKS capacity).
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

---

## 5. Layout Architecture (Page Views)

1. **Dashboard (Overview)**:
    - Top Metrics Cards (Total Active Classes, Today's Reservations, IoT Sensor Health, Penalty Alerts).
    - Bento Grid of Room Cards filterable by Building, Availability, or Sensor Status.
2. **Room Booking Flow (PJ Kelas)**:
    - Step 1: Select Room & Date.
    - Step 2: Input SKS (auto-calculates duration end time: 1 SKS = 50 min).
    - Step 3: Confirmation modal with instant reservation lock.
3. **Check-in / Checkout Workspace**:
    - High-contrast mobile-friendly view with Camera QR Reader and manual fallback token input.
    - Pre-checkout verification checklist (Ensure AC off, LCD off, Doors locked).
4. **Admin Control Panel**:
    - Master data management for Rooms, Dynamic QR Token generation, and IoT ESP32 Telemetry Debug Logs.

---

## 6. Micro-Interactions & Animation Rules

- **Hover Effects**: Subtly scale cards by `1.01` with `ease-out duration-200`.
- **Status Pulse**: `animate-pulse` for live streaming data from ESP32.
- **Button Feedback**: Active click state `scale-95 transition-transform`.
- **Transitions**: Page/Tab switching uses fade-in slide-up (`opacity-0 translate-y-2` to `opacity-100 translate-y-0`).
