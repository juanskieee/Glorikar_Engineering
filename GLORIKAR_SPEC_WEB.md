# Glorikar Engineering — Aircon Services Web App
## Project Specification (HTML / CSS / JS / PHP / MySQL)

Converted from the original React Native + Supabase spec. Same product, same design system, same scheduling logic — rebuilt as a responsive web app (mobile-first, works great on phones via the browser, installable as a PWA).

---

## Overview

A single responsive web app for **Glorikar Engineering**, an aircon services company. One app, two roles: **Client** and **Admin (Boss)**. Role is determined at login — each role sees a completely different navigation/menu.

---

## Tech Stack

| Layer | Technology | Notes |
|---|---|---|
| Frontend | HTML5 + CSS3 + Vanilla JS (or lightweight framework-free SPA) | Mobile-first responsive layout, PWA-installable |
| Backend | PHP 8+ | Plain PHP or a micro-framework (Slim) exposing a REST-style API |
| Database | MySQL 8 | Hosted on any shared/VPS host (e.g. Hostinger, DigitalOcean) |
| Auth | Custom PHP sessions/JWT + `password_hash()` | Email + password login |
| Maps | Mapbox GL JS (same free tier, 50k loads/month) | Loaded via `<script>` tag |
| Push notifications | Web Push API (VAPID) or Firebase Cloud Messaging (Web) | Browser push instead of Expo Push |
| Scheduling logic | PHP service classes | Trip scoring + clustering, same algorithm |
| File uploads | PHP file handling → stored on server or S3-compatible bucket | Before/after job photos |

---

## UI Design System

Identical visual identity to the original spec — just implemented as CSS instead of React Native StyleSheet. Do not use Bootstrap/Materialize defaults — build from these tokens as CSS custom properties.

### Design Direction — Modern & Professional

This must look like a **premium SaaS/field-service product** (think Linear, Stripe Dashboard, Uber for Business) — not a template or admin-panel freebie. Concretely:

- **No default browser chrome anywhere** — custom-styled inputs, selects, checkboxes, date pickers, scrollbars.
- **Generous whitespace**, consistent 8pt spacing rhythm, clear visual hierarchy (one dominant action per screen).
- **Subtle motion only** — 150–250ms ease-out transitions on hover/press/state changes (`transform`, `opacity`, `background-color`). No bouncy or decorative animation.
- **Flat design, zero drop shadows/gradients** per the original spec — depth comes from `border` + surface color steps (`--bg` → `--surface` → `--surface-raised`), not shadows.
- Data-dense admin screens (dashboard, route map, schedule detail) use **tables/cards with clear alignment and tabular numerals**, not cramped text.
- Buttons, inputs, and cards share **one consistent corner radius scale** (`--r-sm/md/lg`) — never mix arbitrary radii.
- Icons are outline-style, consistent stroke width, from a single icon set (Lucide/Ionicons) — never mix icon families.
- Every interactive element has a visible focus state (`outline` or `border-color` change) for accessibility — not just `:hover`.
- Status colors (badges, dots) are the **only** saturated color accents against the neutral navy/slate palette — this keeps the UI feeling calm and professional rather than busy.

### Explicitly Avoid — "AI Slop" Design Patterns

Do not produce a generic AI-generated-looking interface. These patterns are banned outright:

- **No purple-to-blue (or any) gradients** — anywhere: backgrounds, buttons, text, icons, hero sections. All colors are flat, solid values from `:root`.
- No glassmorphism / frosted-glass panels (`backdrop-filter: blur(...)`), no glowing neon borders, no soft drop-shadow "floating card" look.
- No generic rounded-blob background shapes, abstract mesh gradients, or decorative squiggles behind content.
- No stock "AI startup" hero clichés: giant centered gradient headline, glowing orb illustrations, floating 3D shapes, sparkle/star icons next to feature text.
- No overuse of `border-radius` beyond the defined scale — never fully-pill-shaped cards or oversized 24px+ radii on containers.
- No default emoji as icons (✨🚀💡) in UI copy or buttons — use the outline icon set only.
- No rainbow/multi-color icon sets or mismatched icon styles (filled mixed with outline).
- No excessive font-weight 800/900 "bold everything" headlines — stick to the defined type scale's weights (400/500/600/700 only).
- No auto-generated placeholder Lorem Ipsum in the delivered product — use real Glorikar copy (service names, statuses, actual field-service language).

