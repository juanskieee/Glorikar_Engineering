<?php
/**
 * client/select-dates.php — Booking flow, step 2: preferred dates + address.
 */

$pageTitle = 'Choose dates';
require __DIR__ . '/../includes/guard.php';
page_start($pageTitle, $GUARD_USER);
page_header('Choose dates', 'book-service.php');
?>
<main class="content">
  <p class="body-sm text-secondary mb-md">Tell us your preferred window and where to send the team.</p>

  <div class="card">
    <div class="field">
      <label class="field-label label-sm text-secondary" for="date_from">Preferred start date</label>
      <input class="input" type="date" id="date_from" required>
      <div class="field-error" id="err_from">Pick a start date.</div>
    </div>

    <div class="field">
      <label class="field-label label-sm text-secondary" for="date_to">Preferred end date</label>
      <input class="input" type="date" id="date_to" required>
      <div class="field-error" id="err_to">Pick an end date on or after the start date.</div>
    </div>

    <div class="field">
      <label class="field-label label-sm text-secondary" for="address">Service address</label>
      <input class="input" type="text" id="address" placeholder="House no., street, city" required>
      <div class="field-error" id="err_addr">Enter a service address.</div>
    </div>

    <div class="row">
      <button class="btn btn-ghost" type="button" id="locate-btn">Use my current location</button>
      <span class="caption text-secondary" id="locate-status"></span>
    </div>
  </div>

  <div class="bottom-bar">
    <button class="btn btn-primary" id="next-btn">Continue →</button>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const sel = JSON.parse(sessionStorage.getItem('gl_selection') || 'null');
  if (!sel || !Object.keys(sel.services || {}).length) {
    window.location.href = 'book-service.php';
    return;
  }

  const from = document.getElementById('date_from');
  const to = document.getElementById('date_to');
  const addr = document.getElementById('address');
  const locateBtn = document.getElementById('locate-btn');
  const locateStatus = document.getElementById('locate-status');

  const today = new Date();
  const iso = today.toISOString().split('T')[0];
  from.min = iso;
  to.min = iso;

  // Prefill if coming back from confirm.
  if (sel.from) from.value = sel.from;
  if (sel.to) to.value = sel.to;
  if (sel.address) addr.value = sel.address;

  function setError(el, visible) {
    el.classList.toggle('visible', visible);
  }

  locateBtn.addEventListener('click', function () {
    if (!navigator.geolocation) {
      locateStatus.textContent = 'Geolocation not supported.';
      return;
    }
    locateStatus.textContent = 'Locating…';
    navigator.geolocation.getCurrentPosition(
      function (pos) {
        sel.latitude = pos.coords.latitude;
        sel.longitude = pos.coords.longitude;
        sessionStorage.setItem('gl_selection', JSON.stringify(sel));
        locateStatus.textContent = 'Location detected ✓';
        if (!addr.value) addr.value = 'My current location';
      },
      function () {
        locateStatus.textContent = 'Could not detect location — enter the address and we will pin it for you.';
      },
      { enableHighAccuracy: true, timeout: 10000 }
    );
  });

  document.getElementById('next-btn').addEventListener('click', function () {
    const dFrom = from.value;
    const dTo = to.value;
    const address = addr.value.trim();
    const okFrom = !!dFrom;
    const okTo = !!dTo && dTo >= dFrom;
    const okAddr = address.length >= 5;

    setError(document.getElementById('err_from'), !okFrom);
    setError(document.getElementById('err_to'), !okTo);
    setError(document.getElementById('err_addr'), !okAddr);

    if (okFrom && okTo && okAddr) {
      sel.from = dFrom;
      sel.to = dTo;
      sel.address = address;
      sessionStorage.setItem('gl_selection', JSON.stringify(sel));
      window.location.href = 'booking-confirm.php';
    }
  });
});
</script>
<?php page_end(); ?>