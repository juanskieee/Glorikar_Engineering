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
  <title>Job Detail — Glorikar Engineering</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/css/theme.css"/>
  <link rel="stylesheet" href="../assets/css/components.css"/>
  <link rel="stylesheet" href="../assets/css/layout.css"/>
  <style>
    .detail-section {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      padding: var(--sp-md);
    }
    .detail-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: var(--sp-sm) 0;
      border-bottom: 1px solid var(--border);
      gap: var(--sp-md);
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { font: 500 12px/16px 'Inter'; color: var(--text-secondary); min-width: 120px; }
    .detail-value { font: 400 14px/20px 'Inter'; color: var(--text-primary); text-align: right; }
    .timeline-item {
      display: flex;
      gap: var(--sp-md);
      padding: var(--sp-sm) 0;
      position: relative;
    }
    .timeline-dot {
      width: 10px;
      height: 10px;
      border-radius: var(--r-full);
      background: var(--border);
      flex-shrink: 0;
      margin-top: 5px;
    }
    .timeline-dot.active { background: var(--accent); }
    .timeline-dot.done   { background: var(--status-completed); }
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
          <div>
            <div class="page-title" id="page-title">Job Detail</div>
            <div class="page-subtitle" id="page-subtitle">Loading…</div>
          </div>
        </div>
        <div id="header-actions" class="row" style="gap:var(--sp-sm)"></div>
      </div>

      <div id="content" class="stack stack-md mt-md">
        <div class="skeleton-card" style="height:160px;border-radius:var(--r-md)"></div>
        <div class="skeleton-card" style="height:120px;border-radius:var(--r-md)"></div>
        <div class="skeleton-card" style="height:100px;border-radius:var(--r-md)"></div>
      </div>
    </div>

  </main>
  <nav class="bottom-nav" id="bottom-nav"></nav>
</div>

<!-- Status update modal -->
<div class="modal-overlay" id="status-modal" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <span class="heading-sm">Update Status</span>
      <button class="btn-icon" id="close-status-modal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">New Status</label>
        <div class="select-wrap">
          <select class="input" id="new-status-select">
            <option value="scheduled">Scheduled</option>
            <option value="en_route">En Route</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>
      <div class="form-group mt-sm">
        <label class="form-label">Notes (optional)</label>
        <textarea class="input" id="status-notes" placeholder="Add a note about this status change…"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="cancel-status-modal">Cancel</button>
      <button class="btn btn-primary" id="confirm-status-btn">Update</button>
    </div>
  </div>
</div>

<!-- Assign team modal -->
<div class="modal-overlay" id="team-modal" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <span class="heading-sm">Assign Team</span>
      <button class="btn-icon" id="close-team-modal">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Select Team</label>
        <div class="select-wrap">
          <select class="input" id="team-select">
            <option value="">Loading teams…</option>
          </select>
        </div>
      </div>
      <div class="form-group mt-sm">
        <label class="form-label">Scheduled Date</label>
        <input class="input" type="date" id="assign-date"/>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="cancel-team-modal">Cancel</button>
      <button class="btn btn-primary" id="confirm-team-btn">Assign</button>
    </div>
  </div>
</div>

<div class="toast-container" id="toast-container"></div>

<script type="module">
import { requireAdmin }        from '../assets/js/auth.js';
import { renderNav }           from '../assets/js/nav.js';
import { get, post, patch }    from '../assets/js/api.js';

await requireAdmin();
renderNav();

const jobId = new URLSearchParams(location.search).get('id');
if (!jobId) { location.href = 'jobs.php'; }

let job = null;

const statusClass = {
  pending: 'pending', scheduled: 'scheduled', en_route: 'en-route',
  in_progress: 'in-progress', completed: 'completed', cancelled: 'cancelled',
};
const statusLabel = {
  pending: 'Pending', scheduled: 'Scheduled', en_route: 'En Route',
  in_progress: 'In Progress', completed: 'Completed', cancelled: 'Cancelled',
};

const TIMELINE_STEPS = ['pending','scheduled','en_route','in_progress','completed'];

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
  return new Date(d + 'T00:00:00').toLocaleDateString('en-PH', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}