The visual bar is a **flat, high-contrast, editorial dashboard aesthetic** (Linear/Stripe/Vercel-adjacent) — solid navy/slate surfaces, one accent color (`--accent`) used sparingly and intentionally, sharp typography, and restraint. If a design choice looks like it came from a generic "AI website builder" template, it's wrong — simplify it back to the token system.

### CSS Architecture Requirement — Root Variables

**All design tokens (colors, spacing, radii, typography sizes, shadows/borders, transition timings) must be declared once as CSS custom properties inside a single `:root { }` block in `assets/css/theme.css`.** This is not optional:

- `theme.css` is loaded first, before `components.css` and `layout.css`, and defines the `:root` variable set shown below — the single source of truth for the whole app.
- No page, component, or JS file may hardcode a hex color, pixel spacing value, font size, or border-radius. Every value must reference a `var(--token-name)`.
- This makes rebranding, dark/light theming, and design QA a one-file change instead of a find-and-replace across dozens of pages.
- If a new value is needed (a new status color, a new spacing step), it gets **added to `:root` in `theme.css` first**, then referenced elsewhere — never declared inline as a one-off.

### Design Tokens — `assets/css/theme.css`

```css
:root {
  /* Backgrounds */
  --bg: #0F172A;
  --surface: #1E293B;
  --surface-raised: #334155;

  /* Brand */
  --accent: #0EA5E9;
  --accent-dim: #0369A1;

  /* Status */
  --status-pending: #F59E0B;
  --status-scheduled: #6366F1;
  --status-en-route: #0EA5E9;
  --status-in-progress: #10B981;
  --status-completed: #22C55E;
  --status-cancelled: #EF4444;

  /* Text */
  --text-primary: #F1F5F9;
  --text-secondary: #94A3B8;
  --text-disabled: #475569;

  /* Borders */
  --border: #1E293B;
  --border-focus: #0EA5E9;

  /* Utility */
  --white: #FFFFFF;
  --black: #000000;
  --overlay: rgba(0,0,0,0.6);

  /* Spacing (8pt grid) */
  --sp-xs: 4px;  --sp-sm: 8px;  --sp-md: 16px;
  --sp-lg: 24px; --sp-xl: 32px; --sp-xxl: 48px;

  /* Radius */
  --r-sm: 6px; --r-md: 12px; --r-lg: 16px; --r-full: 999px;

  /* Motion */
  --transition-fast: 150ms ease-out;
  --transition-base: 200ms ease-out;
}

body {
  background: var(--bg);
  color: var(--text-primary);
  font-family: 'Inter', sans-serif;
}
```

### Typography

Load **Inter** via Google Fonts `<link>` or self-hosted `@font-face`.

```css
/* Utility classes mirroring the RN typography scale */
.display-lg { font: 700 28px/34px Inter; letter-spacing: -0.5px; }
.display-sm { font: 700 22px/28px Inter; letter-spacing: -0.3px; }
.heading-lg { font: 600 18px/24px Inter; }
.heading-sm { font: 600 15px/20px Inter; }
.body-lg    { font: 400 15px/22px Inter; }
.body-sm    { font: 400 13px/18px Inter; }
.label-lg   { font: 500 14px/20px Inter; letter-spacing: 0.1px; }
.label-sm   { font: 500 12px/16px Inter; letter-spacing: 0.2px; }
.caption    { font: 400 11px/14px Inter; letter-spacing: 0.3px; }
```

### Components (CSS classes, same rules as RN spec)

