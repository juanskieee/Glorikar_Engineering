<?php
require __DIR__ . '/../backend/includes/auth-guard.php';
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#0EA5E9">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>Home — Glorikar Engineering</title>
  <link rel="stylesheet" href="../assets/css/theme.css">
  <link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/layout.css">
</head>
<body>
<div class="app-shell">
  <nav class="sidebar" id="sidebar" aria-label="Sidebar navigation"></nav>

  <main class="main-content">
    <!-- Page header -->
    <header class="page-header">
      <div>
        <div class="page-title" id="greeting">Good morning</div>
        <div class="page-subtitle" id="user-address"></div>
      </div>
      <a href="../shared/notifications.php" class="btn-icon" aria-label="Notifications">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      </a>
    </header>

    <div class="page">

      <!-- Active booking banner -->
      <div id="active-booking" class="hidden mt-md"></div>

      <!-- Book a service CTA -->
      <div class="card mt-md" style="border-color: var(--accent); background: rgba(14,165,233,0.04);">
        <div class="row-between">
          <div>
            <div class="heading-sm">Book a service</div>
            <div class="body-sm text-secondary mt-xs">Cleaning, installation, repair, and more</div>
          </div>
          <a href="book-service.php" class="btn btn-primary btn-sm">Book now</a>
        </div>
      </div>

      <!-- Recent bookings -->
      <div class="row-between mt-lg mb-sm">
        <div class="section-header">Recent bookings</div>
        <a href="my-bookings.php" class="label-sm text-accent">View all</a>
      </div>

      <div id="bookings-list" class="card" style="padding:0;overflow:hidden;">
        <!-- skeleton -->
        <div class="p-md stack stack-sm" id="bookings-skeleton">
          <div class="skeleton skeleton-title" style="width:60%"></div>
          <div class="skeleton skeleton-text" style="width:40%"></div>
          <hr class="divider">
          <div class="skeleton skeleton-title" style="width:55%"></div>
          <div class="skeleton skeleton-text" style="width:35%"></div>
        </div>
      </div>

      <!-- Services overview -->
      <div class="section-header mt-lg mb-sm">Services we offer</div>
      <div class="stack stack-sm" id="services-grid">
        <div class="list-row card" style="padding:var(--sp-sm) var(--sp-md);border-radius:var(--r-md);cursor:default">
          <div class="list-row-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
          </div>
          <div class="list-row-body">
            <div class="list-row-title">Aircon Cleaning</div>
            <div class="list-row-sub">Deep clean, filter wash, drain check</div>
          </div>
          <a href="book-service.php?service=cleaning" class="btn btn-ghost btn-sm">Book</a>
        </div>
        <div class="list-row card" style="padding:var(--sp-sm) var(--sp-md);border-radius:var(--r-md);cursor:default">
          <div class="list-row-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M5.34 18.66l-1.41 1.41M4.93 4.93l1.41 1.41M18.66 18.66l1.41 1.41M2 12h2m16 0h2M12 2v2m0 16v2"/></svg>
          </div>
          <div class="list-row-body">
            <div class="list-row-title">Installation</div>
            <div class="list-row-sub">New unit mounting and wiring</div>
          </div>
          <a href="book-service.php?service=installing" class="btn btn-ghost btn-sm">Book</a>
        </div>
        <div class="list-row card" style="padding:var(--sp-sm) var(--sp-md);border-radius:var(--r-md);cursor:default">
          <div class="list-row-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          </div>
          <div class="list-row-body">
            <div class="list-row-title">Repair</div>
            <div class="list-row-sub">Diagnostics and fault repair</div>
          </div>
          <a href="book-service.php?service=repair" class="btn btn-ghost btn-sm">Book</a>
        </div>
        <div class="list-row card" style="padding:var(--sp-sm) var(--sp-md);border-radius:var(--r-md);cursor:default">
          <div class="list-row-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="5 9 2 12 5 15"/><polyline points="19 9 22 12 19 15"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
          </div>
          <div class="list-row-body">
            <div class="list-row-title">Relocation</div>
            <div class="list-row-sub">Move unit to a new location</div>
          </div>
          <a href="book-service.php?service=relocation" class="btn btn-ghost btn-sm">Book</a>
        </div>
        <div class="list-row card" style="padding:var(--sp-sm) var(--sp-md);border-radius:var(--r-md);cursor:default">
          <div class="list-row-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </div>
          <div class="list-row-body">
            <div class="list-row-title">Inspection</div>
            <div class="list-row-sub">Full system health check</div>
          </div>
          <a href="book-service.php?service=inspection" class="btn btn-ghost btn-sm">Book</a>
        </div>
      </div>

    </div><!-- /page -->
  </main>

  <nav class="bottom-nav" id="bottom-nav" aria-label="Bottom navigation"></nav>
