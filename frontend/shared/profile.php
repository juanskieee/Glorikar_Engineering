<?php
/**
 * shared/profile.php — account details, logout, push notification toggle.
 */

$pageTitle = 'Profile';
require __DIR__ . '/../includes/guard.php';
page_start($pageTitle, $GUARD_USER);
page_header('Profile');
?>
<main class="content">
  <div id="profile-view">
    <div class="skeleton" style="height:120px;margin-bottom:16px;"></div>
    <div class="skeleton" style="height:120px;"></div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const el = document.getElementById('profile-view');

  let u = null;
  try {
    const res = await GL.get('/api/auth/me.php');
    u = res.user;
  } catch (e) {
    el.innerHTML = `<?= error_state_html('Could not load your profile.') ?>`;
    return;
  }

  const initials = u.full_name.split(/\s+/).map(w => w[0]).join('').slice(0, 2).toUpperCase();

  el.innerHTML = `
    <div class="card" style="display:flex;align-items:center;gap:var(--sp-md);">
      <div class="user-avatar" style="width:56px;height:56px;font-size:20px;">${initials}</div>
      <div class="col">
        <span class="heading-lg">${u.full_name}</span>
        <span class="caption text-secondary">${u.role === 'admin' ? 'Administrator' : 'Client'}</span>
      </div>
    </div>

    <div class="card mt-md">
      <div class="list-row"><span class="label-sm text-secondary">Email</span><span class="label-sm">${u.email}</span></div>
      <div class="list-row"><span class="label-sm text-secondary">Phone</span><span class="label-sm">${u.phone || '—'}</span></div>
      <div class="list-row"><span class="label-sm text-secondary">Address</span><span class="label-sm" style="text-align:right;">${u.address}</span></div>
      <div class="list-row"><span class="label-sm text-secondary">Member since</span><span class="label-sm">${new Date(u.created_at.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'long' })}</span></div>
    </div>

    <div class="card mt-md">
      <div class="row" style="justify-content:space-between;">
        <div class="col">
          <span class="heading-sm">Push notifications</span>
          <span class="caption text-secondary">Get booking updates on this device.</span>
        </div>
        <button class="btn btn-ghost btn-sm" id="push-toggle">Enable</button>
      </div>
      <p class="caption text-secondary mt-sm" id="push-status" style="display:none;"></p>
    </div>

    <button class="btn btn-danger mt-lg" data-logout>Sign out</button>
  `;

  // ---- Push notification toggle (Web Push) ----
  const pushBtn = document.getElementById('push-toggle');
  const pushStatus = document.getElementById('push-status');

  function pushSupported() {
    return ('serviceWorker' in navigator) && ('PushManager' in window);
  }

  async function togglePush() {
    if (!pushSupported()) {
      pushStatus.style.display = 'block';
      pushStatus.textContent = 'Push notifications are not supported in this browser.';
      return;
    }

    try {
      const reg = await navigator.serviceWorker.ready;
      let sub = await reg.pushManager.getSubscription();

      if (sub) {
        await sub.unsubscribe();
        pushStatus.style.display = 'block';
        pushStatus.textContent = 'Push notifications disabled for this device.';
        pushBtn.textContent = 'Enable';
        return;
      }

      const vapidKey = window.GL_CONFIG.VAPID_PUBLIC_KEY;
      if (!vapidKey || vapidKey === 'YOUR_VAPID_PUBLIC_KEY') {
        pushStatus.style.display = 'block';
        pushStatus.textContent = 'Notifications are not configured yet (add VAPID keys to enable).';
        return;
      }

      sub = await reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidKey),
      });

      await GL.post('/api/push/subscribe.php', {
        endpoint: sub.endpoint,
        keys: { p256dh: btoa(String.fromCharCode(...new Uint8Array(sub.getKey('p256dh')))), auth: btoa(String.fromCharCode(...new Uint8Array(sub.getKey('auth')))) },
      });

      pushStatus.style.display = 'block';
      pushStatus.textContent = 'Push notifications enabled.';
      pushBtn.textContent = 'Disable';
    } catch (e) {
      pushStatus.style.display = 'block';
      pushStatus.textContent = 'Could not enable push: ' + (e.message || 'unknown error');
    }
  }

  function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    return new Uint8Array([...rawData].map(charCode => charCode.charCodeAt(0)));
  }

  pushBtn.addEventListener('click', togglePush);
});
</script>
<?php page_end(); ?>