- `.btn-primary` — `background: var(--accent)`, `border-radius: var(--r-md)`, hover/active → `var(--accent-dim)` + `transform: scale(0.98)`; disabled → `var(--surface-raised)` bg, `var(--text-disabled)` text
- `.btn-ghost` — transparent bg, `1px solid var(--border)`, `var(--text-secondary)` text
- `.input` — `background: var(--surface-raised)`, `border-radius: var(--r-sm)`, `1px solid var(--border)`, focus → `border-color: var(--border-focus)`
- `.card` — `background: var(--surface)`, `border-radius: var(--r-md)`, `1px solid var(--border)`, padding `var(--sp-md)`, no box-shadow
- `.status-badge` — pill (`border-radius: var(--r-full)`), background = status color at 15% opacity, text = status color full opacity
- `.section-header` — uppercase, `letter-spacing: 1.2px`, `var(--text-secondary)`
- `.divider` — `height: 1px; background: var(--border);`

### Bottom Navigation (mobile) / Sidebar (desktop)

Fixed bottom nav bar on mobile viewports (`max-width: 768px`), collapses into a left sidebar on desktop — same tokens (`background: var(--surface)`, active tint `var(--accent)`, inactive `var(--text-disabled)`).

- **Client tabs:** Home, My Bookings, Notifications, Profile
- **Admin tabs:** Dashboard, Route Map, Teams, Notifications

Use an icon font/SVG set (e.g. Lucide or Ionicons via SVG sprite) — outline for inactive, filled for active.

### Screen → Page Mapping

Every original "Screen" becomes an HTML page (or SPA view). Same layout rule: full-height dark background, scrollable content area, optional sticky bottom action bar.

| Original Screen | Web Page |
|---|---|
| LoginScreen | `login.html` |
| Client HomeScreen | `client/home.html` |
| Client BookServiceScreen | `client/book-service.html` |
| Client SelectDatesScreen | `client/select-dates.html` |
| Client BookingConfirmScreen | `client/booking-confirm.html` |
| Client MyBookingsScreen | `client/my-bookings.html` |
| Client BookingStatusScreen | `client/booking-status.html` |
| ProfileScreen / NotificationsScreen | `shared/profile.html`, `shared/notifications.html` |
| Admin DashboardScreen | `admin/dashboard.html` |
| Admin RouteMapScreen | `admin/route-map.html` |
| Admin ScheduleDetailScreen | `admin/schedule-detail.html` |
| Admin TeamManageScreen | `admin/teams.html` |
| Admin AssignTeamScreen | `admin/assign-team.html` |
| Admin DispatchScreen | `admin/dispatch.html` |
| Admin JobCompleteScreen | `admin/job-complete.html` |
| Admin InvoiceScreen | `admin/invoice.html` |

Loading states: CSS shimmer skeleton (`@keyframes shimmer` gradient sweep) instead of `expo-linear-gradient`. Empty/error states: same copy and icon rules as original spec.

---

## Role-Based Routing Logic

Since this is now a server-rendered/PHP app, role gating happens both client-side (hide nav items) and server-side (every protected page/API checks session role):

```php
// includes/auth-guard.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}
$role = $_SESSION['role']; // 'client' | 'admin'
```

```php
// includes/role-guard.php (include on admin/* pages and admin API endpoints)
require 'auth-guard.php';
if ($role !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}
```

---

## Smart Scheduling Engine Logic (unchanged algorithm, PHP implementation)

Located in `backend/services/SchedulingEngine.php`

### Step 1 — Area clustering
Group all `pending` bookings whose `preferred_date_from` falls within an upcoming window into geographic clusters using a simple radius check (e.g. 5km), calling the Mapbox Distance Matrix API via `cURL`/Guzzle from PHP.

### Step 2 — Trip score calculation (`TripScorer.php`)
```
trip_score = (total_services_count × 10) - (distance_from_depot_km × 2)
```
- A far client with 1 service may score 8 → attach to a nearby route, do not anchor alone
- A far client with 4 services scores 20 → can anchor their own route
- Bookings below a minimum threshold (configurable, default: 10) are deferred until a nearby route exists

### Step 3 — Route optimization (`RouteOptimizer.php`)
Nearest-neighbor algorithm starting from the depot, same as original.

