<?php
require __DIR__ . '/../backend/includes/auth-guard.php';
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#0EA5E9">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>Select Dates — Glorikar Engineering</title>
  <link rel="stylesheet" href="../assets/css/theme.css">
  <link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/layout.css">
</head>
<body>
<div class="app-shell">
  <nav class="sidebar" id="sidebar"></nav>
  <main class="main-content">

    <header class="page-header">
      <div class="page-header-left">
        <a href="book-service.php" class="btn-icon" aria-label="Back">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div>
          <div class="page-title">Preferred Dates</div>
          <div class="page-subtitle">Give us a window to schedule your visit</div>
        </div>
      </div>
      <div class="step-indicator">
        <div class="step-indicator-item active">
          <div class="step-dot complete">✓</div>
        </div>
        <div class="step-indicator-item active">
          <div class="step-dot active">2</div>
        </div>
        <div class="step-indicator-item">
          <div class="step-dot">3</div>
        </div>
      </div>
    </header>

    <div class="page">
      <div class="body-sm text-secondary mb-md">
        Select a start and end date. Our scheduling engine will find the best day within that window.
      </div>

      <!-- Calendar -->
      <div class="card">
        <div class="row-between mb-md">
          <button class="btn-icon" id="prev-month" aria-label="Previous month">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <div class="heading-sm" id="month-label"></div>
          <button class="btn-icon" id="next-month" aria-label="Next month">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>

        <div class="date-grid" id="day-headers">
          <div class="date-cell-header" style="display:grid;place-content:center;padding:var(--sp-xs)">Su</div>
          <div class="date-cell-header" style="display:grid;place-content:center;padding:var(--sp-xs)">Mo</div>
          <div class="date-cell-header" style="display:grid;place-content:center;padding:var(--sp-xs)">Tu</div>
          <div class="date-cell-header" style="display:grid;place-content:center;padding:var(--sp-xs)">We</div>
          <div class="date-cell-header" style="display:grid;place-content:center;padding:var(--sp-xs)">Th</div>
          <div class="date-cell-header" style="display:grid;place-content:center;padding:var(--sp-xs)">Fr</div>
          <div class="date-cell-header" style="display:grid;place-content:center;padding:var(--sp-xs)">Sa</div>
        </div>

        <div class="date-grid mt-sm" id="cal-grid" role="grid" aria-label="Date picker"></div>
      </div>

      <!-- Selected range display -->
      <div class="card mt-md" id="range-display">
        <div class="grid-2" style="gap:var(--sp-md)">
          <div>
            <div class="label-sm text-secondary">From</div>
            <div class="heading-sm mt-xs" id="from-label" style="color:var(--text-disabled)">Not selected</div>
          </div>
          <div>
            <div class="label-sm text-secondary">To</div>
            <div class="heading-sm mt-xs" id="to-label" style="color:var(--text-disabled)">Not selected</div>
          </div>
        </div>
        <div class="body-sm text-secondary mt-sm" id="range-note" style="display:none">
          The wider your window, the faster we can fit you in.
        </div>
      </div>

      <!-- Service address override -->
      <div class="form-group mt-md">
        <label class="form-label" for="service-address">Service address</label>
        <input class="input" type="text" id="service-address"
               placeholder="Leave blank to use your account address">
        <span class="caption text-secondary">Only needed if different from your registered address.</span>
      </div>

      <div style="height: var(--sp-xxl)"></div>
    </div>

  </main>
  <nav class="bottom-nav" id="bottom-nav"></nav>
</div>

<div class="action-bar">
  <div class="flex-1">
    <div class="label-sm text-secondary" id="selected-summary">Select a date range</div>
  </div>
  <button class="btn btn-primary" id="next-btn" disabled>Review booking →</button>
</div>

<script type="module">
import { requireAuth } from '../assets/js/auth.js';
import { renderNav } from '../assets/js/nav.js';

const user = await requireAuth();
renderNav();

// Ensure draft exists
const draft = JSON.parse(sessionStorage.getItem('booking_draft') || 'null');
if (!draft) { window.location.href = 'book-service.php'; }

// Pre-fill address
document.getElementById('service-address').value = user?.address || '';