</div>

<script type="module">
import { requireAuth } from '../assets/js/auth.js';
import { renderNav } from '../assets/js/nav.js';
import { get } from '../assets/js/api.js';

const user = await requireAuth();
if (!user) throw new Error('not auth');

renderNav();

// Greeting
const hour = new Date().getHours();
const greet = hour < 12 ? 'Good morning' : hour < 17 ? 'Good afternoon' : 'Good evening';
document.getElementById('greeting').textContent = `${greet}, ${user.full_name.split(' ')[0]}`;
document.getElementById('user-address').textContent = user.address || '';

// Load recent bookings
async function loadBookings() {
  const skeleton = document.getElementById('bookings-skeleton');
  const list     = document.getElementById('bookings-list');
  try {
    const data = await get('/api/bookings/mine.php?limit=3');
    skeleton.remove();

    if (!data.bookings?.length) {
      list.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div class="empty-state-title">No bookings yet</div>
          <div class="empty-state-body">Book your first aircon service to get started.</div>
          <a href="book-service.php" class="btn btn-primary btn-sm">Book now</a>
        </div>`;
      return;
    }

    const statusClass = { pending:'pending', scheduled:'scheduled', en_route:'en-route', in_progress:'in-progress', completed:'completed', cancelled:'cancelled' };
    list.innerHTML = data.bookings.map(b => `
      <a href="booking-status.php?id=${b.id}" class="list-row">
        <div class="list-row-body">
          <div class="list-row-title">${b.services?.join(', ') || 'Service booking'}</div>
          <div class="list-row-sub">${b.address}</div>
        </div>
        <div class="list-row-meta">
          <span class="badge badge-${statusClass[b.status] || 'pending'}">${b.status.replace('_',' ')}</span>
          <span class="caption text-disabled">${new Date(b.preferred_date_from).toLocaleDateString('en-PH',{month:'short',day:'numeric'})}</span>
        </div>
      </a>
    `).join('');
  } catch (err) {
    skeleton.innerHTML = `<div class="error-state"><div class="error-state-title">Could not load bookings</div><div class="error-state-body">${err.message}</div><button class="btn btn-ghost btn-sm" onclick="location.reload()">Retry</button></div>`;
  }
}

loadBookings();

// Check for active/en-route booking
async function checkActive() {
  try {
    const data = await get('/api/bookings/mine.php?status=en_route');
    const active = data.bookings?.[0];
    if (!active) return;
    document.getElementById('active-booking').innerHTML = `
      <div class="card" style="border-color:var(--status-en-route);background:rgba(14,165,233,0.06)">
        <div class="row-between">
          <div>
            <span class="badge badge-en-route">Technician en route</span>
            <div class="heading-sm mt-xs">${active.services?.join(', ') || 'Service'}</div>
            <div class="body-sm text-secondary mt-xs">ETA: ${active.eta || 'Being confirmed'}</div>
          </div>
          <a href="booking-status.php?id=${active.id}" class="btn btn-ghost btn-sm">Track</a>
        </div>
      </div>`;
    document.getElementById('active-booking').classList.remove('hidden');
  } catch { /* no active booking */ }
}
checkActive();
</script>
</body>
</html>