function renderJob(j) {
  job = j;
  document.getElementById('page-title').textContent = j.client_name;
  document.getElementById('page-subtitle').textContent = j.services?.join(', ') || 'Service Job';

  const stepIdx = TIMELINE_STEPS.indexOf(j.status);

  // Header actions
  const actions = document.getElementById('header-actions');
  const showAssign = ['pending','scheduled'].includes(j.status);
  const showStatus = j.status !== 'completed' && j.status !== 'cancelled';
  const showInvoice = j.status === 'completed';
  actions.innerHTML = `
    ${showAssign  ? `<button class="btn btn-ghost btn-sm" id="assign-btn">Assign Team</button>` : ''}
    ${showStatus  ? `<button class="btn btn-ghost btn-sm" id="status-btn">Update Status</button>` : ''}
    ${showInvoice ? `<a href="invoice-detail.php?booking=${j.id}" class="btn btn-primary btn-sm">View Invoice</a>` : ''}`;

  document.getElementById('assign-btn')?.addEventListener('click', openTeamModal);
  document.getElementById('status-btn')?.addEventListener('click', () => {
    document.getElementById('new-status-select').value = j.status;
    document.getElementById('status-modal').style.display = 'flex';
  });

  document.getElementById('content').innerHTML = `
    <!-- Status timeline -->
    <div class="detail-section">
      <div class="section-header mb-sm">
        <span class="heading-sm">Status</span>
        <span class="badge badge-${statusClass[j.status] || 'pending'}">${statusLabel[j.status] || j.status}</span>
      </div>
      <div class="row" style="gap:0;align-items:center;padding:var(--sp-sm) 0">
        ${TIMELINE_STEPS.map((s, i) => `
          <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:var(--sp-xs)">
            <div style="
              width:28px;height:28px;border-radius:var(--r-full);
              display:flex;align-items:center;justify-content:center;
              background:${i < stepIdx ? 'var(--status-completed)' : i === stepIdx ? 'var(--accent)' : 'var(--surface-raised)'};
              border:2px solid ${i <= stepIdx ? (i < stepIdx ? 'var(--status-completed)' : 'var(--accent)') : 'var(--border)'};
              font:600 11px/14px Inter;
              color:${i <= stepIdx ? 'var(--white)' : 'var(--text-disabled)'}
            ">${i < stepIdx ? '✓' : i + 1}</div>
            <div style="font:400 10px/14px Inter;color:${i <= stepIdx ? 'var(--text-primary)' : 'var(--text-disabled)'};text-align:center">${statusLabel[s]}</div>
          </div>
          ${i < TIMELINE_STEPS.length - 1 ? `<div style="flex:0 0 24px;height:2px;background:${i < stepIdx ? 'var(--status-completed)' : 'var(--border)'};margin-bottom:20px"></div>` : ''}
        `).join('')}
      </div>
    </div>

    <!-- Job details -->
    <div class="detail-section">
      <div class="heading-sm mb-sm">Job Details</div>
      <div class="detail-row">
        <div class="detail-label">Client</div>
        <div class="detail-value">${j.client_name}</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Phone</div>
        <div class="detail-value">${j.client_phone || '—'}</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Address</div>
        <div class="detail-value" style="text-align:right">${j.address}</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Services</div>
        <div class="detail-value">${j.services?.join(', ') || '—'}</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Units</div>
        <div class="detail-value">${j.unit_count ?? '—'}</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Preferred Date</div>
        <div class="detail-value">${fmtDate(j.preferred_date_from)}${j.preferred_date_to !== j.preferred_date_from ? ' – ' + fmtDate(j.preferred_date_to) : ''}</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Scheduled Date</div>
        <div class="detail-value">${j.scheduled_date ? fmtDate(j.scheduled_date) : '—'}</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Assigned Team</div>
        <div class="detail-value">${j.team_name || '—'}</div>
      </div>
      ${j.notes ? `<div class="detail-row"><div class="detail-label">Notes</div><div class="detail-value" style="text-align:right">${j.notes}</div></div>` : ''}
    </div>

    <!-- Photos -->
    ${(j.photos_before?.length || j.photos_after?.length) ? `
    <div class="detail-section">
      <div class="heading-sm mb-sm">Job Photos</div>
      ${j.photos_before?.length ? `
        <div class="label-sm text-secondary mb-xs">Before</div>
        <div class="photo-grid mb-md">${j.photos_before.map(p => `<img class="photo-thumb" src="${p}" alt="Before"/>`).join('')}</div>` : ''}
      ${j.photos_after?.length ? `
        <div class="label-sm text-secondary mb-xs">After</div>
        <div class="photo-grid">${j.photos_after.map(p => `<img class="photo-thumb" src="${p}" alt="After"/>`).join('')}</div>` : ''}
    </div>` : ''}

    <!-- Status log -->
    ${j.status_log?.length ? `
    <div class="detail-section">
      <div class="heading-sm mb-sm">Activity Log</div>
      ${j.status_log.map(e => `
        <div class="timeline-item">
          <div class="timeline-dot done"></div>
          <div>
            <div class="label-sm">${statusLabel[e.status] || e.status}</div>
            <div class="caption text-secondary">${e.note || ''}</div>
            <div class="caption text-disabled">${e.created_at}</div>
          </div>
        </div>`).join('')}
    </div>` : ''}
  `;
}

