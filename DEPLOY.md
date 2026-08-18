# GLORIKAR ENGINEERING — Production Deployment Guide (Aiven + Render)

Deployment target: **Render** (hosts the PHP app + cron) and **Aiven** (hosts
MySQL over TLS). The stack is PHP 8.2+ + MySQL 8 with a PWA/Web Push frontend.

> **Read the whole file first.** HTTPS (Step 6) **must** be live before Web
> Push / PWA can work in a browser. VAPID keys (Step 8) must exist before the
> push smoke test passes.

---

## 1. Architecture Overview

```
Mobile / Desktop browser
        │  HTTPS
        ▼
Render Web Service (this repo, PHP 8.2+ / Apache)
  ├── static pages + PWA (login.php, admin/, client/, assets/, manifest.json)
  ├── API endpoints (api/*.php)
  └── backend/ (includes, services, cron, uploads)
        │  MySQL over TLS (DB_SSL_CA=1 → backend/aiven-ca.pem)
        ▼
Aiven MySQL (managed, free TLS certs)
        ▲
Render Cron Job (same repo) — runs the scheduler nightly at 22:00
```

Two separate Render services use the **same repository**:

| Render service type | Purpose                                     | Command                  |
|---------------------|---------------------------------------------|--------------------------|
| **Web Service**     | serves the site + API over HTTPS            | PHP/Apache (see §4)      |
| **Cron Job**        | nightly scheduler                           | `php backend/cron/run-scheduling.php` |

---

## 2. Platform Requirements

Render's managed PHP runtime already provides most of this. The PHP extensions
you must confirm are enabled (Render PHP: `curl`, `mbstring`, `pdo_mysql`,
`openssl` are on by default; `zip` is required for the build step):

- **PHP 8.2+** (Render PHP runtime is 8.2/8.3+)
- **MySQL 8** — the Aiven service
- **Extensions**: `pdo_mysql`, `openssl`, `curl`, `mbstring`, `json`, `zip`
- `gd` / `imagick` — *optional* (only needed to regenerate PWA icons)

Verify locally before pushing:

```bash
php -v
php -m | grep -E 'openssl|zip|curl|mbstring|json|pdo_mysql'
```

---

## 3. Database Setup — Aiven MySQL

### 3.1 Create the service

1. Aiven console → **Create service** → **MySQL** (free tier is fine for dev).
2. Choose a cloud region close to Render (or the same region).

### 3.2 Download and commit the CA certificate

1. Aiven console → your MySQL service → **Connection info** → **CA Certificate**.
2. Download it and save it in the repo as **`backend/aiven-ca.pem`**.
3. **Commit it to git.** It is a *public* certificate — safe to commit; the
   server private key is never shipped by Aiven.
4. `db.php` reads it from `__DIR__ . '/../../backend/aiven-ca.pem'` and enables
   MySQL SSL only when `DB_SSL_CA` is non-empty (Step 5), so local XAMPP keeps
   connecting over plain TCP.

### 3.3 Create the database and an app user

Aiven console → your MySQL service → **Create Database** (`glorikar`), then
**Create User** (e.g. `glorikar_app`) — the service-generated `avnadmin` works
too but a dedicated app user is cleaner. Grant it only what the app needs:

```sql
-- run in Aiven console → your service → Query editor
CREATE DATABASE glorikar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT SELECT, INSERT, UPDATE, DELETE ON glorikar.* TO 'glorikar_app'@'%';
```

> The app only ever needs `SELECT/INSERT/UPDATE/DELETE`. Do **not** grant
> `CREATE/DROP/ALTER` — schema changes are applied manually below.

### 3.4 Load the schema, then the seed data (in that order)

In Aiven console → your MySQL service → **Query editor**:

1. Paste and run the full contents of `backend/db/schema.sql`.
2. Paste and run the full contents of `backend/db/seed.sql`.

- `schema.sql` — all tables (users, bookings, services, schedules, invoices,
  job_photos, booking_status_log, push_subscriptions, …).
