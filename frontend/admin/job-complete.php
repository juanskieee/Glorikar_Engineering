<?php
/**
 * admin/job-complete.php — complete a job + upload before/after photos.
 */

$pageTitle = 'Complete job';
require __DIR__ . '/../includes/guard.php';
if ($GUARD_ROLE !== 'admin') { http_response_code(403); exit('Forbidden'); }
$bookingId = $_GET['id'] ?? '';
page_start($pageTitle, $GUARD_USER);
page_header('Complete job', 'route-map.php');
?>
<main class="content">
  <div id="job-view">
    <div class="skeleton" style="height:140px;margin-bottom:16px;"></div>
    <div class="skeleton" style="height:160px;"></div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const bookingId = <?= json_encode($bookingId) ?>;
  if (!bookingId) { window.location.href = 'route-map.php'; return; }

  const el = document.getElementById('job-view');

  try {
    const res = await GL.get('/api/bookings/get.php?id=' + encodeURIComponent(bookingId));
    const b = res.booking;

    el.innerHTML = `
      <div class="card">
        <div class="row" style="justify-content:space-between;">
          <span class="heading-lg">${b.client ? b.client.name : 'Job'}</span>
          <span class="status-badge ${b.status}">${GL.statusLabel(b.status)}</span>
        </div>
        <p class="body-sm text-secondary mt-sm">${b.address}</p>
        <div class="row mt-sm" style="justify-content:space-between;">
          <span class="caption text-secondary">${b.preferred_date_from} → ${b.preferred_date_to}</span>
          <span class="price">${GL.money(b.total)}</span>
        </div>
        ${b.notes ? `<p class="body-sm text-secondary mt-sm">Notes: ${b.notes}</p>` : ''}
      </div>

      ${b.status !== 'completed' ? `
        <div class="card mt-md">
          <h3 class="heading-sm mb-md">Photos</h3>
          <div class="row wrap">
            <label class="btn btn-ghost btn-sm">
              Before photos
              <input type="file" accept="image/jpeg,image/png,image/webp" multiple hidden data-photos="before">
            </label>
            <label class="btn btn-ghost btn-sm">
              After photos
              <input type="file" accept="image/jpeg,image/png,image/webp" multiple hidden data-photos="after">
            </label>
          </div>
          <div id="photo-preview" class="photo-grid mt-md"></div>
          <div class="divider"></div>
          <button class="btn btn-primary" id="complete-btn">Mark completed &amp; generate invoice</button>
        </div>` : `
        <div class="card mt-md">
          <p class="body-sm" style="color:var(--status-completed);">This job is complete.</p>
          <button class="btn btn-ghost mt-md" onclick="window.location.href='invoice.php?id=${b.id}'">View invoice</button>
        </div>`}

      ${b.photos && b.photos.length ? `
        <div class="card mt-md">
          <h3 class="heading-sm mb-md">Uploaded photos</h3>
          <div class="photo-grid">
            ${b.photos.map(p => `<div class="photo-tile"><span class="photo-label">${p.type}</span><img src="${p.photo_url}" onerror="this.style.display='none'"></div>`).join('')}
          </div>
        </div>` : ''}
    `;

    // --- Photo handling ---
    let pendingPhotos = []; // { file, type }
    const previewEl = document.getElementById('photo-preview');

    document.querySelectorAll('[data-photos]').forEach(input => {
      input.addEventListener('change', () => {
        for (const file of input.files) {
          pendingPhotos.push({ file, type: input.dataset.photos });
          const reader = new FileReader();
          reader.onload = ev => {
            const tile = document.createElement('div');
            tile.className = 'photo-tile';
            tile.innerHTML = `<span class="photo-label">${input.dataset.photos}</span><img src="${ev.target.result}">`;
            previewEl.appendChild(tile);
          };
          reader.readAsDataURL(file);
        }
        input.value = '';
      });
    });

    const completeBtn = document.getElementById('complete-btn');
    if (completeBtn) completeBtn.addEventListener('click', async function () {
      completeBtn.disabled = true;
      completeBtn.textContent = 'Working…';

      // 1. Upload photos first (best-effort).
      for (const p of pendingPhotos) {
        const fd = new FormData();
        fd.append('booking_id', bookingId);
        fd.append('type', p.type);
        fd.append('photos[]', p.file);
        try {
          await GL.post('/api/jobs/upload-photos.php', fd);
        } catch (e) {
          GL.toast('Photo upload failed: ' + e.message);
        }
      }

      // 2. Mark complete (generates the invoice).
      try {
        const res = await GL.post('/api/jobs/complete.php', { booking_id: bookingId, notes: '' });
        GL.toast('Job completed — invoice generated.');
        window.location.href = 'invoice.php?id=' + bookingId;
      } catch (e) {
        GL.toast(e.message);
        completeBtn.disabled = false;
        completeBtn.textContent = 'Mark completed & generate invoice';
      }
    });

  } catch (e) {
    el.innerHTML = `<?= error_state_html('Could not load this job.') ?>`;
  }
});
</script>
<?php page_end(); ?>