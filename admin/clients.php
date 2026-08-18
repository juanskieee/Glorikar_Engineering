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
  <title>Clients — Glorikar Engineering</title>
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
          <div class="page-title">Clients</div>
          <div class="page-subtitle">All registered clients</div>
        </div>
      </div>

      <div class="action-bar mt-md">
        <input class="input" id="search-input" placeholder="Search name, email, phone…" style="max-width:320px"/>
      </div>

      <div id="clients-container" class="mt-md">
        <div class="skeleton-card" style="height:64px;border-radius:var(--r-md);margin-bottom:var(--sp-sm)"></div>
        <div class="skeleton-card" style="height:64px;border-radius:var(--r-md);margin-bottom:var(--sp-sm)"></div>
        <div class="skeleton-card" style="height:64px;border-radius:var(--r-md)"></div>
      </div>
    </div>

  </main>
  <nav class="bottom-nav" id="bottom-nav"></nav>
</div>

<!-- Client detail drawer -->
<div class="modal-overlay" id="client-modal" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <span class="heading-sm" id="client-modal-name">Client</span>
      <button class="btn-icon" id="close-client-modal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body" id="client-modal-body">Loading…</div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="close-client-modal-2">Close</button>
      <a id="view-client-jobs-btn" href="#" class="btn btn-primary">View Jobs</a>
    </div>
  </div>
</div>

<script type="module">
import { requireAdmin } from '../assets/js/auth.js';
import { renderNav }    from '../assets/js/nav.js';
import { get }          from '../assets/js/api.js';

await requireAdmin();
renderNav();

let allClients = [];

function fmtDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
}

function renderClients(list) {
  const el = document.getElementById('clients-container');
  if (!list.length) {
    el.innerHTML = `<div class="empty-state mt-lg">
      <div class="empty-state-title">No clients found</div>
      <div class="empty-state-body">No clients match your search.</div>
    </div>`;
    return;
  }
  el.innerHTML = `<div class="card" style="padding:0;overflow:hidden">
    ${list.map(c => `
      <div class="list-row client-row" data-id="${c.id}" style="cursor:pointer">
        <div class="list-row-icon">
          <div class="avatar">${(c.full_name || 'C')[0].toUpperCase()}</div>
        </div>
        <div class="list-row-body">
          <div class="list-row-title">${c.full_name}</div>
          <div class="list-row-sub">${c.email} · ${c.phone || '—'}</div>
        </div>
        <div class="list-row-meta">
          <div class="caption text-secondary">${c.total_bookings ?? 0} bookings</div>
        </div>
      </div>`).join('')}
  </div>`;

  document.querySelectorAll('.client-row').forEach(row => {
    row.addEventListener('click', () => openClientModal(row.dataset.id));
  });
}

const modal    = document.getElementById('client-modal');
const closeBtn = document.getElementById('close-client-modal');
const closeBtn2= document.getElementById('close-client-modal-2');
closeBtn.addEventListener('click',  () => modal.style.display = 'none');
closeBtn2.addEventListener('click', () => modal.style.display = 'none');
modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });

async function openClientModal(clientId) {
  modal.style.display = 'flex';
  const body = document.getElementById('client-modal-body');
  body.innerHTML = '<div class="skeleton-text" style="width:60%;height:16px;margin-bottom:8px"></div><div class="skeleton-text" style="width:80%;height:14px"></div>';
  try {
    const data = await get(`/api/admin/client.php?id=${clientId}`);
    const c = data.client;
    document.getElementById('client-modal-name').textContent = c.full_name;
    document.getElementById('view-client-jobs-btn').href = `jobs.php?client=${c.id}`;
    body.innerHTML = `
      <div class="stack stack-sm">
        <div class="row row-between"><span class="label-sm text-secondary">Email</span><span class="body-sm">${c.email}</span></div>
        <div class="divider"></div>
        <div class="row row-between"><span class="label-sm text-secondary">Phone</span><span class="body-sm">${c.phone || '—'}</span></div>
        <div class="divider"></div>
        <div class="row row-between"><span class="label-sm text-secondary">Registered</span><span class="body-sm">${fmtDate(c.created_at)}</span></div>
        <div class="divider"></div>
        <div class="row row-between"><span class="label-sm text-secondary">Total Bookings</span><span class="body-sm">${c.total_bookings ?? 0}</span></div>
        <div class="divider"></div>
        <div class="row row-between"><span class="label-sm text-secondary">Total Spent</span><span class="body-sm">₱${Number(c.total_spent ?? 0).toLocaleString()}</span></div>
        ${c.last_booking ? `<div class="divider"></div><div class="row row-between"><span class="label-sm text-secondary">Last Booking</span><span class="body-sm">${fmtDate(c.last_booking)}</span></div>` : ''}
      </div>`;
  } catch (err) {
    body.innerHTML = `<div class="caption text-secondary">Failed to load client details.</div>`;
  }
}

async function loadClients() {
  try {
    const data = await get('/api/admin/clients.php');
    allClients = data.clients || [];
    renderClients(allClients);
  } catch (err) {
    document.getElementById('clients-container').innerHTML = `
      <div class="error-state mt-lg">
        <div class="error-state-title">Could not load clients</div>
        <div class="error-state-body">${err.message}</div>
        <button class="btn btn-ghost btn-sm mt-sm" onclick="location.reload()">Retry</button>
      </div>`;
  }
}

document.getElementById('search-input').addEventListener('input', e => {
  const q = e.target.value.toLowerCase();
  renderClients(q ? allClients.filter(c => (c.full_name + c.email + (c.phone || '')).toLowerCase().includes(q)) : allClients);
});

loadClients();
</script>
</body>
</html>