### Step 4 — Team assignment
Same matching rules: `is_available = true`, duration fits an 8-hour day, service compatibility.

### Step 5 — Admin approval
Creates a `schedules` row with status `draft`. Admin reviews on Route Map page, can reorder/reassign, then approves → `approved`.

### Step 6 — Dispatch
Admin clicks Dispatch. Status → `dispatched`. All clients on that schedule receive a **web push notification** (or SMS/email fallback) with their ETA slot.

Trigger: manual button (calls `POST /api/schedule/run.php`) **and** a nightly **cron job** (`crontab -e`, `0 22 * * * php /path/to/backend/cron/run-scheduling.php`) at 10pm — replacing the Node cron.

---

## Booking Status Flow (unchanged)

```
pending → scheduled → en_route → in_progress → completed
                                                    ↓
                                                invoice generated
```

Push notification sent on each status change (Web Push instead of Expo Push).

---

## API Endpoints (PHP files, same routes/behavior)

### Auth
```
POST   /api/auth/register.php     Create client account
POST   /api/auth/login.php        Login (starts PHP session / issues JWT)
GET    /api/auth/me.php           Get current user + role
```

### Bookings (client)
```
POST   /api/bookings/create.php
GET    /api/bookings/mine.php
GET    /api/bookings/get.php?id=
```

### Bookings (admin)
```
GET    /api/bookings/pending.php
GET    /api/bookings/all.php
PATCH  /api/bookings/update-status.php
```

### Schedule
```
POST   /api/schedule/run.php
GET    /api/schedule/drafts.php
GET    /api/schedule/get.php?id=
PATCH  /api/schedule/approve.php
PATCH  /api/schedule/dispatch.php
```

### Teams
```
GET    /api/teams/list.php
POST   /api/teams/create.php
PATCH  /api/teams/update.php
POST   /api/teams/add-member.php
```

### Jobs
```
POST   /api/jobs/complete.php
POST   /api/jobs/upload-photos.php
POST   /api/jobs/generate-invoice.php
GET    /api/jobs/get-invoice.php
```

Every PHP endpoint returns JSON (`header('Content-Type: application/json')`) and is called from the frontend via `fetch()`.

---

## Environment Variables

### Backend (`backend/.env` — loaded via `vlucas/phpdotenv`)
```
DB_HOST=
DB_NAME=glorikar
DB_USER=
DB_PASS=
JWT_SECRET=
MAPBOX_ACCESS_TOKEN=
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
DEPOT_LAT=
DEPOT_LNG=
MIN_TRIP_SCORE=10
MAX_DAILY_HOURS=8
```

### Frontend (`frontend/js/config.js`)
```js
export const API_URL = 'https://api.glorikar.com';
export const MAPBOX_TOKEN = '...';
export const VAPID_PUBLIC_KEY = '...';
```

---

## Key Dependencies

### Frontend
- No build step required (plain JS modules) — or optionally Vite for bundling
- Mapbox GL JS (`<script src="https://api.mapbox.com/mapbox-gl-js/...">`)
- SortableJS (drag-and-drop reordering, replaces `react-native-draggable-flatlist`)
- Google Fonts: Inter

### Backend (`backend/composer.json`)
```json
{
  "require": {
    "vlucas/phpdotenv": "^5.0",
    "firebase/php-jwt": "^6.0",
    "minishlink/web-push": "^8.0",
    "guzzlehttp/guzzle": "^7.0"
  }
}
```

---

## Project Structure

