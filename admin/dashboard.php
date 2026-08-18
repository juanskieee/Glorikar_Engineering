<?php
require __DIR__ . '/../backend/includes/role-guard.php';
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>"/>
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#0EA5E9">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>Dashboard — Glorikar Engineering</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/css/theme.css"/>
  <link rel="stylesheet" href="../assets/css/components.css"/>
  <link rel="stylesheet" href="../assets/css/layout.css"/>
</head>
<body>
<div class="app-shell">
  <aside class="sidebar" id="sidebar"></aside>
  <main class="main-content">

    <div class="page">
      <div class="page-header">
        <div class="page-header-left">
          <div class="page-title">Dashboard</div>
          <div class="page-subtitle" id="today-label">Loading…</div>
        </div>
        <div class="row" style="gap:var(--sp-sm)">
          <button class="btn btn-ghost btn-sm" id="run-schedule-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 3l14 9-14 9V3z"/></svg>
            Run Scheduler
          </button>
          <a href="schedule.php" class="btn btn-primary btn-sm">View Schedule</a>
        </div>
      </div>

      <!-- Stat cards -->
      <div class="grid-4 mt-md" id="stats-grid">
        <div class="stat-card skeleton-card" style="height:90px"></div>
        <div class="stat-card skeleton-card" style="height:90px"></div>
        <div class="stat-card skeleton-card" style="height:90px"></div>
        <div class="stat-card skeleton-card" style="height:90px"></div>
      </div>

      <div class="grid-2 mt-lg" style="align-items:start">

        <!-- Today's Jobs -->
        <div>
          <div class="section-header">
            <span class="heading-sm">Today's Jobs</span>
            <a href="jobs.php" class="btn btn-ghost btn-sm">View all</a>
          </div>
          <div id="jobs-list" class="mt-sm">
            <div class="skeleton-card" style="height:64px;border-radius:var(--r-md);margin-bottom:var(--sp-sm)"></div>
            <div class="skeleton-card" style="height:64px;border-radius:var(--r-md);margin-bottom:var(--sp-sm)"></div>
            <div class="skeleton-card" style="height:64px;border-radius:var(--r-md)"></div>
          </div>
        </div>

        <!-- Pending bookings -->
        <div>
          <div class="section-header">
            <span class="heading-sm">Pending Bookings</span>
            <span class="badge badge-pending" id="pending-count">—</span>
          </div>
          <div id="pending-list" class="mt-sm">
            <div class="skeleton-card" style="height:64px;border-radius:var(--r-md);margin-bottom:var(--sp-sm)"></div>
            <div class="skeleton-card" style="height:64px;border-radius:var(--r-md)"></div>
          </div>
        </div>

      </div>

      <!-- Recent activity -->
      <div class="mt-lg">
        <div class="section-header">
          <span class="heading-sm">Recent Activity</span>
        </div>
        <div id="activity-list" class="mt-sm">
          <div class="skeleton-card" style="height:48px;border-radius:var(--r-md);margin-bottom:var(--sp-sm)"></div>
          <div class="skeleton-card" style="height:48px;border-radius:var(--r-md);margin-bottom:var(--sp-sm)"></div>
          <div class="skeleton-card" style="height:48px;border-radius:var(--r-md)"></div>
        </div>
      </div>
    </div>

  </main>
  <nav class="bottom-nav" id="bottom-nav"></nav>
</div>

<!-- Run Scheduler confirm modal -->
<div class="modal-overlay" id="scheduler-modal" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <span class="heading-sm">Run Scheduling Engine</span>
      <button class="btn-icon" id="close-modal-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <p class="body-sm text-secondary">This will assign all pending bookings to available teams based on date, location, and workload. This normally runs automatically at 10 PM.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="cancel-modal-btn">Cancel</button>
      <button class="btn btn-primary" id="confirm-run-btn">Run Now</button>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script type="module">
import { requireAdmin } from '../assets/js/auth.js';
import { renderNav }    from '../assets/js/nav.js';
import { get, post }    from '../assets/js/api.js';

await requireAdmin();
renderNav();

// Today label
const today = new Date();
document.getElementById('today-label').textContent = today.toLocaleDateString('en-PH', {
  weekday: 'long', month: 'long', day: 'numeric', year: 'numeric'
});

const statusClass = {
  pending: 'pending', scheduled: 'scheduled', en_route: 'en-route',
  in_progress: 'in-progress', completed: 'completed', cancelled: 'cancelled',
};
const statusLabel = {
  pending: 'Pending', scheduled: 'Scheduled', en_route: 'En Route',
  in_progress: 'In Progress', completed: 'Completed', cancelled: 'Cancelled',
};

function toast(msg, type = 'info') {
  const c = document.getElementById('toast-container');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.textContent = msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}

function fmtDate(d) {
  return new Date(d + 'T00:00:00').toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });
}

