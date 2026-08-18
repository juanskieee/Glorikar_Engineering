<?php
/**
 * client/booking-status.php — single booking detail + status timeline.
 */

$pageTitle = 'Booking status';
require __DIR__ . '/../includes/guard.php';
page_start($pageTitle, $GUARD_USER);
page_header('Booking status');
?>
<main class="content">
  <div id="status-view">
    <div class="skeleton" style="height:120px;margin-bottom:16px;"></div>
    <div class="skeleton" style="height:200px;"></div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const params = new URLSearchParams(location.search);
  const id = params.get('id');
  if (!id) { window.location.href = 'my-bookings.php'; return; }

  const el = document.getElementById('status-view');
  const STEPS = ['pending', 'scheduled', 'en_route', 'in_progress', 'completed'];

  try {
    const res = await GL.get('/api/bookings/get.php?id=' + encodeURIComponent(id));
    const b = res.booking;
    const stepIdx = STEPS.indexOf(b.status);
    const cancelled = b.status === 'cancelled';

    const servicesHtml = b.services.map(s => `
      <div class="list-row">
        <span class="body-sm" style="text-transform:capitalize;">${s.name} × ${s.quantity}</span>
        <span class="label-sm">${GL.money(s.base_price * s.quantity)}</span>
      </div>`).join('');

    const timelineHtml = cancelled
      ? `<div class="card" style="border-color:var(--status-cancelled);">
           <p class="heading-sm" style="color:var(--status-cancelled);">This booking was cancelled.</p>
           <p class="body-sm text-secondary mt-sm">If this was a mistake, please book again or contact us.</p>
         </div>`
      : `<div class="timeline">
           ${STEPS.map((s, i) => {
             const done = i < stepIdx;
             const active = i === stepIdx;
             const labels = { pending: 'Pending approval', scheduled: 'Scheduled', en_route: 'Technician en route', in_progress: 'Service in progress', completed: 'Completed' };
             return `
               <div class="timeline-step ${done ? 'complete' : ''} ${active ? 'active' : ''}">
                 <div class="step-dot"></div>
                 <p class="step-title heading-sm">${labels[s]}</p>
                 ${s === 'en_route' && b.schedule ? `<p class="step-time caption text-secondary">ETA slot: ${b.schedule.stops && b.schedule.stops[0] && b.schedule.stops[0].eta ? b.schedule.stops[0].eta : 'On the way'}</p>` : ''}
                 ${active ? `<p class="caption" style="color:var(--accent);">Current</p>` : ''}
               </div>`;
           }).join('')}
         </div>`;

    el.innerHTML = `
      <div class="card">
        <div class="row" style="justify-content:space-between;">
          <span class="heading-lg">${b.services.map(s => s.name).join(' + ')}</span>
          <span class="status-badge ${b.status}">${GL.statusLabel(b.status)}</span>
        </div>
        <p class="body-sm text-secondary mt-sm">${b.address}</p>
        <div class="row mt-sm" style="justify-content:space-between;">
          <span class="caption text-secondary">Preferred: ${b.preferred_date_from} → ${b.preferred_date_to}</span>
          <span class="price">${GL.money(b.total)}</span>
        </div>
        ${b.schedule ? `
          <div class="divider"></div>
          <div class="row">
            <span class="label-sm text-secondary">Team: </span>
            <span class="label-sm">${b.schedule.team_name} (${b.schedule.vehicle || 'no vehicle'})</span>
          </div>
          <div class="row mt-sm">
            <span class="label-sm text-secondary">Service date: </span>
            <span class="label-sm">${b.schedule.scheduled_date}</span>
          </div>` : ''}
        ${b.notes ? `<p class="body-sm text-secondary mt-sm">Notes: ${b.notes}</p>` : ''}
      </div>

      <div class="mt-md">${timelineHtml}</div>

      <div class="card mt-md">
        <h3 class="heading-sm mb-md">Services</h3>
        ${servicesHtml}
        <div class="divider"></div>
        <div class="row" style="justify-content:space-between;">
          <span class="label-lg">Total</span>
          <span class="price">${GL.money(b.total)}</span>
        </div>
      </div>

      ${b.photos && b.photos.length ? `
        <div class="card mt-md">
          <h3 class="heading-sm mb-md">Job photos</h3>
          <div class="photo-grid">
            ${b.photos.map(p => `<div class="photo-tile"><span class="photo-label">${p.type}</span><img src="${p.photo_url}" alt="${p.type} photo" onerror="this.style.display='none'"></div>`).join('')}
          </div>
        </div>` : ''}

      ${b.status === 'completed' ? `
        <button class="btn btn-ghost mt-lg" onclick="window.location.href='invoice.php?id=${b.id}'">View invoice</button>` : ''}
    `;
  } catch (e) {
    el.innerHTML = `<?= error_state_html('Could not load this booking.') ?>`;
  }
});
</script>
<?php page_end(); ?>