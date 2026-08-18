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
  <title>Invoices — Glorikar Engineering</title>
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
          <div class="page-title">Invoices</div>
          <div class="page-subtitle">Billing and payment records</div>
        </div>
      </div>

      <!-- Summary -->
      <div class="grid-3 mt-md" id="invoice-stats">
        <div class="skeleton-card" style="height:80px;border-radius:var(--r-md)"></div>
        <div class="skeleton-card" style="height:80px;border-radius:var(--r-md)"></div>
        <div class="skeleton-card" style="height:80px;border-radius:var(--r-md)"></div>
      </div>

      <!-- Filters -->
      <div class="action-bar mt-md">
        <div class="row" style="gap:var(--sp-sm);flex:1">
          <input class="input" id="search-input" placeholder="Search client or invoice…" style="max-width:260px"/>
          <div class="select-wrap">
            <select class="input" id="paid-filter" style="width:140px">
              <option value="">All</option>
              <option value="0">Unpaid</option>
              <option value="1">Paid</option>
            </select>
          </div>
        </div>
      </div>

      <div id="invoices-container" class="mt-md">
        <div class="skeleton-card" style="height:64px;border-radius:var(--r-md);margin-bottom:var(--sp-sm)"></div>
        <div class="skeleton-card" style="height:64px;border-radius:var(--r-md);margin-bottom:var(--sp-sm)"></div>
        <div class="skeleton-card" style="height:64px;border-radius:var(--r-md)"></div>
      </div>
    </div>

  </main>
  <nav class="bottom-nav" id="bottom-nav"></nav>
</div>

<div class="toast-container" id="toast-container"></div>

<script type="module">
import { requireAdmin } from '../assets/js/auth.js';
import { renderNav }    from '../assets/js/nav.js';
import { get }          from '../assets/js/api.js';

await requireAdmin();
renderNav();

let allInvoices = [];

function fmtDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
}
function fmtMoney(n) { return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 }); }

function renderInvoices(list) {
  const el = document.getElementById('invoices-container');
  if (!list.length) {
    el.innerHTML = `<div class="empty-state mt-lg">
      <div class="empty-state-icon"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>
      <div class="empty-state-title">No invoices found</div>
      <div class="empty-state-body">Invoices are generated when a job is marked complete.</div>
    </div>`;
    return;
  }
  el.innerHTML = `<div class="card" style="padding:0;overflow:hidden">
    ${list.map(inv => `
      <a href="invoice-detail.php?id=${inv.id}" class="list-row">
        <div class="list-row-body">
          <div class="list-row-title">${inv.client_name}</div>
          <div class="list-row-sub">${inv.services?.join(', ')} · ${fmtDate(inv.issued_at)}</div>
        </div>
        <div class="list-row-meta" style="align-items:flex-end;gap:var(--sp-xs)">
          <div class="label-sm">${fmtMoney(inv.total_amount)}</div>
          <span class="badge ${inv.paid ? 'badge-completed' : 'badge-pending'}">${inv.paid ? 'Paid' : 'Unpaid'}</span>
        </div>
      </a>`).join('')}
  </div>`;
}

function applyFilters() {
  const q    = document.getElementById('search-input').value.toLowerCase();
  const paid = document.getElementById('paid-filter').value;
  let list = allInvoices;
  if (q)    list = list.filter(i => (i.client_name + (i.id || '')).toLowerCase().includes(q));
  if (paid !== '') list = list.filter(i => String(Number(i.paid)) === paid);
  renderInvoices(list);
}

async function loadInvoices() {
  try {
    const data = await get('/api/admin/invoices.php');
    allInvoices = data.invoices || [];
    const stats = data.stats || {};

    document.getElementById('invoice-stats').innerHTML = `
      <div class="stat-card">
        <div class="stat-card-label">Total Invoiced</div>
        <div class="stat-card-value">${fmtMoney(stats.total_amount)}</div>
        <div class="stat-card-sub">${allInvoices.length} invoices</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-label">Collected</div>
        <div class="stat-card-value">${fmtMoney(stats.paid_amount)}</div>
        <div class="stat-card-sub">${stats.paid_count ?? 0} paid</div>
      </div>
      <div class="stat-card">
        <div class="stat-card-label">Outstanding</div>
        <div class="stat-card-value" style="color:var(--status-pending)">${fmtMoney(stats.unpaid_amount)}</div>
        <div class="stat-card-sub">${stats.unpaid_count ?? 0} unpaid</div>
      </div>`;

    applyFilters();
  } catch (err) {
    document.getElementById('invoices-container').innerHTML = `
      <div class="error-state mt-lg">
        <div class="error-state-title">Could not load invoices</div>
        <div class="error-state-body">${err.message}</div>
        <button class="btn btn-ghost btn-sm mt-sm" onclick="location.reload()">Retry</button>
      </div>`;
  }
}

document.getElementById('search-input').addEventListener('input', applyFilters);
document.getElementById('paid-filter').addEventListener('change', applyFilters);

loadInvoices();
</script>
</body>
</html>
