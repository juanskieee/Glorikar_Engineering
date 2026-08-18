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
  <title>Profile — Glorikar Engineering</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../assets/css/theme.css"/>
  <link rel="stylesheet" href="../assets/css/components.css"/>
  <link rel="stylesheet" href="../assets/css/layout.css"/>
  <style>
    .profile-hero {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      padding: var(--sp-lg);
      display: flex;
      align-items: center;
      gap: var(--sp-md);
    }
    .profile-avatar-lg {
      width: 64px; height: 64px;
      border-radius: var(--r-full);
      background: var(--accent-dim);
      display: flex; align-items: center; justify-content: center;
      font: 700 24px/24px 'Inter';
      color: var(--white);
      flex-shrink: 0;
    }
    .profile-section {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      overflow: hidden;
    }
    .profile-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: var(--sp-md);
      border-bottom: 1px solid var(--border);
      gap: var(--sp-md);
    }
    .profile-row:last-child { border-bottom: none; }
    .profile-row-label { font: 500 13px/16px 'Inter'; color: var(--text-secondary); }
    .profile-row-value { font: 400 14px/20px 'Inter'; color: var(--text-primary); text-align: right; }
  </style>
</head>
<body>
<div class="app-shell">
  <aside class="sidebar" id="sidebar"></aside>
  <main class="main-content">

    <div class="page">
      <div class="page-header">
        <div class="page-title">Profile</div>
        <button class="btn btn-ghost btn-sm" id="logout-btn">Sign Out</button>
      </div>

      <!-- Hero -->
      <div class="profile-hero mt-md" id="profile-hero">
        <div class="profile-avatar-lg skeleton" id="profile-avatar" style="background:var(--surface-raised)"></div>
        <div>
          <div class="skeleton-title" style="width:140px;height:18px;margin-bottom:6px"></div>
          <div class="skeleton-text" style="width:100px;height:14px"></div>
        </div>
      </div>

      <!-- Account info -->
      <div class="mt-lg">
        <div class="label-sm text-secondary mb-sm" style="padding-left:var(--sp-xs)">Account</div>
        <div class="profile-section" id="account-section">
          <div class="profile-row">
            <div class="skeleton-text" style="width:80px;height:14px"></div>
            <div class="skeleton-text" style="width:160px;height:14px"></div>
          </div>
          <div class="profile-row">
            <div class="skeleton-text" style="width:60px;height:14px"></div>
            <div class="skeleton-text" style="width:120px;height:14px"></div>
          </div>
        </div>
      </div>

      <!-- Edit profile form (hidden by default) -->
      <div class="mt-lg" id="edit-form" style="display:none">
        <div class="label-sm text-secondary mb-sm" style="padding-left:var(--sp-xs)">Edit Profile</div>
        <div class="profile-section" style="padding:var(--sp-md)">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input class="input" id="edit-name" placeholder="Your full name"/>
          </div>
          <div class="form-group mt-sm">
            <label class="form-label">Phone</label>
            <input class="input" id="edit-phone" placeholder="+63 9XX XXX XXXX" type="tel"/>
          </div>
          <div class="row mt-md" style="gap:var(--sp-sm);justify-content:flex-end">
            <button class="btn btn-ghost" id="cancel-edit-btn">Cancel</button>
            <button class="btn btn-primary" id="save-profile-btn">Save Changes</button>
          </div>
        </div>
      </div>

      <!-- Change password -->
      <div class="mt-lg">
        <div class="label-sm text-secondary mb-sm" style="padding-left:var(--sp-xs)">Security</div>
        <div class="profile-section" id="password-section">
          <div class="profile-row" style="cursor:pointer" id="change-pw-toggle">
            <div class="profile-row-label">Change Password</div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--text-secondary)"><polyline points="9 18 15 12 9 6"/></svg>
          </div>
        </div>
        <div id="change-pw-form" style="display:none">
          <div class="profile-section mt-sm" style="padding:var(--sp-md)">
            <div class="form-group">
              <label class="form-label">Current Password</label>
              <input class="input" id="pw-current" type="password" placeholder="Current password"/>
            </div>
            <div class="form-group mt-sm">
              <label class="form-label">New Password</label>
              <input class="input" id="pw-new" type="password" placeholder="At least 8 characters"/>
            </div>
            <div class="form-group mt-sm">
              <label class="form-label">Confirm New Password</label>
              <input class="input" id="pw-confirm" type="password" placeholder="Repeat new password"/>
            </div>
            <div class="row mt-md" style="gap:var(--sp-sm);justify-content:flex-end">
              <button class="btn btn-ghost" id="cancel-pw-btn">Cancel</button>
              <button class="btn btn-primary" id="save-pw-btn">Update Password</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Notifications -->
      <div class="mt-lg">
        <div class="label-sm text-secondary mb-sm" style="padding-left:var(--sp-xs)">Notifications</div>
        <div class="profile-section">
          <div class="profile-row">
            <div>
              <div class="profile-row-label">Turn Off Notifications</div>
              <div class="caption text-secondary">Stop receiving push alerts about your service updates.</div>
            </div>
            <button class="btn btn-ghost btn-sm" id="turn-off-notifications" hidden>Turn off</button>
          </div>
        </div>
      </div>

      <!-- Danger zone (client only) -->
      <div class="mt-xl mb-xl" id="danger-zone" style="display:none">
        <div class="label-sm text-secondary mb-sm" style="padding-left:var(--sp-xs)">Account</div>
        <div class="profile-section">
          <div class="profile-row">
            <div>
              <div class="profile-row-label" style="color:var(--status-cancelled)">Delete Account</div>
              <div class="caption text-secondary">Permanently remove your account and all data.</div>
            </div>
            <button class="btn btn-danger btn-sm" id="delete-account-btn">Delete</button>
          </div>
        </div>
      </div>

    </div>
  </main>
  <nav class="bottom-nav" id="bottom-nav"></nav>
