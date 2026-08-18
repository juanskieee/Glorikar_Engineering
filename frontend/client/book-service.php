<?php
/**
 * client/book-service.php — Booking flow, step 1: choose services & quantities.
 */

$pageTitle = 'Book a service';
require __DIR__ . '/../includes/guard.php';
page_start($pageTitle, $GUARD_USER);
page_header('Book a service', 'home.php');
?>
<main class="content">
  <p class="body-sm text-secondary mb-md">Select the services you need. You can add more than one, and set how many units.</p>

  <div id="services-list">
    <div class="skeleton" style="height:88px;margin-bottom:8px;"></div>
    <div class="skeleton" style="height:88px;margin-bottom:8px;"></div>
    <div class="skeleton" style="height:88px;margin-bottom:8px;"></div>
    <div class="skeleton" style="height:88px;"></div>
  </div>

  <div class="bottom-bar">
    <button class="btn btn-primary" id="continue-btn" disabled>Continue →</button>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const listEl = document.getElementById('services-list');
  const continueBtn = document.getElementById('continue-btn');

  // Selections persisted across the multi-step flow.
  let selection = JSON.parse(sessionStorage.getItem('gl_selection') || 'null') || { services: {}, address: '', from: '', to: '', notes: '' };
  if (!selection.services) selection.services = {};

  let services = [];
  try {
    const res = await GL.get('/api/services/list.php');
    services = res.services;
  } catch (e) {
    listEl.innerHTML = `<?= error_state_html('Could not load services.') ?>`;
    return;
  }

  function render() {
    listEl.innerHTML = services.map(function (s) {
      const qty = selection.services[s.id] || 0;
      const selected = qty > 0;
      return `
        <div class="service-option ${selected ? 'selected' : ''}" data-id="${s.id}" data-selected="${selected}">
          <div class="col grow">
            <span class="heading-sm" style="text-transform:capitalize;">${s.name}</span>
            <span class="caption text-secondary">~${s.duration_hrs} hr${s.duration_hrs > 1 ? 's' : ''} · ${GL.money(s.base_price)}/unit</span>
            ${selected ? `
              <div class="stepper mt-sm">
                <button type="button" data-dec="${s.id}">−</button>
                <span class="stepper-value">${qty}</span>
                <button type="button" data-inc="${s.id}">+</button>
              </div>` : ''}
          </div>
          <span class="price">${selected ? GL.money(s.base_price * qty) : ''}</span>
        </div>`;
    }).join('');
    updateTotal();
  }

  function updateTotal() {
    const count = Object.keys(selection.services).length;
    continueBtn.disabled = count === 0;
  }

  listEl.addEventListener('click', function (e) {
    const id = e.target.closest('[data-id]')?.dataset.id;
    if (!id) return;
    if (e.target.closest('[data-inc]')) {
      selection.services[id] = (selection.services[id] || 0) + 1;
    } else if (e.target.closest('[data-dec]')) {
      selection.services[id] = Math.max(0, (selection.services[id] || 0) - 1);
      if (selection.services[id] === 0) delete selection.services[id];
    } else {
      // Tap the row itself: toggle on (qty 1) or start stepper.
      selection.services[id] = selection.services[id] ? selection.services[id] : 1;
    }
    sessionStorage.setItem('gl_selection', JSON.stringify(selection));
    render();
  });

  continueBtn.addEventListener('click', function () {
    sessionStorage.setItem('gl_selection', JSON.stringify(selection));
    window.location.href = 'select-dates.php';
  });

  render();
});
</script>
<?php page_end(); ?>