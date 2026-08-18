// ============================================================
// nav.js — role-based nav behavior: active tab highlight,
// unread notification badge, logout, user chip initials.
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
  // Active tab highlight based on current path.
  const path = window.location.pathname;
  const currentFile = path.split('/').pop();

  // Register the service worker (PWA install + push).
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js').catch(() => { /* HTTPS required in production */ });
  }

  document.querySelectorAll('[data-nav-link]').forEach(function (link) {
    const href = link.getAttribute('href');
    const target = href.split('/').pop().split('?')[0];
    if (target === currentFile) {
      link.classList.add('active');
    }
  });

  // User chip initials.
  const chip = document.getElementById('user-chip-name');
  const chipInitials = document.getElementById('user-chip-initials');
  if (chip && chip.dataset.name) {
    if (chipInitials) {
      const words = chip.dataset.name.trim().split(/\s+/);
      chipInitials.textContent = (words[0][0] + (words[1] ? words[1][0] : '')).toUpperCase();
    }
  }

  // Logout buttons.
  document.querySelectorAll('[data-logout]').forEach(function (btn) {
    btn.addEventListener('click', async function (e) {
      e.preventDefault();
      btn.disabled = true;
      try {
        await GL.post('/api/auth/logout.php', {});
      } catch (err) { /* ignore */ }
      window.location.href = 'login.php';
    });
  });

  // Unread notification badge (poll + refresh).
  async function refreshBadge() {
    const badges = document.querySelectorAll('[data-notif-count]');
    if (!badges.length) return;
    try {
      const res = await GL.get('/api/notifications/list.php');
      const unread = (res.notifications || []).filter((n) => !n.is_read).length;
      badges.forEach((b) => {
        b.textContent = unread > 0 ? (unread > 99 ? '99+' : unread) : '';
        b.classList.toggle('hidden', unread === 0);
      });
    } catch (err) { /* silent */ }
  }
  refreshBadge();
  setInterval(refreshBadge, 30000);
});