```
glorikar/
├── frontend/
│   ├── index.html
│   ├── login.html
│   ├── assets/
│   │   ├── css/
│   │   │   ├── theme.css
│   │   │   ├── components.css
│   │   │   └── layout.css
│   │   ├── js/
│   │   │   ├── config.js
│   │   │   ├── api.js              # fetch wrapper
│   │   │   ├── auth.js
│   │   │   ├── nav.js               # role-based nav render
│   │   │   └── sw.js                # service worker (PWA + push)
│   │   └── icons/
│   ├── client/
│   │   ├── home.html
│   │   ├── book-service.html
│   │   ├── select-dates.html
│   │   ├── booking-confirm.html
│   │   ├── my-bookings.html
│   │   └── booking-status.html
│   ├── admin/
│   │   ├── dashboard.html
│   │   ├── route-map.html
│   │   ├── schedule-detail.html
│   │   ├── teams.html
│   │   ├── assign-team.html
│   │   ├── dispatch.html
│   │   ├── job-complete.html
│   │   └── invoice.html
│   └── shared/
│       ├── profile.html
│       └── notifications.html
│
└── backend/
    ├── api/
    │   ├── auth/ (register.php, login.php, me.php)
    │   ├── bookings/ (create.php, mine.php, get.php, pending.php, all.php, update-status.php)
    │   ├── schedule/ (run.php, drafts.php, get.php, approve.php, dispatch.php)
    │   ├── teams/ (list.php, create.php, update.php, add-member.php)
    │   └── jobs/ (complete.php, upload-photos.php, generate-invoice.php, get-invoice.php)
    ├── includes/
    │   ├── auth-guard.php
    │   ├── role-guard.php
    │   └── db.php                   # PDO MySQL connection
    ├── services/
    │   ├── SchedulingEngine.php
    │   ├── TripScorer.php
    │   ├── ClusterService.php
    │   ├── RouteOptimizer.php
    │   └── MapboxService.php
    ├── cron/
    │   └── run-scheduling.php
    ├── uploads/                     # job photos
    ├── vendor/                      # composer packages
    ├── composer.json
    └── .env
```

---

## Database Schema (MySQL 8)

