# Roomie — Project Summary

Smart classroom occupancy and usage-tracking system for a university campus,
built on Laravel 10. Students check into physical classrooms by scanning a QR
code on the door with their phone; an ESP32 sensor node in each room reports
door and motion state back to the server, which drives live occupancy
monitoring and automatic check-out.

The project's language is Indonesian throughout the domain — UI copy, code
comments, route paths (`/scan`, `/riwayat`, `/daftar`), and IoT payload keys
(`pintu`, `gerak`) are all Indonesian by design, not placeholder text.

## Tech stack

| Layer | Choice |
|---|---|
| Backend | Laravel 10.x, PHP `^8.1` (CLI here is 8.4.12) |
| Auth | Session-based web guard; Sanctum installed but unused by the app |
| Database | MySQL (`roomie`), `utf8mb4`, InnoDB |
| Frontend | Blade + Tailwind via CDN (`cdn.tailwindcss.com`) |
| QR scanning | `html5-qrcode@2.3.8` (jsDelivr), camera-based, no native app |
| QR printing | `qrcodejs@1.0.0` (jsDelivr), admin print sheet |
| Hardware | ESP32 + PIR, simulated on Wokwi (firmware not in this repo) |
| Tests | PHPUnit 10 (2 stock tests, 1 failing — see Testing) |
| Lint | Laravel Pint (Laravel preset, no `pint.json`) |

No queue worker, no scheduler, no Docker/Sail, no CI. `resources/js` and
`vite.config.js` are vestigial — no Blade view references `@vite`, so the npm
toolchain is not part of the build.

Scale is small and self-contained: 60 PHP files / ~2,800 lines, 9 Blade
templates / ~750 lines, 4 migrations beyond the Laravel defaults, 3 commits on
`main`.

## Domain model

```
User (role: admin | mahasiswa)
  └──< Reservation   (usage log, NOT a booking)
        └── RoomSession  (the actual check-in/out lifecycle)

Room (code, qr_token, is_active, sensor_*, sensor_payload)
```

`Reservation` and `RoomSession` are deliberately near-duplicates kept for
backwards compatibility with the original schema. `Reservation` records the
*course* being studied (`course_name`, `sks`, `start_at`, `end_at`); the
`RoomSession` records the *physical* usage of the room and is what the
occupancy logic actually queries. Every `Reservation` has exactly one
`RoomSession` — enforced by a unique index added in the last migration.

Status enums are database-level enums, not PHP constants:

- `reservations`: `CONFIRMED` | `CANCELLED` | `COMPLETED`
- `room_sessions`: `NOT_STARTED` | `ACTIVE` | `COMPLETED` | `EXPIRED`

Note that `NOT_STARTED` is the column default but is never written by the
application — sessions are created directly as `ACTIVE`.

## Core flow: scan, then confirm

The central design decision is that **scanning does not check you in**. A
single scan cannot create a reservation, because the app refuses to let the
client choose the duration:

1. `POST /scan` validates `qr_token` against `rooms.qr_token`, checks for
   conflicts, then stashes `roomie_pending_scan` in the Laravel session
   (`room_id`, `user_id`, SHA-256 of the token, 5-minute TTL) and redirects to
   `/scan/hasil/{code}` with a flash action of `form`.
2. The result page renders the check-in form **only** if that pending entry
   matches the room and the current user. Opening the URL directly shows no
   form, so the flow can't be bypassed by hand-crafting a link.
3. `POST /scan/checkin` re-validates the pending token, then creates the
   `Reservation` + `RoomSession` pair.

Duration is computed server-side from the SKS count:

```
end_at = now() + (sks * 50 minutes) + 60 minutes grace
```

Both constants live in `config/roomie.php`. Clock times are always server
`now()`; the browser only submits `course_name` and `sks`.

Re-scanning the same QR while you own the active session is the check-out
gesture — `scan()` detects the ownership and completes the session instead of
opening the form. This is why the scanner copy reads "scan pertama =
check-in, scan kedua = check-out."

## Concurrency

Every state transition is wrapped in `DB::transaction($fn, 3)` (3 deadlock
retries) with `lockForUpdate()` on the room and/or user rows. Occupancy is
re-checked inside the transaction at check-in time, not only at scan time,
precisely because two students can scan the same door seconds apart. The same
3-part occupancy predicate is repeated in four places and must stay consistent:

```php
RoomSession::where('room_id', $room->id)
    ->where('status', 'ACTIVE')
    ->whereHas('reservation', fn ($q) => $q->where('status', 'CONFIRMED')
                                       ->where('end_at', '>', $now))
```

Expired sessions are swept opportunistically by `expireOldSessions()` during
scans — there is no background job or scheduled command, so a room whose
occupant simply walked away stays "occupied" in the UI until the next scan
touching that room (or a device auto check-out) occurs.

## IoT integration

An ESP32 node per room polls and reports over plain HTTP. Both endpoints live
in `routes/api.php` with **no auth middleware**; `RoomDeviceController` verifies
an `X-Device-Key` header using `hash_equals` against
`config('roomie.device_api_key')` — `503` if unset, `401` if wrong.

