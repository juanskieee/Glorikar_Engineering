<?php
/**
 * client/booking-confirm.php — Booking flow, step 3: review & submit.
 */

$pageTitle = 'Confirm booking';
require __DIR__ . '/../includes/guard.php';
page_start($pageTitle, $GUARD_USER);
page_header('Confirm booking', 'select-dates.php');
?>
<main class="content">
  <div id="confirm-view">
    <div class="skeleton" style="height:120px;margin-bottom:16px;"></div>
    <div class="skeleton" style="height:120px;"></div>
  </div>

  <div class="bottom-bar">
    <button class="btn btn-primary" id="submit-btn">Submit booking</button>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const sel = JSON.parse(sessionStorage.getItem('gl_selection') || 'null');
  if (!sel || !Object.keys(sel.services || {}).length) {
    window.location.href = 'book-service.php';
    return;
  }

  const view = document.getElementById('confirm-view');
  const submitBtn = document.getElementById('submit-btn');

  let services = [];
  try {
    const res = await GL.get('/api/services/list.php');
    services = res.services;
  } catch (e) {
    view.innerHTML = `<?= error_state_html('Could not load services.') ?>`;
    submitBtn.disabled = true;
    return;
  }

  const items = Object.keys(sel.services).map(id => {
    const s = services.find(x => x.id == id);
    const qty = sel.services[id];
    return { ...s, qty, total: Number(s.base_price) * qty };
  });
  const grand = items.reduce((t, i) => t + i.total, 0);

  view.innerHTML = `
    <div class="card">
      <h3 class="heading-sm mb-md">Services</h3>
      ${items.map(i => `
        <div class="list-row">
          <div class="list-row-left">
            <p class="list-title heading-sm" style="text-transform:capitalize;">${i.name} × ${i.qty}</p>
            <p class="list-sub caption text-secondary">${GL.money(i.base_price)} each</p>
          </div>
          <div class="list-row-right price">${GL.money(i.total)}</div>
        </div>`).join('')}
      <div class="divider"></div>
      <div class="row" style="justify-content:space-between;">
        <span class="label-lg">Total estimate</span>
        <span class="price" style="font-size:18px;">${GL.money(grand)}</span>
      </div>
    </div>

    <div class="card">
      <h3 class="heading-sm mb-md">Schedule</h3>
      <div class="list-row"><span class="label-sm text-secondary">Preferred dates</span><span class="label-sm">${sel.from} → ${sel.to}</span></div>
      <div class="list-row"><span class="label-sm text-secondary">Address</span><span class="label-sm" style="text-align:right;">${sel.address}</span></div>
      ${sel.latitude ? `<div class="list-row"><span class="label-sm text-secondary">Location</span><span class="caption text-secondary">${sel.latitude.toFixed(4)}, ${sel.longitude.toFixed(4)}</span></div>` : ''}
    </div>

    <div class="card">
      <label class="field-label label-sm text-secondary" for="notes">Notes (optional)</label>
      <textarea class="textarea" id="notes" placeholder="Model, brand, number of units, special instructions…">${sel.notes || ''}</textarea>
    </div>`;

  submitBtn.addEventListener('click', async function () {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting…';

    const notes = (document.getElementById('notes') || {}).value || '';
    const payload = {
      address: sel.address,
      preferred_date_from: sel.from,
      preferred_date_to: sel.to,
      notes,
      services: Object.keys(sel.services).map(id => ({ service_id: Number(id), quantity: sel.services[id] })),
    };

    // Attach coordinates if we have them; otherwise try Mapbox client-side.
    if (sel.latitude && sel.longitude) {
      payload.latitude = sel.latitude;
      payload.longitude = sel.longitude;
    } else if (window.GL_CONFIG.MAPBOX_TOKEN && window.GL_CONFIG.MAPBOX_TOKEN !== 'YOUR_MAPBOX_TOKEN') {
      try {
        const geo = await fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(sel.address)}.json?limit=1&access_token=${window.GL_CONFIG.MAPBOX_TOKEN}`);
        const g = await geo.json();
        if (g.features && g.features[0]) {
          payload.longitude = g.features[0].center[0];
          payload.latitude = g.features[0].center[1];
        }
      } catch (e) { /* fall through */ }
    }

    if (!payload.latitude || !payload.longitude) {
      GL.toast('We need your location. Go back and tap "Use my current location".');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Submit booking';
      return;
    }

    try {
      const res = await GL.post('/api/bookings/create.php', payload);
      sessionStorage.removeItem('gl_selection');
      window.location.href = 'booking-status.php?id=' + res.booking.id;
    } catch (err) {
      GL.toast(err.message);
      submitBtn.disabled = false;
      submitBtn.textContent = 'Submit booking';
    }
  });
});
</script>
<?php page_end(); ?>