// Calendar state
let viewYear, viewMonth, dateFrom = null, dateTo = null;
const today = new Date();
today.setHours(0,0,0,0);
const minDate = new Date(today); minDate.setDate(today.getDate() + 3); // 3-day minimum lead

viewYear  = minDate.getFullYear();
viewMonth = minDate.getMonth();

const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const fmt = d => d ? d.toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}) : 'Not selected';

function renderCalendar() {
  document.getElementById('month-label').textContent = `${months[viewMonth]} ${viewYear}`;
  const grid = document.getElementById('cal-grid');
  grid.innerHTML = '';

  const first = new Date(viewYear, viewMonth, 1);
  const last  = new Date(viewYear, viewMonth + 1, 0);

  // Blank cells before first day
  for (let i = 0; i < first.getDay(); i++) {
    const blank = document.createElement('div');
    blank.style.cssText = 'aspect-ratio:1';
    grid.appendChild(blank);
  }

  for (let d = 1; d <= last.getDate(); d++) {
    const date = new Date(viewYear, viewMonth, d);
    const cell = document.createElement('button');
    cell.className = 'date-cell';
    cell.textContent = d;

    const isPast = date < minDate;
    if (isPast) {
      cell.classList.add('disabled');
      cell.disabled = true;
    } else {
      const isFrom = dateFrom && date.getTime() === dateFrom.getTime();
      const isTo   = dateTo   && date.getTime() === dateTo.getTime();
      const isRange = dateFrom && dateTo && date > dateFrom && date < dateTo;

      if (isFrom || isTo) cell.classList.add('selected');
      if (isRange) cell.classList.add('in-range');

      cell.setAttribute('aria-label', fmt(date));
      cell.addEventListener('click', () => handleDateClick(date));
    }

    grid.appendChild(cell);
  }
}

function handleDateClick(date) {
  if (!dateFrom || (dateFrom && dateTo)) {
    dateFrom = date; dateTo = null;
  } else {
    if (date < dateFrom) { dateTo = dateFrom; dateFrom = date; }
    else if (date.getTime() === dateFrom.getTime()) { dateFrom = null; }
    else { dateTo = date; }
  }
  updateDisplay();
  renderCalendar();
}

function updateDisplay() {
  document.getElementById('from-label').textContent = dateFrom ? fmt(dateFrom) : 'Not selected';
  document.getElementById('from-label').style.color = dateFrom ? 'var(--text-primary)' : 'var(--text-disabled)';
  document.getElementById('to-label').textContent = dateTo ? fmt(dateTo) : 'Not selected';
  document.getElementById('to-label').style.color = dateTo ? 'var(--text-primary)' : 'var(--text-disabled)';

  const hasRange = dateFrom && dateTo;
  document.getElementById('range-note').style.display = hasRange ? 'block' : 'none';
  document.getElementById('next-btn').disabled = !dateFrom;

  if (dateFrom && dateTo) {
    const days = Math.round((dateTo - dateFrom) / 86400000) + 1;
    document.getElementById('selected-summary').textContent = `${days}-day window selected`;
  } else if (dateFrom) {
    document.getElementById('selected-summary').textContent = `From ${fmt(dateFrom)} — pick an end date`;
  } else {
    document.getElementById('selected-summary').textContent = 'Select a date range';
  }
}

document.getElementById('prev-month').addEventListener('click', () => {
  viewMonth--; if (viewMonth < 0) { viewMonth = 11; viewYear--; }
  renderCalendar();
});
document.getElementById('next-month').addEventListener('click', () => {
  viewMonth++; if (viewMonth > 11) { viewMonth = 0; viewYear++; }
  renderCalendar();
});

document.getElementById('next-btn').addEventListener('click', () => {
  const toDate = dateTo || dateFrom; // single day if no end
  sessionStorage.setItem('booking_draft', JSON.stringify({
    ...draft,
    preferred_date_from: dateFrom.toISOString().split('T')[0],
    preferred_date_to:   toDate.toISOString().split('T')[0],
    address: document.getElementById('service-address').value.trim() || user?.address || '',
  }));
  window.location.href = 'booking-confirm.php';
});

renderCalendar();
updateDisplay();
</script>
</body>
</html>