async function loadJob() {
  try {
    const data = await get(`/api/admin/job.php?id=${jobId}`);
    renderJob(data.job);
  } catch (err) {
    document.getElementById('content').innerHTML = `
      <div class="error-state mt-lg">
        <div class="error-state-title">Could not load job</div>
        <div class="error-state-body">${err.message}</div>
        <button class="btn btn-ghost btn-sm mt-sm" onclick="loadJob()">Retry</button>
      </div>`;
  }
}

// Status modal
const statusModal   = document.getElementById('status-modal');
const confirmStatus = document.getElementById('confirm-status-btn');
document.getElementById('close-status-modal').addEventListener('click',  () => statusModal.style.display = 'none');
document.getElementById('cancel-status-modal').addEventListener('click', () => statusModal.style.display = 'none');
statusModal.addEventListener('click', e => { if (e.target === statusModal) statusModal.style.display = 'none'; });

confirmStatus.addEventListener('click', async () => {
  const newStatus = document.getElementById('new-status-select').value;
  const notes     = document.getElementById('status-notes').value.trim();
  confirmStatus.disabled = true; confirmStatus.textContent = 'Saving…';
  try {
    await patch(`/api/admin/job-status.php?id=${jobId}`, { status: newStatus, notes });
    statusModal.style.display = 'none';
    toast('Status updated.', 'success');
    loadJob();
  } catch (err) {
    toast('Failed: ' + err.message, 'error');
  } finally {
    confirmStatus.disabled = false; confirmStatus.textContent = 'Update';
  }
});

// Team modal
const teamModal   = document.getElementById('team-modal');
const confirmTeam = document.getElementById('confirm-team-btn');
document.getElementById('close-team-modal').addEventListener('click',  () => teamModal.style.display = 'none');
document.getElementById('cancel-team-modal').addEventListener('click', () => teamModal.style.display = 'none');
teamModal.addEventListener('click', e => { if (e.target === teamModal) teamModal.style.display = 'none'; });

async function openTeamModal() {
  teamModal.style.display = 'flex';
  const sel = document.getElementById('team-select');
  sel.innerHTML = '<option value="">Loading…</option>';
  try {
    const data = await get('/api/admin/teams.php');
    sel.innerHTML = data.teams.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
  } catch { sel.innerHTML = '<option value="">Failed to load</option>'; }
  if (job?.scheduled_date) document.getElementById('assign-date').value = job.scheduled_date;
}

confirmTeam.addEventListener('click', async () => {
  const team_id = document.getElementById('team-select').value;
  const date    = document.getElementById('assign-date').value;
  if (!team_id || !date) { toast('Select a team and date.', 'error'); return; }
  confirmTeam.disabled = true; confirmTeam.textContent = 'Assigning…';
  try {
    await patch(`/api/admin/assign-team.php?id=${jobId}`, { team_id, scheduled_date: date });
    teamModal.style.display = 'none';
    toast('Team assigned.', 'success');
    loadJob();
  } catch (err) {
    toast('Failed: ' + err.message, 'error');
  } finally {
    confirmTeam.disabled = false; confirmTeam.textContent = 'Assign';
  }
});

loadJob();
</script>
</body>
</html>
