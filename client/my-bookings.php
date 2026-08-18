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
  <title>My Bookings — Glorikar Engineering</title>
  <link rel="stylesheet" href="../assets/css/theme.css">
  <link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/layout.css">
</head>
<body>
<div class="app-shell">
  <nav class="sidebar" id="sidebar"></nav>
  <main class="main-content">

    <header class="page-header">
      <div class="page-title">My Bookings</div>
      <a href="book-service.php" class="btn btn-primary btn-sm">+ Book</a>
    </header>

    <!-- Filter tabs -->
    <div style="display:flex;gap:var(--sp-sm);padding:var(--sp-sm) var(--sp-md);border-bottom:1px solid var(--border);overflow-x:auto;scrollbar-width:none;">
      <button class="btn btn-primary btn-sm filter-btn" data-filter="all">All</button>
      <button class="btn btn-ghost btn-sm filter-btn" data-filter="pending">Pending</button>
      <button class="btn btn-ghost btn-sm filter-btn" data-filter="scheduled">Scheduled</button>
      <button class="btn btn-ghost btn-sm filter-btn" data-filter="en_route,in_progress">Active</button>
      <button class="btn btn-ghost btn-sm filter-btn" data-filter="completed">Completed</button>
    </div>

    <div class="page" style="padding-top:var(--sp-md)">
      <div id="bookings-container">
        <!-- Skeleton -->
        <div class="card" style="padding:0;overflow:hidden" id="skeleton">
          <div class="p-md stack stack-md">
            <div class="stack stack-sm">
              <div class="skeleton skeleton-title" style="width:55%"></div>
              <div class="skeleton skeleton-text" style="width:40%"></div>
              <div class="skeleton skeleton-text" style="width:30%"></div>
            </div>
            <hr class="divider">
            <div class="stack stack-sm">
              <div class="skeleton skeleton-title" style="width:60%"></div>
              <div class="skeleton skeleton-text" style="width:45%"></div>
              <div class="skeleton skeleton-text" style="width:35%"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </main>
  <nav class="bottom-nav" id="bottom-nav"></nav>
</div>

<script type="module">
import { requireAuth } from '../assets/js/auth.js';
import { renderNav } from '../assets/js/nav.js';
import { get } from '../assets/js/api.js';

await requireAuth();
renderNav();

let allBookings = [];

const statusClass = {
  pending:     'pending',
  scheduled:   'scheduled',
  en_route:    'en-route',
  in_progress: 'in-progress',
  completed:   'completed',
  cancelled:   'cancelled',
};

const statusLabel = {
  pending:     'Pending',
  scheduled:   'Scheduled',
  en_route:    'En Route',
  in_progress: 'In Progress',
  completed:   'Completed',
  cancelled:   'Cancelled',
};

function fmtDate(d) {
  return new Date(d + 'T00:00:00').toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
}

function renderBookings(list) {
  const container = document.getElementById('bookings-container');
  if (!list.length) {
    container.innerHTML = `
      <div class="empty-state mt-lg">
        <div class="empty-state-icon">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="empty-state-title">No bookings found</div>
        <div class="empty-state-body">No bookings match this filter.</div>
        <a href="book-service.php" class="btn btn-primary btn-sm mt-sm">Book a service</a>
      </div>`;
    return;
  }
  container.innerHTML = `
    <div class="card" style="padding:0;overflow:hidden">
      ${list.map(b => `
        <a href="booking-status.php?id=${b.id}" class="list-row">
          <div class="list-row-body">
            <div class="list-row-title">${b.services?.join(', ') || 'Service booking'}</div>
            <div class="list-row-sub">${b.address}</div>
            <div class="list-row-sub mt-xs">${fmtDate(b.preferred_date_from)}${b.preferred_date_to !== b.preferred_date_from ? ' – ' + fmtDate(b.preferred_date_to) : ''}</div>
          </div>
          <div class="list-row-meta">
            <span class="badge badge-${statusClass[b.status] || 'pending'}">${statusLabel[b.status] || b.status}</span>
          </div>
        </a>`).join('')}
    </div>`;
}

async function loadBookings() {
  try {
    const data = await get('/api/bookings/mine.php');
    allBookings = data.bookings || [];
    document.getElementById('skeleton')?.remove();
    renderBookings(allBookings);
  } catch (err) {
    document.getElementById('bookings-container').innerHTML = `
      <div class="error-state mt-lg">
        <div class="error-state-title">Could not load bookings</div>
        <div class="error-state-body">${err.message}</div>
        <button class="btn btn-ghost btn-sm mt-sm" onclick="location.reload()">Retry</button>
      </div>`;
  }
}

// Filter tabs
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-btn').forEach(b => {
      b.classList.remove('btn-primary');
      b.classList.add('btn-ghost');
    });
    btn.classList.add('btn-primary');
    btn.classList.remove('btn-ghost');

    const f = btn.dataset.filter;
    if (f === 'all') {
      renderBookings(allBookings);
    } else {
      const statuses = f.split(',');
      renderBookings(allBookings.filter(b => statuses.includes(b.status)));
    }
  });
});

loadBookings();
</script>
</body>
</html>
