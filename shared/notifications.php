<?php
require __DIR__ . '/../backend/includes/auth-guard.php';
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
  <title>Notifications — Glorikar Engineering</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/css/theme.css"/>
  <link rel="stylesheet" href="../assets/css/components.css"/>
  <link rel="stylesheet" href="../assets/css/layout.css"/>
  <style>
    .notif-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      padding: var(--sp-md);
      display: flex;
      gap: var(--sp-md);
      align-items: flex-start;
    }
    .notif-icon {
      width: 38px; height: 38px;
      border-radius: var(--r-full);
      background: var(--accent-dim);
      color: var(--white);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .notif-title { font: 600 14px/20px 'Inter'; color: var(--text-primary); }
    .notif-body  { font: 400 13px/18px 'Inter'; color: var(--text-secondary); margin-top: 2px; }
    .notif-time  { font: 400 12px/16px 'Inter'; color: var(--text-tertiary); margin-top: 6px; }
    .push-section {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      overflow: hidden;
    }
    .push-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: var(--sp-md);
      gap: var(--sp-md);
    }
    .push-row-label { font: 500 13px/16px 'Inter'; color: var(--text-secondary); }
  </style>
</head>
<body>
<div class="app-shell">
  <aside class="sidebar" id="sidebar"></aside>
  <main class="main-content">

    <div class="page">
      <div class="page-header">
        <div class="page-title">Notifications</div>
      </div>

      <div id="notifications-list" class="mt-md" style="display:flex;flex-direction:column;gap:var(--sp-sm)">
        <div class="skeleton-card" style="height:88px;border-radius:var(--r-md)"></div>
        <div class="skeleton-card" style="height:88px;border-radius:var(--r-md)"></div>
      </div>

      <!-- Push notification settings -->
      <div class="mt-lg">
        <div class="label-sm text-secondary mb-sm" style="padding-left:var(--sp-xs)">Push Notifications</div>
        <div class="push-section">
          <div class="push-row">
            <div>
              <div class="push-row-label">Turn Off Notifications</div>
              <div class="caption text-secondary">Stop receiving push alerts about your service updates.</div>
            </div>
            <button class="btn btn-ghost btn-sm" id="turn-off-notifications" hidden>Turn off</button>
          </div>
        </div>
      </div>
    </div>

  </main>
  <nav class="bottom-nav" id="bottom-nav"></nav>
</div>

<div class="toast-container" id="toast-container"></div>

<script type="module">
import { renderNav } from '../assets/js/nav.js';
import { get, patch } from '../assets/js/api.js';
import { turnOffNotifications } from '../assets/js/pwa.js';

renderNav();

function syncPushButton() {
  const btn = document.getElementById('turn-off-notifications');
  if (!btn) return;
  btn.hidden = localStorage.getItem('push_subscribed') !== '1';
}

document.getElementById('turn-off-notifications')?.addEventListener('click', async () => {
  await turnOffNotifications();
  toast('Notifications turned off.', 'success');
  syncPushButton();
});

syncPushButton();

function toast(msg, type = 'info') {
  const c = document.getElementById('toast-container');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.textContent = msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}

const bellIcon = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>`;

function formatTime(ts) {
  if (!ts) return '';
  const d = new Date(ts);
  if (isNaN(d.getTime())) return String(ts);
  const now = new Date();
  const diff = now - d;
  if (diff < 60_000) return 'Just now';
  if (diff < 3_600_000) return Math.floor(diff / 60_000) + 'm ago';
  if (diff < 86_400_000) return Math.floor(diff / 3_600_000) + 'h ago';
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

function renderNotifications(items) {
  const container = document.getElementById('notifications-list');

  if (!items?.length) {
    container.innerHTML = `
      <div class="empty-state">
        <div class="empty-state-title">No notifications</div>
        <div class="empty-state-body">You're all caught up. New updates will appear here.</div>
      </div>`;
    return;
  }

  container.innerHTML = items.map(n => `
    <div class="notif-card">
      <div class="notif-icon">${bellIcon}</div>
      <div class="flex-1 min-w-0">
        <div class="notif-title">${n.title || 'Notification'}</div>
        ${n.body ? `<div class="notif-body">${n.body}</div>` : ''}
        <div class="notif-time">${formatTime(n.created_at || n.timestamp)}</div>
      </div>
    </div>
  `).join('');
}

async function loadNotifications() {
  try {
    const data = await get('/api/notifications/list.php');
    renderNotifications(data.notifications);

    // Mark all as read once loaded
    try {
      await patch('/api/notifications/read-all.php', {});
    } catch { /* non-fatal */ }
  } catch (err) {
    document.getElementById('notifications-list').innerHTML = `
      <div class="error-state">
        <div class="error-state-title">Could not load notifications</div>
        <div class="error-state-body">${err.message}</div>
        <button class="btn btn-ghost btn-sm mt-sm" onclick="location.reload()">Retry</button>
      </div>`;
  }
}

loadNotifications();
</script>
</body>
</html>