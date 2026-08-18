<?php
/**
 * admin/dashboard.php — Admin dashboard with KPIs + quick actions.
 */

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/guard.php';

// Server-side admin gate (defense in depth).
if ($GUARD_ROLE !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

page_start($pageTitle, $GUARD_USER);
page_header('Dashboard');
?>
<main class="content">
  <div class="row wrap" style="gap:var(--sp-sm);">
    <div class="card grow" style="min-width:140px;"><span class="display-lg" id="kpi-pending">—</span><span class="caption text-secondary">Pending bookings</span></div>
    <div class="card grow" style="min-width:140px;"><span class="display-lg" id="kpi-drafts">—</span><span class="caption text-secondary">Draft schedules</span></div>
    <div class="card grow" style="min-width:140px;"><span class="display-lg" id="kpi-dispatched">—</span><span class="caption text-secondary">Dispatched</span></div>
    <div class="card grow" style="min-width:140px;"><span class="display-lg" id="kpi-teams">—</span><span class="caption text-secondary">Teams available</span></div>
  </div>

  <section class="mt-lg">
    <h2 class="section-header">Quick actions</h2>
    <div class="card">
      <div class="list-row">
        <div class="col">
          <span class="heading-sm">Run scheduling engine</span>
          <span class="caption text-secondary">Cluster &amp; score pending bookings into draft routes.</span>
        </div>
        <button class="btn btn-primary btn-sm" id="run-btn">Run now</button>
      </div>
      <div class="divider"></div>
      <div class="list-row" style="cursor:pointer;" onclick="location.href='route-map.php'">
        <div class="col"><span class="heading-sm">Review routes</span><span class="caption text-secondary">Approve, reorder and dispatch on the Route Map.</span></div>
        <span class="text-secondary">→</span>
      </div>
      <div class="divider"></div>
      <div class="list-row" style="cursor:pointer;" onclick="location.href='teams.php'">
        <div class="col"><span class="heading-sm">Manage teams</span><span class="caption text-secondary">Create teams and assign members.</span></div>
        <span class="text-secondary">→</span>
      </div>
      <div class="divider"></div>
      <div class="list-row" style="cursor:pointer;" onclick="location.href='dispatch.php'">
        <div class="col"><span class="heading-sm">Dispatch</span><span class="caption text-secondary">Send approved routes to the field.</span></div>
        <span class="text-secondary">→</span>
      </div>
    </div>
  </section>

  <div id="run-result" class="hidden" style="margin-top:var(--sp-md);"></div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  async function loadKpis() {
    try {
      const [pending, drafts, dispatched, teams] = await Promise.all([
        GL.get('/api/bookings/pending.php'),
        GL.get('/api/schedule/drafts.php?status=draft'),
        GL.get('/api/schedule/drafts.php?status=dispatched'),
        GL.get('/api/teams/list.php'),
      ]);
      document.getElementById('kpi-pending').textContent = (pending.bookings || []).length;
      document.getElementById('kpi-drafts').textContent = (drafts.schedules || []).length;
      document.getElementById('kpi-dispatched').textContent = (dispatched.schedules || []).length;
      document.getElementById('kpi-teams').textContent = (teams.teams || []).filter(t => t.is_available).length;
    } catch (e) { /* KPIs stay — */ }
  }

  const runBtn = document.getElementById('run-btn');
  runBtn.addEventListener('click', async function () {
    runBtn.disabled = true;
    runBtn.textContent = 'Running…';
    const box = document.getElementById('run-result');
    box.classList.remove('hidden');
    try {
      const res = await GL.post('/api/schedule/run.php', {});
      const s = res.summary || {};
      box.innerHTML = `
        <div class="card" style="border-color:var(--status-completed);">
          <span class="body-sm">✅ Schedules created: <b>${s.schedules_created}</b> · Bookings scheduled: <b>${s.bookings_scheduled}</b> · Deferred: <b>${s.bookings_deferred}</b></span>
          ${(s.errors || []).map(e => `<p class="caption mt-sm" style="color:var(--status-cancelled);">${e}</p>`).join('')}
        </div>`;
      loadKpis();
      GL.toast('Scheduling run complete.');
    } catch (e) {
      box.innerHTML = `<div class="card" style="border-color:var(--status-cancelled);"><span class="body-sm" style="color:var(--status-cancelled);">${e.message}</span></div>`;
    }
    runBtn.disabled = false;
    runBtn.textContent = 'Run now';
  });

  loadKpis();
});
</script>
<?php page_end(); ?>