<?php
/**
 * shared/notifications.php — in-app notifications for any role.
 */

$pageTitle = 'Notifications';
require __DIR__ . '/../includes/guard.php';
page_start($pageTitle, $GUARD_USER);
page_header('Notifications');
?>
<main class="content">
  <div id="notif-list">
    <div class="skeleton" style="height:64px;margin-bottom:10px;"></div>
    <div class="skeleton" style="height:64px;margin-bottom:10px;"></div>
    <div class="skeleton" style="height:64px;"></div>
  </div>

  <div class="bottom-bar">
    <button class="btn btn-ghost" id="mark-all-btn">Mark all as read</button>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const el = document.getElementById('notif-list');
  const markAll = document.getElementById('mark-all-btn');

  async function load() {
    try {
      const res = await GL.get('/api/notifications/list.php');
      const items = res.notifications || [];
      if (!items.length) {
        el.innerHTML = `<?= empty_state_html('bell', 'All caught up', 'You have no notifications.') ?>`;
        markAll.disabled = true;
        return;
      }
      el.innerHTML = items.map(n => `
        <div class="card" style="${n.is_read ? 'opacity:.55;' : ''}">
          <div class="row" style="justify-content:space-between;">
            <span class="heading-sm">${n.title}</span>
            <span class="caption text-secondary">${new Date(n.created_at.replace(' ', 'T')).toLocaleDateString('en-PH', { month: 'short', day: 'numeric' })}</span>
          </div>
          <p class="body-sm text-secondary mt-sm">${n.message}</p>
          ${!n.is_read ? `<span class="status-badge scheduled" style="margin-top:var(--sp-sm);">new</span>` : ''}
        </div>`).join('');
      markAll.disabled = false;
    } catch (e) {
      el.innerHTML = `<?= error_state_html('Could not load notifications.') ?>`;
    }
  }

  markAll.addEventListener('click', async function () {
    try {
      await GL.post('/api/notifications/mark-read.php', { id: 'all' });
      markAll.disabled = true;
      load();
    } catch (e) {
      GL.toast(e.message);
    }
  });

  load();
});
</script>
<?php page_end(); ?>