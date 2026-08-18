<?php
/**
 * admin/schedule-detail.php — single schedule: stops in order, approve,
 * dispatch, reassign team, open job-complete for each stop.
 */

$pageTitle = 'Schedule';
require __DIR__ . '/../includes/guard.php';
if ($GUARD_ROLE !== 'admin') { http_response_code(403); exit('Forbidden'); }
page_start($pageTitle, $GUARD_USER);
page_header('Schedule detail', 'route-map.php');
?>
<main class="content">
  <div id="detail-view">
    <div class="skeleton" style="height:140px;margin-bottom:16px;"></div>
    <div class="skeleton" style="height:200px;"></div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const params = new URLSearchParams(location.search);
  const id = params.get('id');
  if (!id) { window.location.href = 'route-map.php'; return; }

  const el = document.getElementById('detail-view');

  try {
    const res = await GL.get('/api/schedule/get.php?id=' + encodeURIComponent(id));
    const s = res.schedule;

    el.innerHTML = `
      <div class="card">
        <div class="row" style="justify-content:space-between;">
          <span class="heading-lg">${s.scheduled_date}</span>
          <span class="status-badge ${s.status}">${s.status}</span>
        </div>
        <div class="list-row"><span class="label-sm text-secondary">Team</span><span class="label-sm">${s.team_name} ${s.team_vehicle ? '· ' + s.team_vehicle : ''}</span></div>
        <div class="list-row"><span class="label-sm text-secondary">Members</span><span class="label-sm">${s.team_members || '—'}</span></div>
        <div class="list-row"><span class="label-sm text-secondary">Route distance</span><span class="label-sm">${s.total_distance_km != null ? Number(s.total_distance_km).toFixed(1) + ' km' : '—'}</span></div>
      </div>

      <h2 class="section-header mt-lg">Stops</h2>
      <div id="stops">
        ${(s.stops || []).map(st => `
          <div class="card stop-item">
            <div class="stop-index">${st.stop_order}</div>
            <div class="col grow">
              <span class="heading-sm">${st.client_name}</span>
              <span class="caption text-secondary">${st.address}</span>
              <span class="caption mt-sm" style="color:var(--accent);">${st.duration_hrs} hrs · ETA ${st.eta || '—'} · ${GL.statusLabel(st.booking_status)}</span>
            </div>
            <a class="btn btn-ghost btn-sm" href="job-complete.php?id=${st.booking_id}">Job</a>
          </div>`).join('')}
      </div>

      ${s.status === 'dispatched' ? `
        <h2 class="section-header mt-lg">Field tracking</h2>
        <div class="card">
          <div class="list-row">
            <div class="col"><span class="heading-sm">Advance status</span><span class="caption text-secondary">Push clients through the flow as the team works.</span></div>
          </div>
          <div class="row wrap">
            <button class="btn btn-ghost btn-sm" id="advance-btn">Advance all stops</button>
          </div>
          <p class="caption text-secondary mt-sm" id="advance-status"></p>
        </div>` : ''}

      <div class="bottom-bar row" style="gap:var(--sp-sm);">
        ${s.status === 'draft' ? `<button class="btn btn-primary grow" id="approve-btn">Approve</button>` : ''}
        ${s.status === 'approved' ? `<button class="btn btn-primary grow" id="dispatch-btn">Dispatch</button>` : ''}
        ${s.status === 'dispatched' ? `<a class="btn btn-ghost grow" href="route-map.php">Track in field</a>` : ''}
      </div>
    `;

    // --- Field tracking: advance each stop's booking status forward ---
    const advanceBtn = document.getElementById('advance-btn');
    if (advanceBtn) {
      const stops = s.stops || [];
      advanceBtn.addEventListener('click', async function () {
        const order = ['scheduled', 'en_route', 'in_progress', 'completed'];
        advanceBtn.disabled = true;
        const statusEl = document.getElementById('advance-status');
        for (const st of stops) {
          const idx = order.indexOf(st.booking_status);
          const next = order[idx + 1];
          if (!next) { continue; }
          try {
            await GL.post('/api/bookings/update-status.php', { id: st.booking_id, status: next });
            statusEl.textContent = `${st.client_name}: ${next}`;
            await new Promise(r => setTimeout(r, 350));
          } catch (e) {
            statusEl.textContent = `${st.client_name}: ${e.message}`;
          }
        }
        statusEl.textContent = statusEl.textContent || 'All stops complete.';
        setTimeout(() => location.reload(), 900);
      });
    }

    const approveBtn = document.getElementById('approve-btn');
    if (approveBtn) approveBtn.addEventListener('click', async () => {
      try { await GL.post('/api/schedule/approve.php', { id }); GL.toast('Schedule approved.'); location.reload(); }
      catch (e) { GL.toast(e.message); }
    });

    const dispatchBtn = document.getElementById('dispatch-btn');
    if (dispatchBtn) dispatchBtn.addEventListener('click', async () => {
      dispatchBtn.disabled = true;
      try { await GL.post('/api/schedule/dispatch.php', { id }); GL.toast('Dispatched — clients notified.'); location.reload(); }
      catch (e) { GL.toast(e.message); dispatchBtn.disabled = false; }
    });

  } catch (e) {
    el.innerHTML = `<?= error_state_html('Could not load this schedule.') ?>`;
  }
});
</script>
<?php page_end(); ?>