async function loadDashboard() {
  try {
    const data = await get('/api/admin/dashboard.php');
    const { stats, today_jobs, pending_bookings, recent_activity } = data;

    // Stats
    document.getElementById('stats-grid').innerHTML = `
      <div class="stat-card">
        <div class="stat-card-label">Today's Jobs</div>
        <div class="stat-card-value">${stats.today_jobs ?? 0}</div>
        <div class="stat-card-sub">${stats.completed_today ?? 0} completed</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-label">Pending Bookings</div>
        <div class="stat-card-value">${stats.pending ?? 0}</div>
        <div class="stat-card-sub">awaiting scheduling</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-label">Active Teams</div>
        <div class="stat-card-value">${stats.active_teams ?? 0}</div>
        <div class="stat-card-sub">of ${stats.total_teams ?? 0} total</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-label">Revenue Today</div>
        <div class="stat-card-value">₱${Number(stats.revenue_today ?? 0).toLocaleString()}</div>
        <div class="stat-card-sub">${stats.invoices_today ?? 0} invoices</div>
      </div>`;

    // Today's jobs
    const jobsEl = document.getElementById('jobs-list');
    if (!today_jobs?.length) {
      jobsEl.innerHTML = `<div class="empty-state"><div class="empty-state-title">No jobs today</div><div class="empty-state-body">Nothing scheduled for today.</div></div>`;
    } else {
      jobsEl.innerHTML = `<div class="card" style="padding:0;overflow:hidden">
        ${today_jobs.map(j => `
          <a href="job-detail.php?id=${j.id}" class="list-row">
            <div class="list-row-icon">
              <div class="avatar">${(j.team_name || 'T')[0]}</div>
            </div>
            <div class="list-row-body">
              <div class="list-row-title">${j.client_name}</div>
              <div class="list-row-sub">${j.address}</div>
            </div>
            <div class="list-row-meta">
              <span class="badge badge-${statusClass[j.status] || 'pending'}">${statusLabel[j.status] || j.status}</span>
            </div>
          </a>`).join('')}
      </div>`;
    }

    // Pending bookings
    const pendingEl = document.getElementById('pending-list');
    document.getElementById('pending-count').textContent = pending_bookings?.length ?? 0;
    if (!pending_bookings?.length) {
      pendingEl.innerHTML = `<div class="empty-state"><div class="empty-state-title">No pending bookings</div><div class="empty-state-body">All bookings are scheduled.</div></div>`;
    } else {
      pendingEl.innerHTML = `<div class="card" style="padding:0;overflow:hidden">
        ${pending_bookings.map(b => `
          <a href="jobs.php?filter=pending" class="list-row">
            <div class="list-row-body">
              <div class="list-row-title">${b.client_name}</div>
              <div class="list-row-sub">${b.services?.join(', ')} · ${fmtDate(b.preferred_date_from)}</div>
            </div>
            <div class="list-row-meta">
              <span class="badge badge-pending">Pending</span>
            </div>
          </a>`).join('')}
      </div>`;
    }

    // Activity
    const actEl = document.getElementById('activity-list');
    if (!recent_activity?.length) {
      actEl.innerHTML = `<div class="empty-state"><div class="empty-state-title">No recent activity</div></div>`;
    } else {
      actEl.innerHTML = `<div class="card" style="padding:0;overflow:hidden">
        ${recent_activity.map(a => `
          <div class="list-row">
            <div class="list-row-body">
              <div class="list-row-title">${a.description}</div>
              <div class="list-row-sub">${a.time}</div>
            </div>
            ${a.status ? `<div class="list-row-meta"><span class="badge badge-${statusClass[a.status] || 'pending'}">${statusLabel[a.status] || a.status}</span></div>` : ''}
          </div>`).join('')}
      </div>`;
    }

  } catch (err) {
    document.getElementById('stats-grid').innerHTML = `<div class="error-state" style="grid-column:1/-1"><div class="error-state-title">Failed to load dashboard</div><div class="error-state-body">${err.message}</div><button class="btn btn-ghost btn-sm mt-sm" onclick="location.reload()">Retry</button></div>`;
  }
}

// Scheduler modal
const modal    = document.getElementById('scheduler-modal');
const runBtn   = document.getElementById('run-schedule-btn');
const closeBtn = document.getElementById('close-modal-btn');
const cancelBtn= document.getElementById('cancel-modal-btn');
const confirmBtn=document.getElementById('confirm-run-btn');

runBtn.addEventListener('click',   () => modal.style.display = 'flex');
closeBtn.addEventListener('click', () => modal.style.display = 'none');
cancelBtn.addEventListener('click',() => modal.style.display = 'none');
modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });

confirmBtn.addEventListener('click', async () => {
  confirmBtn.disabled = true;
  confirmBtn.textContent = 'Running…';
  try {
    await post('/api/admin/run-scheduler.php', {});
    modal.style.display = 'none';
    toast('Scheduler completed successfully.', 'success');
    loadDashboard();
  } catch (err) {
    toast('Scheduler failed: ' + err.message, 'error');
  } finally {
    confirmBtn.disabled = false;
    confirmBtn.textContent = 'Run Now';
  }
});

loadDashboard();
</script>
</body>
</html>