- `seed.sql` — the 5 service types and an **optional, commented-out** admin
  account. Uncomment the admin insert if you want a seeded admin, then change
  the password immediately after first login.

---

## 4. Deploy the App to Render

### 4.1 Create the Web Service

1. Push this repo to GitHub (the Aiven CA cert in `backend/` is committed).
2. Render dashboard → **New + → Web Service** → pick the repo.
3. Service type: **Web Service**, Root Directory: repo root.
4. Runtime: **PHP** (Render auto-detects; ensure PHP 8.2+).
5. **Build command**: `composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader`
6. **Start command**: `apache2-foreground` (Render's PHP runtime — `.htaccess`
   rules are honored).

> **Do NOT commit these** (add to `.gitignore` / `.dockerignore`):
> `backend/.env` (real secrets — created via env vars instead, Step 5),
> `backend/vendor/` (installed by the build command),
> `context.txt`, `GLORIKAR_SPEC_WEB.md` (internal docs).

### 4.2 Directory permissions

`backend/uploads/` and `backend/logs/` must be writable by the PHP process.
Render's web service filesystem is ephemeral per deploy, so on Render these
directories are created at runtime (the app `mkdir()`s `backend/uploads/job-photos/`
on demand). On any non-Render host, still apply:

```bash
chmod 750 backend/uploads
chmod 750 backend/logs
```

---

## 5. Environment Variables — Render Dashboard

**There is no `.env` file on Render.** Put **every** value in:

> Render dashboard → your Web Service → **Environment** tab → **Add Environment Variable**

| Key                    | Value / where to get it                                                              |
|------------------------|--------------------------------------------------------------------------------------|
| `DB_HOST`              | Aiven → Connection info → Host (e.g. `mysql-abc123-xyz.aivencloud.com`)               |
| `DB_PORT`              | Aiven → Connection info → Port (e.g. `25060`)                                        |
| `DB_NAME`              | `glorikar`                                                                           |
| `DB_USER`              | the app user from §3.3 (or `avnadmin`)                                               |
| `DB_PASS`              | that user's password                                                                 |
| `DB_SSL_CA`            | **`1`** — non-empty = enable MySQL SSL with `backend/aiven-ca.pem` (local: leave unset) |
| `JWT_SECRET`           | `openssl rand -hex 32`                                                               |
| `MAPBOX_ACCESS_TOKEN`  | public token from mapbox.com (address geocoding)                                     |
| `VAPID_PUBLIC_KEY`     | from §8 — must match `assets/js/config.js`                                           |
| `VAPID_PRIVATE_KEY`    | from §8 — server-side only, never expose                                            |
| `DEPOT_LAT`            | your depot/office latitude (e.g. `14.3294`)                                          |
| `DEPOT_LNG`            | your depot/office longitude (e.g. `120.9367`)                                        |
| `MIN_TRIP_SCORE`       | default `10`                                                                         |
| `MAX_DAILY_HOURS`      | default `8`                                                                          |

**Set the same env vars on the Cron Job service too** (Step 7) — it needs the
database and scheduler settings.

### Frontend config (one-time, committed to git)

In `assets/js/config.js`, set the live values:

```js
export const API_URL          = 'https://your-app.onrender.com'; // the Render service URL
export const MAPBOX_TOKEN     = 'pk.your_mapbox_public_token';
export const VAPID_PUBLIC_KEY = '…from Step 8…';                 // must match the env var
```

---

## 6. HTTPS (required — Web Push and Service Workers break without it)

Browsers only allow `pushManager.subscribe()` and service workers on secure
origins. **Render auto-provisions a free TLS certificate** for
`your-app.onrender.com` and any custom domain you add (Settings → Custom
Domain). No manual cert step is needed.

1. Confirm the site loads over `https://your-app.onrender.com`.
2. The root `.htaccess` redirects HTTP → HTTPS:

   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

   Verify `http://…` 301s to `https://…`.