```sql
CREATE TABLE users (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  email         VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name     VARCHAR(255) NOT NULL,
  phone         VARCHAR(50),
  address       TEXT NOT NULL,
  latitude      DOUBLE,
  longitude     DOUBLE,
  role          ENUM('client','admin') NOT NULL DEFAULT 'client',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Role is set to 'admin' manually in the DB for the boss account.

CREATE TABLE services (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL, -- cleaning | installing | relocation | repair | inspection
  duration_hrs  FLOAT NOT NULL,
  base_price    DECIMAL(10,2) NOT NULL
);

CREATE TABLE bookings (
  id                  CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  client_id           CHAR(36) REFERENCES users(id),
  status              ENUM('pending','scheduled','en_route','in_progress','completed','cancelled') DEFAULT 'pending',
  preferred_date_from DATE NOT NULL,
  preferred_date_to   DATE NOT NULL,
  address             TEXT NOT NULL,
  latitude            DOUBLE NOT NULL,
  longitude           DOUBLE NOT NULL,
  notes               TEXT,
  trip_score          FLOAT,
  schedule_id         CHAR(36),
  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE booking_services (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  booking_id    CHAR(36) REFERENCES bookings(id),
  service_id    INT REFERENCES services(id),
  quantity      INT DEFAULT 1
);

CREATE TABLE teams (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  name          VARCHAR(100) NOT NULL,
  vehicle       VARCHAR(100),
  is_available  BOOLEAN DEFAULT TRUE,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE team_members (
  id          CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  team_id     CHAR(36) REFERENCES teams(id),
  user_id     CHAR(36) REFERENCES users(id),
  role_tag    ENUM('lead','technician')
);

CREATE TABLE schedules (
  id                CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  scheduled_date    DATE NOT NULL,
  team_id           CHAR(36) REFERENCES teams(id),
  status            ENUM('draft','approved','dispatched','done') DEFAULT 'draft',
  total_distance_km FLOAT,
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE schedule_stops (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  schedule_id   CHAR(36) REFERENCES schedules(id),
  booking_id    CHAR(36) REFERENCES bookings(id),
  stop_order    INT NOT NULL,
  eta           TIME
);

CREATE TABLE invoices (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  booking_id    CHAR(36) REFERENCES bookings(id),
  total_amount  DECIMAL(10,2) NOT NULL,
  issued_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  paid          BOOLEAN DEFAULT FALSE,
  notes         TEXT
);

CREATE TABLE job_photos (
  id            CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  booking_id    CHAR(36) REFERENCES bookings(id),
  photo_url     TEXT NOT NULL,
  type          ENUM('before','after'),
  uploaded_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

> MySQL doesn't have native row-level security like Supabase. Enforce "clients can only read their own bookings" in every PHP query with `WHERE client_id = :session_user_id`, checked server-side on every request — never trust a client-supplied `client_id`.

---

## Setup Instructions

### Build Order

**Phase 1 — Foundation**
1. Create `frontend/` and `backend/` folders
2. Build `theme.css`, `components.css`, `layout.css` with all design tokens
3. Load Inter via Google Fonts `<link>` in a shared `<head>` partial
4. Build shared components as reusable HTML partials + CSS classes: button, card, badge, section header, divider, empty state, skeleton

**Phase 2 — Database & Auth**
5. Create MySQL database, run the schema SQL above
6. Build `backend/includes/db.php` (PDO connection)
7. Build `api/auth/register.php`, `login.php` (bcrypt via `password_hash`/`password_verify`, PHP session or JWT), `me.php`
8. Build `login.html` + `frontend/js/auth.js`
9. Build role-based nav rendering (`nav.js`) — confirm role switching works before any other page

**Phase 3 — Backend API**
10. `composer init`, install dependencies
11. Build `auth-guard.php` / `role-guard.php` middleware includes
12. Implement routes in order: auth → bookings → teams → schedule → jobs
13. Build scheduling engine classes in isolation with seed data before wiring to endpoints
14. Set up cron job for nightly scheduling (10pm)
15. Deploy backend to a PHP host (shared hosting, VPS, or Render/Railway PHP runtime), point frontend `API_URL` at it

**Phase 4 — Client Pages**
16. `client/home.html` → `book-service.html` → `select-dates.html` → `booking-confirm.html`
17. `client/my-bookings.html` → `booking-status.html`
18. `shared/notifications.html` → `shared/profile.html`

**Phase 5 — Admin Pages**
19. `admin/dashboard.html` → `route-map.html` → `schedule-detail.html`
20. `admin/teams.html` → `assign-team.html` → `dispatch.html`
21. `admin/job-complete.html` → `invoice.html`

**Phase 6 — Polish**
22. Add CSS shimmer skeletons to all list pages
23. Add empty states to all list pages
24. Add error states with retry to all data-fetching pages
25. Wire up Web Push end to end (VAPID keys, service worker, subscription storage)
26. Test full flow: client books → admin schedules → dispatch → client gets push → job complete → invoice

**Phase 7 — Deploy & PWA**
27. Add `manifest.json` + `sw.js` so the site is installable on Android/iOS home screens
28. Deploy frontend as static files (any host/CDN) + backend PHP/MySQL host
29. Point a custom domain, enable HTTPS (required for Web Push and service workers)

---

## Notes

- All admin-only API endpoints must `require 'role-guard.php';` at the top
- The scheduling engine is triggerable manually (button in admin dashboard, calls `run.php`) **and** automatically via cron (10pm nightly)
- Mapbox Distance Matrix has a limit of 25×25 per request — batch in PHP if pending bookings exceed 25
- Geocode every booking address via Mapbox Geocoding API at creation time (server-side, in `create.php`) and store `latitude`/`longitude`
- Enforce data access rules manually in PHP (no built-in RLS like Supabase) — always scope queries to the logged-in user's session
- Seed the `services` table with the 5 service types on first deploy (`backend/db/seed.sql`)
- **Never hardcode colors in HTML/JS** — always reference CSS variables from `theme.css`
- **Never use pixel values outside the spacing scale** — use the `--sp-*` variables only
- All text must use one of the typography utility classes — no inline `font-size`/`font-weight`
- Escape/sanitize all user input server-side (PDO prepared statements everywhere) and encode output (`htmlspecialchars`) to prevent SQL injection and XSS

---

## Safety & Security Instructions

These rules are mandatory, not optional polish. Apply them from Phase 1 — retrofitting security later is how breaches happen.

### Authentication & Session Security
- Hash passwords with `password_hash($pw, PASSWORD_BCRYPT)` (or `PASSWORD_ARGON2ID` if available). Never store or log plaintext passwords.
- Verify with `password_verify()`. Never compare hashes with `==`/`===`.
- Regenerate the session ID on login (`session_regenerate_id(true)`) to prevent session fixation.
- Set cookies with `Secure`, `HttpOnly`, and `SameSite=Strict` (or `Lax` if cross-site redirects are needed).
- Enforce a session timeout (e.g. 30 min idle) and re-check `$_SESSION['role']` server-side on every protected request — never trust a role sent from the client.
- If using JWTs instead of sessions: sign with a strong secret (`JWT_SECRET`, 256-bit+, stored only in `.env`), set short expiry (15–60 min) plus refresh tokens, and verify signature + expiry on every request.
- Rate-limit login and registration endpoints (e.g. 5 attempts per IP per 15 minutes) to block brute-force and credential-stuffing attempts.
- Never reveal whether an email exists on failed login ("Invalid email or password", not "No account found").

### Input Validation & Injection Prevention
- Use **PDO prepared statements with bound parameters** for every single query — no string concatenation into SQL, ever.
- Validate and sanitize all input server-side (type, length, format) even if it's already validated in JS — client-side validation is a UX convenience, not a security control.
- Encode all output with `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')` before rendering user-supplied content to prevent stored/reflected XSS.
- Validate file uploads (job photos) strictly: check MIME type and file signature (not just extension), enforce a max file size, rename files to random UUIDs on save, and store outside the web root or in a directory with PHP execution disabled (`.htaccess` deny for `uploads/`).
- Set a Content-Security-Policy header restricting script sources to your own domain + Mapbox's CDN.

