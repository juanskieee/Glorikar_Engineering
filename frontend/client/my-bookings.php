<?php
/**
 * client/my-bookings.php — list of the client's bookings.
 */

$pageTitle = 'My Bookings';
require __DIR__ . '/../includes/guard.php';
page_start($pageTitle, $GUARD_USER);
page_header('My Bookings');
?>
<main class="content">
  <div id="bookings-list">
    <div class="skeleton" style="height:80px;margin-bottom:12px;"></div>
    <div class="skeleton" style="height:80px;margin-bottom:12px;"></div>
    <div class="skeleton" style="height:80px;"></div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const el = document.getElementById('bookings-list');

  try {
    const res = await GL.get('/api/bookings/mine.php');
    const bookings = res.bookings || [];

    if (!bookings.length) {
      el.innerHTML = `<?= empty_state_html('inbox', 'No bookings yet', 'Book your first aircon service from Home.') ?>`;
      return;
    }

    el.innerHTML = bookings.map(function (b) {
      return `
        <div class="card" style="cursor:pointer;" onclick="location.href='booking-status.php?id=${b.id}'">
          <div class="row" style="justify-content:space-between;">
            <span class="heading-sm">${b.services.map(s => s.name).join(' + ')}</span>
            <span class="status-badge ${b.status}">${GL.statusLabel(b.status)}</span>
          </div>
          <p class="body-sm text-secondary mt-sm">${b.address}</p>
          <div class="row mt-sm" style="justify-content:space-between;">
            <span class="caption text-secondary">${b.preferred_date_from}${b.preferred_date_to !== b.preferred_date_from ? ' → ' + b.preferred_date_to : ''}</span>
            <span class="price">${GL.money(b.total)}</span>
          </div>
          ${b.scheduled_date ? `<p class="caption mt-sm" style="color:var(--status-en-route);">Scheduled: ${b.scheduled_date}</p>` : ''}
        </div>`;
    }).join('');
  } catch (e) {
    el.innerHTML = `<?= error_state_html('Could not load your bookings.') ?>`;
  }
});
</script>
<?php page_end(); ?>