<?php
/**
 * admin/invoice.php — view or generate an invoice for a booking.
 */

$pageTitle = 'Invoice';
require __DIR__ . '/../includes/guard.php';
if ($GUARD_ROLE !== 'admin') { http_response_code(403); exit('Forbidden'); }
$bookingId = $_GET['id'] ?? '';
page_start($pageTitle, $GUARD_USER);
page_header('Invoice');
?>
<main class="content">
  <div id="invoice-view">
    <div class="skeleton" style="height:200px;"></div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const bookingId = <?= json_encode($bookingId) ?>;
  if (!bookingId) { window.location.href = 'route-map.php'; return; }

  const el = document.getElementById('invoice-view');

  async function render() {
    try {
      const res = await GL.get('/api/jobs/get-invoice.php?booking_id=' + encodeURIComponent(bookingId));
      const inv = res.invoice;
      const b = res.booking;

      el.innerHTML = `
        <div class="card">
          <div class="row" style="justify-content:space-between;">
            <div class="col">
              <span class="display-sm">Invoice</span>
              <span class="caption text-secondary">#${inv.id.slice(0, 8).toUpperCase()}</span>
            </div>
            <span class="status-badge ${inv.paid ? 'completed' : 'pending'}">${inv.paid ? 'Paid' : 'Unpaid'}</span>
          </div>
          <div class="divider"></div>
          <div class="list-row"><span class="label-sm text-secondary">Client</span><span class="label-sm">${b.client_name}</span></div>
          <div class="list-row"><span class="label-sm text-secondary">Email</span><span class="label-sm">${b.client_email}</span></div>
          <div class="list-row"><span class="label-sm text-secondary">Address</span><span class="label-sm" style="text-align:right;">${b.address}</span></div>
          <div class="list-row"><span class="label-sm text-secondary">Issued</span><span class="label-sm">${new Date(inv.issued_at.replace(' ', 'T')).toLocaleString('en-PH')}</span></div>
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
        </div>
        <button class="btn btn-ghost mt-lg" onclick="window.print()">Print / Save as PDF</button>`;
    } catch (e) {
      if (e.status === 404) {
        el.innerHTML = `
          <div class="card" style="border-color:var(--status-pending);">
            <span class="heading-sm">No invoice yet</span>
            <p class="body-sm text-secondary mt-sm">Complete this job to generate the invoice.</p>
            <button class="btn btn-primary mt-md" onclick="window.location.href='job-complete.php?id=${bookingId}'">Go to job</button>
          </div>`;
      } else {
        el.innerHTML = `<?= error_state_html('Could not load invoice.') ?>`;
      }
    }
  }

  render();
});
</script>
<?php page_end(); ?>