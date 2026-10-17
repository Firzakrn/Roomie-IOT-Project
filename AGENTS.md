# AGENTS.md

Laravel 10 classroom check-in/check-out app ("Roomie") plus an ESP32/Wokwi IoT
sensor feed. UI text, code comments, and route paths are in Indonesian — keep
that language for user-facing strings and comments; use English for identifiers.

## Commands

```bash
composer install
php artisan migrate --seed        # idempotent: aman dijalankan ulang
php artisan serve                 # APP_URL is http://127.0.0.1:8000
php artisan test                  # see the warning below
vendor/bin/pint                   # Laravel preset; no pint.json exists
php artisan route:list
```

- **Do not run `npm install` / `npm run build`.** No Blade view uses `@vite`;
  Tailwind comes from `cdn.tailwindcss.com` and the QR scanner/labels load
  `html5-qrcode` / `qrcodejs` from jsDelivr. Vite is vestigial in this repo.
- `npm` itself is blocked by the PowerShell execution policy on this machine —
  use `npm.cmd` if you ever need it.
- Requires a live MySQL (`roomie` on 127.0.0.1:3306). There is no Docker/Sail
  setup, no queue worker, no scheduled jobs.

## Testing pitfalls

- There is **no `.env.testing`** and the sqlite lines in `phpunit.xml` are
  commented out, so `php artisan test` runs against the **dev MySQL database**.
  Treat any test that writes data as dangerous; don't run migrations or seeders
  "just to check".
- `tests/Feature/ExampleTest.php` is stock Laravel and **fails on `main`**: it
  expects `200` from `/`, which is behind `auth` middleware and returns `302`.
  This is expected — do not change the app to make it pass.
- Only `UserFactory` exists. There are no factories for `Room`, `Reservation`,
  or `RoomSession`, and no `RefreshDatabase` usage anywhere.
- `DatabaseSeeder` is idempotent and safe to re-run. Users and rooms use
  `firstOrCreate`, so an existing `password` is never overwritten and — critically
  — an existing `rooms.qr_token` is **not** re-randomised, which would kill every
  printed door label. Seeded logins (password `password123`):
  `admin@kampus.ac.id` (role `admin`), `nadia@kampus.ac.id` (role `mahasiswa`).
- `HistorySeeder` adds demo usage history: 16 rows for Nadia (2 pages at 10/page)
  and 3 for admin. Every timestamp is computed from `now()` with the same
  `sks × 50 + 60` formula as `RoomSessionController::storeCheckin()`, and it
  seeds exactly **one** `ACTIVE` session (Nadia's, so the dashboard bento and
  notification centre have something to show). `reservations` has no unique
  constraint, so a second run would silently duplicate rows — the seeder
  therefore skips itself when the user already has history. To reseed:
  `DELETE FROM room_sessions; DELETE FROM reservations;` then re-run.
- The seeded `ACTIVE` session expires ~4h after seeding and is only swept to
  `EXPIRED` during a scan (`expireOldSessions`), so a day-old database shows that
  room as overdue until someone scans it.

## Domain rules that are easy to break

**Two-step scan — `POST /scan` does NOT check anyone in.**
`RoomSessionController::scan` only resolves the `qr_token`, checks for
conflicts, and stashes `roomie_pending_scan` (room_id, user_id, sha256 of the
token, 5-minute expiry) in the session, then redirects to
`/scan/hasil/{code}`. `POST /scan/checkin` is what creates the `Reservation`
+ `RoomSession`. Opening the result URL directly cannot check you in.

**Duration and timestamps are server-computed, never client input.**
`end_at = now() + sks * config('roomie.minutes_per_sks') + config('roomie.grace_minutes')`
(50 min per SKS + 60 min grace). The form submits only `course_name` and `sks`.

**`reservations` are usage logs, not bookings.** There is deliberately no
booking endpoint (see the comment above the scan routes). Don't add one.

**Occupancy is a 3-part condition, never just `status = 'ACTIVE'`.** A room is
occupied iff it has an `ACTIVE` `RoomSession` whose `Reservation` is
`CONFIRMED` **and** `end_at > now()`. Copy the `whereHas('reservation', ...)`
shape from `DashboardController::activeSessionQuery` when you add queries.
Status enums: `reservations` = CONFIRMED|CANCELLED|COMPLETED;
`room_sessions` = NOT_STARTED|ACTIVE|COMPLETED|EXPIRED.

**Rescanning your own active session is the check-out.** `scan()` sees the
caller owns the active session and completes it instead of opening the form.

**All state transitions are `DB::transaction(..., 3)` with `lockForUpdate()`
on the room and/or user, and re-check occupancy at submit time.** Preserve this
pattern when touching check-in/check-out.

**Nothing runs in the background.** `app/Console/Kernel.php` has an empty
schedule; stale sessions are only swept opportunistically by
`expireOldSessions()` during a scan.

**Roles are assigned server-side.** Registration always writes role
`mahasiswa`. The only admin gate is an inline
`abort_unless(auth()->user()->role === 'admin', 403)` in
`DashboardController::qrLabels` — there is no policy or role middleware.

## IoT / device endpoints

- `routes/api.php` has **no auth middleware**. `RoomDeviceController` verifies
  the `X-Device-Key` header with `hash_equals` against
  `config('roomie.device_api_key')`, returning `503` if unset, `401` if wrong.
- **`ROOMIE_DEVICE_API_KEY` exists in `.env` but is missing from
  `.env.example`.** A fresh clone gets `503` on every `/api/room/*` call until
  it's added.
- Payload keys are Indonesian: `pintu` (required, `TERBUKA`|`TERTUTUP`),
  `gerak` (bool), `auto_checkout` (only honored when `gerak === false` and
  `session_id` is present), `suhu`. Stored on `rooms.sensor_payload`,
  `sensor_status`, `sensor_updated_at`.
- Sensor readings older than **90 seconds** render as `UNKNOWN` (see
  `DashboardController::roomSnapshot` and `getRoomModal`).
- The ESP32/Wokwi firmware and diagram are **not in this repo**. `cloudflared.exe`
  at the repo root is committed and used to tunnel the local server so the
  device can reach `/api/room/{code}/status` and `/sensor`.

## Frontend facts

- Dashboard live data is a plain `fetch` to `route('rooms.live')` every 10s
  (skipped when the tab is hidden) — no broadcasting/websockets, despite
  `laravel/broadcasting` and Sanctum being installed.
- Room detail is a server-rendered partial fetched into a modal from
  `resources/views/components/room-modal.blade.php` via
  `route('room.detail-modal')`.

## Style and git

- `.editorconfig`: 4 spaces, LF, final newline. `vendor/bin/pint` enforces the
  Laravel preset — run it before finishing.
- Work on a feature branch and open a PR. There is no CI, so run
  `php artisan test` and `vendor/bin/pint` locally and report results.
