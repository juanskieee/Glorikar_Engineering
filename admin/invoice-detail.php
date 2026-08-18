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
  <title>Invoice — Glorikar Engineering</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/css/theme.css"/>
  <link rel="stylesheet" href="../assets/css/components.css"/>
  <link rel="stylesheet" href="../assets/css/layout.css"/>
  <style>
    .invoice-header {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      padding: var(--sp-lg);
    }
    .invoice-logo {
      font: 700 20px/24px 'Inter';
      color: var(--text-primary);
      letter-spacing: -0.3px;
    }
    .invoice-logo span { color: var(--accent); }
    .invoice-meta { display: grid; grid-template-columns: 1fr 1fr; gap: var(--sp-md); margin-top: var(--sp-lg); }
    .invoice-meta-block .label-sm { margin-bottom: var(--sp-xs); }
    .invoice-table { width: 100%; border-collapse: collapse; }
    .invoice-table th {
      text-align: left;
      font: 500 12px/16px 'Inter';
      color: var(--text-secondary);
      padding: var(--sp-sm) var(--sp-md);
      border-bottom: 1px solid var(--border);
    }
    .invoice-table th:last-child { text-align: right; }
    .invoice-table td {
      padding: var(--sp-sm) var(--sp-md);
      font: 400 14px/20px 'Inter';
      color: var(--text-primary);
      border-bottom: 1px solid var(--border);
    }
    .invoice-table td:last-child { text-align: right; }
    .invoice-table tr:last-child td { border-bottom: none; }
    .invoice-total-row td {
      font: 600 15px/20px 'Inter';
      padding-top: var(--sp-md);
      border-top: 1px solid var(--border);
    }
  </style>
</head>
<body>
<div class="app-shell">
  <aside class="sidebar" id="sidebar"></aside>
  <main class="main-content">

    <div class="page">
      <div class="page-header">
        <div class="page-header-left">
          <button class="btn-icon" onclick="history.back()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <div class="page-title">Invoice</div>
        </div>
        <div class="row" style="gap:var(--sp-sm)" id="header-actions"></div>
      </div>

      <div id="content" class="stack stack-md mt-md">
        <div class="skeleton-card" style="height:200px;border-radius:var(--r-md)"></div>
        <div class="skeleton-card" style="height:180px;border-radius:var(--r-md)"></div>
      </div>
    </div>

  </main>
  <nav class="bottom-nav" id="bottom-nav"></nav>
</div>

<div class="toast-container" id="toast-container"></div>

<script type="module">
import { requireAdmin }     from '../assets/js/auth.js';
import { renderNav }        from '../assets/js/nav.js';
import { get, patch }       from '../assets/js/api.js';

await requireAdmin();
renderNav();

const params = new URLSearchParams(location.search);
const invoiceId  = params.get('id');
const bookingId  = params.get('booking');

if (!invoiceId && !bookingId) { location.href = 'invoices.php'; }

function toast(msg, type = 'info') {
  const c = document.getElementById('toast-container');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.textContent = msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}
function fmtDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-PH', { month: 'long', day: 'numeric', year: 'numeric' });
}
function fmtMoney(n) { return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2 }); }

function renderInvoice(inv) {
  const actions = document.getElementById('header-actions');
  actions.innerHTML = `
    ${!inv.paid ? `<button class="btn btn-primary btn-sm" id="mark-paid-btn">Mark as Paid</button>` : `<span class="badge badge-completed" style="padding:var(--sp-sm) var(--sp-md)">Paid</span>`}
    <button class="btn btn-ghost btn-sm" onclick="window.print()">Print</button>`;

  document.getElementById('mark-paid-btn')?.addEventListener('click', async () => {
    const btn = document.getElementById('mark-paid-btn');
    btn.disabled = true; btn.textContent = 'Saving…';
    try {
      await patch(`/api/admin/invoice.php?id=${inv.id}`, { paid: true });
      toast('Invoice marked as paid.', 'success');
      loadInvoice();
    } catch (err) {
      toast('Failed: ' + err.message, 'error');
      btn.disabled = false; btn.textContent = 'Mark as Paid';
    }
  });

  document.getElementById('content').innerHTML = `
    <!-- Invoice header card -->
    <div class="invoice-header">
      <div class="row row-between">
        <div class="invoice-logo">Glorikar <span>Engineering</span></div>
        <div style="text-align:right">
          <div class="heading-sm">Invoice</div>
          <div class="caption text-secondary mt-xs">#${inv.id?.slice(0,8).toUpperCase()}</div>
        </div>
      </div>
      <div class="invoice-meta">
        <div class="invoice-meta-block">
          <div class="label-sm text-secondary">Bill To</div>
          <div class="body-sm">${inv.client_name}</div>
          <div class="caption text-secondary">${inv.client_email || ''}</div>
          <div class="caption text-secondary">${inv.client_phone || ''}</div>
        </div>
        <div class="invoice-meta-block" style="text-align:right">
          <div class="label-sm text-secondary">Service Address</div>
          <div class="body-sm">${inv.address || '—'}</div>
          <div class="caption text-secondary mt-sm">Issued: ${fmtDate(inv.issued_at)}</div>
          <div class="caption text-secondary">Job Date: ${fmtDate(inv.scheduled_date)}</div>
        </div>
      </div>
    </div>

    <!-- Line items -->
    <div class="card" style="padding:0;overflow:hidden">
      <div style="padding:var(--sp-md)">
        <div class="heading-sm">Services Rendered</div>
      </div>
      <table class="invoice-table">
        <thead>
          <tr>
            <th>Description</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
          ${(inv.line_items || []).map(item => `
            <tr>
              <td>${item.description}</td>
              <td>${item.qty}</td>
              <td>${fmtMoney(item.unit_price)}</td>
              <td>${fmtMoney(item.qty * item.unit_price)}</td>
            </tr>`).join('')}
          ${inv.notes ? `<tr><td colspan="4" class="caption text-secondary" style="padding-top:var(--sp-sm)">Note: ${inv.notes}</td></tr>` : ''}
        </tbody>
        <tfoot>
          <tr class="invoice-total-row">
            <td colspan="3">Total</td>
            <td>${fmtMoney(inv.total_amount)}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Payment status -->
    <div class="card">
      <div class="row row-between">
        <div>
          <div class="heading-sm">Payment Status</div>
          <div class="caption text-secondary mt-xs">${inv.paid ? 'Payment received.' : 'Payment pending from client.'}</div>
        </div>
        <span class="badge ${inv.paid ? 'badge-completed' : 'badge-pending'}">${inv.paid ? 'Paid' : 'Unpaid'}</span>
      </div>
    </div>
  `;
}

async function loadInvoice() {
  try {
    const endpoint = invoiceId
      ? `/api/admin/invoice.php?id=${invoiceId}`
      : `/api/admin/invoice.php?booking=${bookingId}`;
    const data = await get(endpoint);
    renderInvoice(data.invoice);
  } catch (err) {
    document.getElementById('content').innerHTML = `
      <div class="error-state mt-lg">
        <div class="error-state-title">Could not load invoice</div>
        <div class="error-state-body">${err.message}</div>
        <button class="btn btn-ghost btn-sm mt-sm" onclick="loadInvoice()">Retry</button>
      </div>`;
  }
}

loadInvoice();
</script>
</body>
</html>
