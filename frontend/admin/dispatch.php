<?php
/**
 * admin/dispatch.php — Dispatch approved schedules to the field.
 */

$pageTitle = 'Dispatch';
require __DIR__ . '/../includes/guard.php';
if ($GUARD_ROLE !== 'admin') { http_response_code(403); exit('Forbidden'); }
page_start($pageTitle, $GUARD_USER);
page_header('Dispatch');
?>
<main class="content">
  <h2 class="section-header">Ready to dispatch</h2>
  <div id="approved-list">
    <div class="skeleton" style="height:90px;margin-bottom:12px;"></div>
    <div class="skeleton" style="height:90px;"></div>
  </div>

  <h2 class="section-header mt-lg">Already dispatched</h2>
  <div id="dispatched-list">
    <div class="skeleton" style="height:80px;"></div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const approvedEl = document.getElementById('approved-list');
  const dispatchedEl = document.getElementById('dispatched-list');

  async function load() {
    try {
      const [approved, dispatched] = await Promise.all([
        GL.get('/api/schedule/drafts.php?status=approved'),
        GL.get('/api/schedule/drafts.php?status=dispatched'),
      ]);

      approvedEl.innerHTML = (approved.schedules || []).length
        ? (approved.schedules || []).map(s => `
            <div class="card">
              <div class="row" style="justify-content:space-between;">
                <div class="col">
                  <span class="heading-sm">${s.scheduled_date} · ${s.team_name}</span>
                  <span class="caption text-secondary">${s.stop_count} stops</span>
                </div>
                <button class="btn btn-primary btn-sm" data-dispatch="${s.id}">Dispatch</button>
              </div>
            </div>`).join('')
        : `<?= empty_state_html('truck', 'Nothing to dispatch', 'Approve draft schedules first.') ?>`;

      dispatchedEl.innerHTML = (dispatched.schedules || []).length
        ? (dispatched.schedules || []).map(s => `
            <div class="card" style="cursor:pointer;" onclick="location.href='schedule-detail.php?id=${s.id}'">
              <div class="row" style="justify-content:space-between;">
                <span class="heading-sm">${s.scheduled_date} · ${s.team_name}</span>
                <span class="status-badge dispatched">dispatched</span>
              </div>
              <p class="caption text-secondary mt-sm">${s.stop_count} stops · clients notified</p>
            </div>`).join('')
        : `<?= empty_state_html('truck', 'No active dispatches') ?>`;
    } catch (e) {
      approvedEl.innerHTML = `<?= error_state_html('Could not load schedules.') ?>`;
    }
  }

  approvedEl.addEventListener('click', async function (ev) {
    const btn = ev.target.closest('[data-dispatch]');
    if (!btn) return;
    btn.disabled = true;
    btn.textContent = 'Dispatching…';
    try {
      await GL.post('/api/schedule/dispatch.php', { id: btn.dataset.dispatch });
      GL.toast('Dispatched — clients notified with ETA.');
      load();
    } catch (e) {
      GL.toast(e.message);
      btn.disabled = false;
      btn.textContent = 'Dispatch';
    }
  });

  load();
});
</script>
<?php page_end(); ?>