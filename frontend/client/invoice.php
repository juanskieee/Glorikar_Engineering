<?php
/**
 * client/invoice.php — the client's invoice for a booking.
 */

$pageTitle = 'Invoice';
require __DIR__ . '/../includes/guard.php';
page_start($pageTitle, $GUARD_USER);
page_header('Invoice', 'booking-status.php?id=' . e($_GET['id'] ?? ''));
?>
<main class="content">
  <div id="invoice-view">
    <div class="skeleton" style="height:200px;"></div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const params = new URLSearchParams(location.search);
  const id = params.get('id');
  if (!id) { window.location.href = 'my-bookings.php'; return; }

  const el = document.getElementById('invoice-view');

  try {
    const res = await GL.get('/api/jobs/get-invoice.php?booking_id=' + encodeURIComponent(id));
    const inv = res.invoice;
    const b = res.booking;

    el.innerHTML = `
      <div class="card">
        <div class="row" style="justify-content:space-between;">
          <div class="col">
            <span class="display-sm">Invoice</span>
            <span class="caption text-secondary">#${inv.id.slice(0, 8).toUpperCase()}</span>
          </div>
          <span class="status-badge completed">Paid ${inv.paid ? '✓' : '—'}</span>
        </div>

        <div class="divider"></div>

        <div class="list-row"><span class="label-sm text-secondary">Billed to</span><span class="label-sm">${b.client_name}</span></div>
        <div class="list-row"><span class="label-sm text-secondary">Email</span><span class="label-sm">${b.client_email}</span></div>
        <div class="list-row"><span class="label-sm text-secondary">Service address</span><span class="label-sm" style="text-align:right;">${b.address}</span></div>
        <div class="list-row"><span class="label-sm text-secondary">Issued</span><span class="label-sm">${new Date(inv.issued_at.replace(' ', 'T')).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })}</span></div>

        <div class="divider"></div>

        ${res.items.map(i => `
          <div class="list-row">
            <span class="body-sm" style="text-transform:capitalize;">${i.name} × ${i.quantity}</span>
            <span class="label-sm">${GL.money(i.line_total)}</span>
          </div>`).join('')}

        <div class="divider"></div>

        <div class="row" style="justify-content:space-between;">
          <span class="label-lg">Total</span>
          <span class="price" style="font-size:20px;">${GL.money(inv.total_amount)}</span>
        </div>

        ${inv.notes ? `<p class="body-sm text-secondary mt-md">${inv.notes}</p>` : ''}

        <button class="btn btn-primary mt-lg" onclick="window.print()">Print / Save as PDF</button>
      </div>`;
  } catch (e) {
    el.innerHTML = `<?= error_state_html('Invoice not available yet.') ?>`;
  }
});
</script>
<?php page_end(); ?>