### Authorization (Access Control)
- Every admin API endpoint must call `role-guard.php` and reject non-admin sessions with `403`.
- Every client-scoped query (bookings, invoices, photos) must filter by the **server-side session's** `user_id` — never accept a `client_id` from the request body/query string and trust it.
- Apply the same check for object-level access: a client requesting `booking-status.html?id=X` must be blocked (404, not 403, to avoid leaking existence) if `X` doesn't belong to them.

### Transport & Infrastructure
- Enforce HTTPS everywhere (required for Web Push and service workers anyway) — redirect all HTTP to HTTPS, use HSTS.
- Keep `.env` out of the web root and out of version control (`.gitignore`); never expose `DB_PASS`, `JWT_SECRET`, `MAPBOX_ACCESS_TOKEN`, or `VAPID_PRIVATE_KEY` to the frontend.
- Run MySQL with a dedicated least-privilege app user (not `root`) that only has grants on the `glorikar` database.
- Keep PHP, MySQL, and Composer dependencies patched; subscribe to security advisories or run `composer audit` periodically.
- Disable PHP error display in production (`display_errors = Off`) and log errors to a file instead, so stack traces never leak to users.

### CSRF Protection
- Generate a CSRF token per session, embed it in every state-changing form/AJAX request, and verify it server-side before processing POST/PATCH/DELETE requests.

### API Hardening
- Add CORS restrictions so `/api/*` only accepts requests from your known frontend origin(s), not `*`.
- Return generic error messages to clients (no SQL errors, file paths, or stack traces); log details server-side only.
- Apply rate limiting to public-facing endpoints (registration, booking creation) to deter abuse and scraping.

### Data Privacy
- Store only the location data needed (booking address lat/lng) — don't track client devices beyond what's needed for the ETA feature.
- Give clients a way to see/download/delete their own data on request (basic data-subject rights).
- Redact or mask sensitive fields (phone, address) in admin logs/exports where not operationally necessary.

### Operational Safety
- Take regular automated MySQL backups (daily) with tested restore procedures.
- Log authentication events (login success/failure, role changes) for audit purposes, without logging passwords or tokens.
- Before going live, run the OWASP Top 10 checklist against the app (injection, broken auth, XSS, broken access control, security misconfiguration, etc.) and fix findings before Phase 7 deploy.