</div>

<div class="toast-container" id="toast-container"></div>

<script type="module">
import { getMe, logout }  from '../assets/js/auth.js';
import { renderNav }      from '../assets/js/nav.js';
import { patch, post, del } from '../assets/js/api.js';
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

function initials(name = '') {
  return name.trim().split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() || '?';
}

async function loadProfile() {
  try {
    const user = await getMe();
    const isAdmin = user.role === 'admin';

    // Hero
    document.getElementById('profile-hero').innerHTML = `
      <div class="profile-avatar-lg">${initials(user.full_name)}</div>
      <div>
        <div class="heading-lg">${user.full_name}</div>
        <div class="caption text-secondary mt-xs">${isAdmin ? 'Administrator' : 'Client'}</div>
      </div>
      <button class="btn btn-ghost btn-sm" id="edit-profile-btn" style="margin-left:auto">Edit</button>`;

    document.getElementById('edit-profile-btn').addEventListener('click', () => {
      document.getElementById('edit-name').value  = user.full_name || '';
      document.getElementById('edit-phone').value = user.phone || '';
      document.getElementById('edit-form').style.display = 'block';
    });

    // Account info
    document.getElementById('account-section').innerHTML = `
      <div class="profile-row">
        <div class="profile-row-label">Full Name</div>
        <div class="profile-row-value">${user.full_name}</div>
      </div>
      <div class="profile-row">
        <div class="profile-row-label">Email</div>
        <div class="profile-row-value">${user.email}</div>
      </div>
      <div class="profile-row">
        <div class="profile-row-label">Phone</div>
        <div class="profile-row-value">${user.phone || '—'}</div>
      </div>
      <div class="profile-row">
        <div class="profile-row-label">Role</div>
        <div class="profile-row-value">${isAdmin ? 'Administrator' : 'Client'}</div>
      </div>`;

    // Show danger zone for clients only
    if (!isAdmin) document.getElementById('danger-zone').style.display = 'block';

  } catch (err) {
    toast('Could not load profile.', 'error');
  }
}

// Edit profile
document.getElementById('cancel-edit-btn').addEventListener('click', () => {
  document.getElementById('edit-form').style.display = 'none';
});
document.getElementById('save-profile-btn').addEventListener('click', async () => {
  const name  = document.getElementById('edit-name').value.trim();
  const phone = document.getElementById('edit-phone').value.trim();
  if (!name) { toast('Name is required.', 'error'); return; }
  const btn = document.getElementById('save-profile-btn');
  btn.disabled = true; btn.textContent = 'Saving…';
  try {
    await patch('/api/profile.php', { full_name: name, phone });
    toast('Profile updated.', 'success');
    document.getElementById('edit-form').style.display = 'none';
    loadProfile();
  } catch (err) {
    toast('Failed: ' + err.message, 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'Save Changes';
  }
});

// Change password
document.getElementById('change-pw-toggle').addEventListener('click', () => {
  const form = document.getElementById('change-pw-form');
  form.style.display = form.style.display === 'none' ? 'block' : 'none';
});
document.getElementById('cancel-pw-btn').addEventListener('click', () => {
  document.getElementById('change-pw-form').style.display = 'none';
});
document.getElementById('save-pw-btn').addEventListener('click', async () => {
  const current = document.getElementById('pw-current').value;
  const newPw   = document.getElementById('pw-new').value;
  const confirm = document.getElementById('pw-confirm').value;
  if (!current || !newPw) { toast('All fields required.', 'error'); return; }
  if (newPw !== confirm)  { toast('Passwords do not match.', 'error'); return; }
  if (newPw.length < 8)   { toast('Password must be at least 8 characters.', 'error'); return; }
  const btn = document.getElementById('save-pw-btn');
  btn.disabled = true; btn.textContent = 'Saving…';
  try {
    await post('/api/auth/change-password.php', { current_password: current, new_password: newPw });
    toast('Password updated.', 'success');
    document.getElementById('change-pw-form').style.display = 'none';
    document.getElementById('pw-current').value = '';
    document.getElementById('pw-new').value = '';
    document.getElementById('pw-confirm').value = '';
  } catch (err) {
    toast('Failed: ' + err.message, 'error');
  } finally {
    btn.disabled = false; btn.textContent = 'Update Password';
  }
});

// Delete account
document.getElementById('delete-account-btn')?.addEventListener('click', async () => {
  if (!confirm('Delete your account? This cannot be undone and all your data will be removed.')) return;
  try {
    await del('/api/profile.php');
    await logout();
  } catch (err) {
    toast('Failed: ' + err.message, 'error');
  }
});

// Logout
document.getElementById('logout-btn').addEventListener('click', async () => {
  await logout();
});

loadProfile();
</script>
</body>
</html>