3. **Manifest.json fix (already in the repo).** The `.htaccess` deny rule for
   `\.json$` would block `/manifest.json`, which breaks PWA install. The allow
   rule is placed **before** the deny block:

   ```apache
   # Allow the PWA manifest (the generic .json deny rule below would otherwise block it)
   <Files "manifest.json">
     Require all granted
   </Files>

   <FilesMatch "\.(env|sql|log|json|md|gitignore|lock)$">
     Require all denied
   </FilesMatch>
   ```

---

## 7. Cron — Render Cron Job (Render has no cron on Web Services)

Render **does not support cron scheduling on Web Services**. Create a separate
**Cron Job** service from the same repo:

1. Render dashboard → **New + → Cron Job** → pick the same repo.
2. Service type: **Cron Job**, Root Directory: repo root.
3. **Command**: `php backend/cron/run-scheduling.php`
4. **Schedule**: `0 22 * * *`
5. Add the **same environment variables** as the Web Service (Step 5).

The script is CLI-only (it refuses to run under a web request) and appends one
line per run to `backend/logs/cron.log`. Test it manually first:

```bash
php backend/cron/run-scheduling.php
```

---

## 8. VAPID Keys (Web Push)

If you already generated keys (they're in the Render env vars and
`assets/js/config.js`), skip to Step 9. Otherwise generate a fresh pair once:

```bash
cd backend
php -r "
require 'vendor/autoload.php';
use Minishlink\WebPush\VAPID;
\$keys = VAPID::createVapidKeys();
echo 'VAPID_PUBLIC_KEY=' . \$keys['publicKey'] . PHP_EOL;
echo 'VAPID_PRIVATE_KEY=' . \$keys['privateKey'] . PHP_EOL;
"
```

Copy the output into **both** places:

1. Render Web Service + Cron Job env vars → `VAPID_PUBLIC_KEY` and `VAPID_PRIVATE_KEY`
2. `assets/js/config.js` → `VAPID_PUBLIC_KEY` export

> Keep `VAPID_PRIVATE_KEY` secret — it is only used server-side by
> `backend/services/PushService.php`.
>
> If keygen fails with `configuration file routines::no such file`, PHP can't
> find an `openssl.cnf`; set `OPENSSL_CONF` to a valid one (e.g.
> `export OPENSSL_CONF=/etc/ssl/openssl.cnf`) and retry.

---

## 9. Job Photos — authenticated serving (pending work)

`backend/uploads/job-photos/` lives **behind a deny-all `.htaccess`**, and the
database stores only the bare filename — there is currently **no route that
serves photos back**. Before job photos are viewable in production you must add
an authenticated serving endpoint, e.g. **`api/jobs/photo.php`**:

- Guarded by `role-guard.php` (admins only, or extend to the owning client).
- Reads the filename from the query string, resolves it inside
  `backend/uploads/job-photos/`, rejects any `..` / absolute traversal, and
  `readfile()`s it with the correct `Content-Type` (jpeg/png/webp) and
  `Content-Disposition: inline`.

Until that endpoint exists, photos upload successfully but cannot be displayed.

---

## 10. Post-Deploy Smoke Test Checklist

Work through this end-to-end on the live site (use a phone + desktop browser):

- [ ] **Register a client account** (`/register.php`)
- [ ] **Book a service — all 4 steps** (book-service → select-dates →
      booking-confirm → booking-status)
- [ ] **Log in as admin**, run the scheduler manually (Schedule page → Run now,
      or `php backend/cron/run-scheduling.php`)
- [ ] **Approve + dispatch a schedule** (admin Schedule page)
- [ ] **Client receives a push notification** ("Team On The Way") on the phone
      that subscribed — after opting in via the banner
- [ ] **Admin marks the job complete** → invoice is generated automatically
- [ ] **Client views the invoice** (client booking-status / invoices)
- [ ] **PWA install prompt appears** on mobile Chrome (Add to Home Screen /
      Install App)

If the push notification doesn't arrive, check in order: HTTPS is on,
`manifest.json` is served (not 403), the client actually tapped "Allow" on the
banner, and `VAPID_PUBLIC_KEY` matches between the Render env vars and
`assets/js/config.js`.
