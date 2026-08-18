# Glorikar Engineering — Aircon Services Platform

Mobile-first PWA for an aircon services business: clients book cleaning, installation,
relocation, repair, and inspection jobs; admins plan team routes, dispatch technicians,
track field status, and invoice clients.

## Stack

| Layer      | Tech                                                        |
|------------|-------------------------------------------------------------|
| Frontend   | HTML + CSS (dark theme, no framework) + vanilla JS          |
| Backend    | PHP 8 REST API (no framework, no composer needed locally)   |
| Database   | MySQL / MariaDB                                             |
| PWA        | `manifest.json` + service worker (installable, Web Push)    |

## Directory layout

```
backend/
  api/            REST endpoints (auth, bookings, schedule, teams, jobs, notifications, push, services)
  cron/           run-scheduling.php  (CLI: creates draft schedules)
  db/             schema.sql, seed data
  includes/       env loader, PDO, auth/role guards, CSRF, rate limiting, CORS, helpers
  services/       Mapbox, trip scoring, clustering, route optimization, scheduling engine,
                  Web Push (VAPID), notifications
  uploads/        job photos (blocked from executing PHP)
frontend/
  admin/          admin pages (dashboard, route map, schedules, teams, dispatch, job completion, invoices)
  client/         client pages (book, select dates, confirm, bookings, status, invoice)
  shared/         notifications, profile
  includes/       server-side partials + role guard
  assets/         css + js
tools/            make-icons.php (PWA icons), generate-vapid.php (Web Push keys)
dev-router.php    local dev server router
```

## Local setup (XAMPP)

1. Start MySQL in XAMPP. Create the database and seed the admin + demo data:

   ```sh
   php backend\install.php
   ```

2. Configure `backend/.env` (copy from `.env.example`). Defaults work for stock XAMPP
   (root with no password, `glorikar` DB). Override `RATE_LIMIT_MAX` in dev to avoid throttling.

3. Serve the app with the built-in router (no Apache needed):

   ```sh
   php -S localhost:8000 dev-router.php
   ```

   Open `http://localhost:8000/`.

4. (Optional) Generate Web Push keys if you intend to test notifications:

   ```sh
   php tools\generate-vapid.php
   ```
   Paste into `backend/.env` (`VAPID_PUBLIC_KEY` / `VAPID_PRIVATE_KEY`) and the public key
   into `frontend/assets/js/config.js` (`GL_CONFIG.VAPID_PUBLIC_KEY`).

5. (Optional) Forgot-password emails — set `EMAIL_USER` and `EMAIL_APP_PASSWORD` in `backend/.env`
   (a Gmail address + its 16-character app password). "Forgot password?" on the login page then
   sends a one-time reset link (PHPMailer, Gmail SMTP). A quick send test:

   ```sh
   php tools\test-mail.php
   ```

## Accounts

- Admin: `boss@glorikar.com` — the password is **generated randomly and printed by the installer**;
  it is never hardcoded in the repo. Save it when you run `php backend/install.php`
  (re-running the installer regenerates it).
- Demo client: `client1@example.com` / `Test1234`

> **Before any real deploy:** rotate the seeded admin password (or disable that account) on the
> server. Since the installer prints a per-install random password this is already unique to your
> machine, but any credential that ever appeared in a printed install log should still be rotated
> before a production deployment.

## Scheduling engine

`php backend\cron\run-scheduling.php` picks up pending bookings and:

1. Geocodes missing coordinates via Mapbox (haversine fallback).
2. Clusters bookings within 5 km.
3. Scores clusters (`trip = services×10 − distance×2`, minimum 10).
4. Routes stops nearest-neighbour with ETA estimates.
5. Assigns the team with the least workload for the day (8 h cap) and creates **drafts**.

Admins approve drafts on `admin/schedule-detail.php`, then dispatch — the team and the
client receive in-app (and push) notifications.

## Production notes

- Serve `frontend/` and `backend/` separately (API must be reachable at `API_URL`).
  Copy `frontend/.htaccess.example` → `frontend/.htaccess` and force HTTPS.
- Add a real `MAPBOX_ACCESS_TOKEN` for geocoding + map tiles.
- Run the scheduler as a cron job: `php backend/cron/run-scheduling.php`
- Web Push requires HTTPS (service workers + push are only available on secure origins;
  `localhost` is exempt for local dev).
- `composer.json` lists optional deps for production (JWT, phpseclib) — not required locally.
- **Vendored PHPMailer** (v7.1.1 in `backend/vendor/phpmailer/`) is committed because composer isn't
  used locally. That means you own its maintenance — periodically check for CVE/security
  advisories and re-vendor the `src/` files when a fix lands.
- **Reset-link host:** the forgot-password email builds its link from `RESET_LINK_BASE` in `.env`.
  Leave it empty only on localhost (falls back to the request host). In production set it to the
  canonical origin (e.g. `https://glorikar.example.com`) so a forged `Host` header can't rewrite
  the link; also configure your server to reject unrecognized hosts.
- **Content-Security-Policy:** none is shipped (the pages use inline styles/scripts), so there
  is currently no CSP to block push or Mapbox. If you add one, allow `'self'`, `'unsafe-inline'`
  for styles, plus `api.mapbox.com` / `tiles.mapbox.com` (Mapbox GL JS, tiles, geocoding).
  Web Push delivery is handled by the browser's push service (e.g. `fcm.googleapis.com`) and is
  **not** subject to the page CSP — only the same-origin subscribe call is.

## Security

- PHP sessions, CSRF token (`X-CSRF-Token` header) on all mutating requests.
- Rate limiting via the `auth_audit` table (window + max configurable in `.env`).
- Role guards on every page and endpoint (`admin`, `client`); foreign bookings return 404.
- Uploaded files: extension + MIME checks, random names, and a `.htaccess` that blocks PHP
  execution in `backend/uploads/`.