- `GET /api/room/{code}/status` → `OCCUPIED` / `UNOCCUPIED` / `NOT_FOUND`,
  plus the course name and end time, so the device can drive an LED/display.
- `POST /api/room/{code}/sensor` → stores the latest reading and handles
  auto check-out.

Payload keys are Indonesian and validated strictly: `pintu` (required,
`TERBUKA`|`TERTUTUP`), `gerak` (bool), `suhu` (numeric), and `auto_checkout`
with a `session_id`. The server enforces the business rule that an
auto check-out is only accepted when the PIR reports **no** motion
(`gerak === false`) and a valid `session_id` is supplied — a student present
but still is never logged out by a stale or noisy sensor. That transition runs
inside its own locked transaction and flips the session to `EXPIRED` with the
reservation to `COMPLETED`.

Readings older than **90 seconds** are treated as stale and render as
`UNKNOWN` / "Perlu diperiksa", which is why the dashboard has an amber
"Perlu Diperiksa" counter. The local server is exposed to the device through
the committed `cloudflared.exe` tunnel, since camera access and device calls
both require HTTPS.

Known gap: `ROOMIE_DEVICE_API_KEY` is set in `.env` but absent from
`.env.example`, so a fresh clone returns `503` on every device endpoint.

## Web surface

Session-authenticated (except login/register). Indonesian paths:

| Route | Purpose |
|---|---|
| `GET /` | Dashboard — stat tiles, room table, client-side search |
| `GET /rooms/live` | JSON snapshot polled every 10s |
| `GET /scan` | Camera scanner (`html5-qrcode`) |
| `POST /scan` | Token resolution → pending session, or check-out |
| `POST /scan/checkin` | Confirmation → creates reservation + session |
| `GET /scan/hasil/{code}` | Result page, conditionally renders the form |
| `GET /riwayat` | Paginated personal usage history (10/page) |
| `GET /room/{code}/detail-modal` | Blade partial for the detail modal |
| `GET /admin/qr-ruangan` | Printable QR sheet — `admin` role only |
| `GET /daftar`, `POST /daftar` | Self-service registration |

Live updates are plain `fetch` polling against `rooms.live`, skipped while the
tab is hidden, not websockets — broadcasting and Sanctum are installed but
unused. Room rows are matched to payload items by `data-room-code` attribute
comparison rather than interpolated selectors.

Access control is thin: registration always assigns `mahasiswa` server-side,
and the only admin gate is an inline
`abort_unless(auth()->user()->role === 'admin', 403)` in
`DashboardController::qrLabels`. There is no policy class or role middleware.
Write routes are throttled inline (`throttle:8,1` on login, `5,1` on register,
`12,1` on scan).

## Testing

```bash
php artisan test      # Tests: 1 failed, 1 passed
vendor/bin/pint
```

The suite is stock Laravel scaffolding, not a real test suite. There is no
`.env.testing` and the sqlite overrides in `phpunit.xml` are commented out, so
**`php artisan test` runs against the development MySQL database** — any test
that writes data is a real risk to dev data. `tests/Feature/ExampleTest.php`
fails on `main` because it asserts `200` from `/`, which is behind `auth`
middleware and correctly returns `302`; the failure is expected and should not
be "fixed" by changing the app. Only `UserFactory` exists — there are no
factories or `RefreshDatabase` usage for `Room`, `Reservation`, or
`RoomSession`, so writing the first real feature tests means creating them
plus an isolated test database first.

`DatabaseSeeder` uses `firstOrCreate()` for users and rooms, so it **is**
idempotent and safe to re-run — existing passwords are never overwritten and,
critically, existing `rooms.qr_token` values are **not** re-randomised (which
would invalidate every printed door label). `HistorySeeder` is the one that
would silently duplicate rows on a second run because `reservations` has no
unique constraint; it therefore skips itself when the user already has history.
To reseed history: `DELETE FROM room_sessions; DELETE FROM reservations;` then
re-run. Seeded accounts (password `password123`): `admin@kampus.ac.id` (admin),
`nadia@kampus.ac.id` (mahasiswa), plus three rooms with random 32-char QR
tokens.

## Setup

```bash
composer install
cp .env.example .env          # then add ROOMIE_DEVICE_API_KEY (missing from the template)
php artisan key:generate
php artisan migrate --seed
php artisan serve             # http://127.0.0.1:8000
```

Requires a running MySQL reachable at `127.0.0.1:3306`. Camera scanning needs
HTTPS on a phone, hence the Cloudflare tunnel for on-device testing. On this
Windows machine `npm` is blocked by the PowerShell execution policy — use
`npm.cmd` if it ever becomes necessary.

## Current state

Feature-complete for its PRD: authentication, QR check-in/out, live
occupancy monitoring, usage history, admin QR printing, and the IoT sensor
pipeline are all implemented and wired together. The gaps are in engineering
infrastructure rather than features — no real test suite, no CI, no isolated
test database, a missing `.env.example` entry for the device key, an inline
admin authorization check with no policy layer, and no background sweeper for
expired sessions.

Agent-facing conventions and pitfalls live in `AGENTS.md`.
