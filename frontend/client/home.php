<?php
/**
 * client/home.php — Client Home.
 */

$pageTitle = 'Home';
require __DIR__ . '/../includes/guard.php';
page_start($pageTitle, $GUARD_USER);
?>
<div class="page-header">
  <h1 class="page-title display-sm">Good day, <?= e(explode(' ', $GUARD_USER['full_name'])[0]) ?> 👋</h1>
</div>

<main class="content">
  <section>
    <h2 class="section-header">Our Services</h2>
    <div id="services-list">
      <div class="skeleton" style="height:76px;margin-bottom:8px;"></div>
      <div class="skeleton" style="height:76px;margin-bottom:8px;"></div>
      <div class="skeleton" style="height:76px;"></div>
    </div>
  </section>

  <section class="mt-lg">
    <h2 class="section-header">Your Bookings</h2>
    <div id="recent-bookings">
      <div class="skeleton" style="height:64px;margin-bottom:8px;"></div>
      <div class="skeleton" style="height:64px;"></div>
    </div>
  </section>

  <button class="btn btn-primary mt-lg" onclick="location.href='book-service.php'">
    Book a service
  </button>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const servicesEl = document.getElementById('services-list');
  const bookingsEl = document.getElementById('recent-bookings');

  try {
    const svc = await GL.get('/api/services/list.php');
    servicesEl.innerHTML = svc.services.map(function (s) {
      return `
        <div class="service-option" onclick="location.href='book-service.php?s=${s.id}'">
          <div class="radio-dot"></div>
          <div class="col grow">
            <span class="heading-sm" style="text-transform:capitalize;">${s.name}</span>
            <span class="caption text-secondary">~${s.duration_hrs} hr${s.duration_hrs > 1 ? 's' : ''} per unit</span>
          </div>
          <span class="price">${GL.money(s.base_price)}</span>
        </div>`;
    }).join('');
  } catch (e) {
    servicesEl.innerHTML = `<?= error_state_html('Could not load services.') ?>`;
  }

  try {
    const mine = await GL.get('/api/bookings/mine.php');
    const recent = (mine.bookings || []).slice(0, 3);
    if (!recent.length) {
      bookingsEl.innerHTML = `<?= empty_state_html('calendar', 'No bookings yet', 'Book your first aircon service today.') ?>`;
    } else {
      bookingsEl.innerHTML = recent.map(function (b) {
        const total = b.services.reduce((t, s) => t + (Number(s.base_price) * s.quantity), 0);
        return `
          <div class="list-row" onclick="location.href='booking-status.php?id=${b.id}'" style="cursor:pointer;">
            <div class="list-row-left">
              <p class="list-title heading-sm">${b.services.map(s => s.name).join(' + ')}</p>
              <p class="list-sub caption text-secondary">${b.preferred_date_from} · ${GL.statusLabel(b.status)}</p>
            </div>
            <div class="list-row-right">
              <span class="price">${GL.money(total)}</span>
              <span class="status-badge ${b.status}">${GL.statusLabel(b.status)}</span>
            </div>
          </div>`;
      }).join('');
    }
  } catch (e) {
    bookingsEl.innerHTML = `<?= error_state_html('Could not load your bookings.') ?>`;
  }
});
</script>
<?php page_end(); ?>