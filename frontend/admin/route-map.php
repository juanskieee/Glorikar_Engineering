<?php
/**
 * admin/route-map.php — Admin Route Map. Browse schedules by status,
 * and view any schedule's route (depot + stops) on a live Mapbox map.
 */

$pageTitle = 'Route Map';
require __DIR__ . '/../includes/guard.php';
if ($GUARD_ROLE !== 'admin') { http_response_code(403); exit('Forbidden'); }
page_start($pageTitle, $GUARD_USER);
page_header('Route Map');
?>
<main class="content">
  <div class="row wrap mb-md">
    <button class="btn btn-ghost btn-sm" data-status="draft">Drafts</button>
    <button class="btn btn-ghost btn-sm" data-status="approved">Approved</button>
    <button class="btn btn-ghost btn-sm" data-status="dispatched">Dispatched</button>
    <button class="btn btn-ghost btn-sm" data-status="done">Done</button>
  </div>

  <div id="map-panel" class="hidden card" style="padding:0;margin-bottom:var(--sp-md);overflow:hidden;">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-bottom:1px solid var(--border);">
      <span class="heading-sm" id="map-title">Route</span>
      <button class="btn btn-ghost btn-sm" type="button" id="map-close">Close map</button>
    </div>
    <div id="map" style="height:420px;background:var(--surface);"></div>
  </div>

  <div id="schedule-list">
    <div class="skeleton" style="height:90px;margin-bottom:12px;"></div>
    <div class="skeleton" style="height:90px;margin-bottom:12px;"></div>
    <div class="skeleton" style="height:90px;"></div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const listEl = document.getElementById('schedule-list');
  const buttons = document.querySelectorAll('[data-status]');
  const mapPanel = document.getElementById('map-panel');
  const mapTitle = document.getElementById('map-title');
  const mapContainer = document.getElementById('map');
  const mapClose = document.getElementById('map-close');
  let current = 'draft';

  const MAPBOX_TOKEN = window.GL_CONFIG.MAPBOX_TOKEN;
  const hasToken = MAPBOX_TOKEN && MAPBOX_TOKEN !== 'YOUR_MAPBOX_TOKEN';

  buttons.forEach(btn => btn.addEventListener('click', () => {
    current = btn.dataset.status;
    buttons.forEach(b => b.style.borderColor = b === btn ? 'var(--border-focus)' : '');
    load();
  }));

  if (mapClose) mapClose.addEventListener('click', () => mapPanel.classList.add('hidden'));

  async function load() {
    listEl.innerHTML = '<div class="skeleton" style="height:90px;"></div>';
    try {
      const res = await GL.get('/api/schedule/drafts.php?status=' + current);
      const schedules = res.schedules || [];
      if (!schedules.length) {
        listEl.innerHTML = `<?= empty_state_html('map', 'No schedules here', 'Run the scheduling engine to generate draft routes.') ?>`;
        return;
      }
      listEl.innerHTML = schedules.map(s => `
        <div class="card" style="cursor:pointer;" onclick="location.href='schedule-detail.php?id=${s.id}'">
          <div class="row" style="justify-content:space-between;">
            <span class="heading-sm">${s.scheduled_date} · ${s.team_name}</span>
            <span class="status-badge ${s.status}">${s.status}</span>
          </div>
          <p class="body-sm text-secondary mt-sm">${s.stop_count} stop${s.stop_count === 1 ? '' : 's'} · ${s.vehicle || 'no vehicle'}</p>
          ${s.total_distance_km != null ? `<p class="caption mt-sm" style="color:var(--accent);">${Number(s.total_distance_km).toFixed(1)} km route</p>` : ''}
          <div class="row mt-sm">
            <a class="btn btn-ghost btn-sm grow" href="schedule-detail.php?id=${s.id}">Details</a>
            ${hasToken ? `<button class="btn btn-primary btn-sm" type="button" data-map-id="${s.id}" data-map-name="${s.scheduled_date} · ${s.team_name}">View map</button>` : ''}
          </div>
        </div>`).join('');

      if (hasToken) {
        listEl.querySelectorAll('[data-map-id]').forEach(btn => btn.addEventListener('click', (e) => {
          e.stopPropagation();
          e.preventDefault();
          showRoute(btn.dataset.mapId, btn.dataset.mapName);
        }));
      }
    } catch (e) {
      listEl.innerHTML = `<?= error_state_html('Could not load schedules.') ?>`;
    }
  }

  async function showRoute(scheduleId, name) {
    if (mapTitle) mapTitle.textContent = name;
    mapPanel.classList.remove('hidden');
    mapContainer.innerHTML = '<div class="skeleton" style="height:420px;"></div>';

    let res;
    try {
      res = await GL.get('/api/schedule/get.php?id=' + scheduleId);
    } catch (e) {
      mapContainer.innerHTML = `<div class="empty-state"><p class="heading-sm">Could not load route.</p><p class="body-sm text-secondary">${e.message}</p></div>`;
      return;
    }

    const stops = (res.schedule && res.schedule.stops) || [];
    if (!stops.length) {
      mapContainer.innerHTML = `<div class="empty-state"><p class="heading-sm">No stops on this route.</p></div>`;
      return;
    }

    ensureMapbox(() => {
      const coords = stops
        .filter(st => st.latitude != null && st.longitude != null)
        .sort((a, b) => (a.stop_order || 0) - (b.stop_order || 0))
        .map(st => [st.longitude, st.latitude]);

      if (!coords.length) {
        mapContainer.innerHTML = `<div class="empty-state"><p class="heading-sm">Stops are missing coordinates.</p></div>`;
        return;
      }

      const depot = <?= json_encode([(float)\Env::get('DEPOT_LNG', 120.9745), (float)\Env::get('DEPOT_LAT', 14.3001)]) ?>;
      const line = [depot, ...coords];

      const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/dark-v11',
        center: depot,
        zoom: 11,
        attributionControl: false
      });
      map.addControl(new mapboxgl.AttributionControl({ compact: true }), 'bottom-left');

      map.on('load', () => {
        const bounds = new mapboxgl.LngLatBounds();
        line.forEach(c => bounds.extend(c));

        map.addSource('route', {
          type: 'geojson',
          data: { type: 'Feature', properties: {}, geometry: { type: 'LineString', coordinates: line } }
        });
        map.addLayer({
          id: 'route-line',
          type: 'line',
          source: 'route',
          paint: { 'line-color': '#0EA5E9', 'line-width': 4, 'line-opacity': 0.9 }
        });
        map.addLayer({
          id: 'route-casing',
          type: 'line',
          source: 'route',
          paint: { 'line-color': '#0F172A', 'line-width': 8, 'line-opacity': 0.35 }
        });

        // Depot marker.
        new mapboxgl.Marker({ color: '#0EA5E9' })
          .setLngLat(depot)
          .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML('<b>Shop / depot</b>'))
          .addTo(map);

        // Stop markers with order labels.
        coords.forEach((c, i) => {
          const st = stops[i];
          const el = document.createElement('div');
          el.className = 'map-stop';
          el.style.cssText = 'width:26px;height:26px;border-radius:50%;background:#0EA5E9;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;border:2px solid #0F172A;';
          el.textContent = i + 1;
          new mapboxgl.Marker({ element: el })
            .setLngLat(c)
            .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML(`<b>${(i + 1)}. ${st.client_name || 'Client'}</b><br>${st.address || ''}<br>ETA ${st.eta || '—'}`))
            .addTo(map);
        });

        map.fitBounds(bounds, { padding: 50 });
      });
    });
  }

  function ensureMapbox(cb) {
    if (window.mapboxgl) { cb(); return; }
    const css = document.createElement('link');
    css.rel = 'stylesheet';
    css.href = 'https://api.mapbox.com/mapbox-gl-js/v3.7.0/mapbox-gl.css';
    document.head.appendChild(css);

    const s = document.createElement('script');
    s.src = 'https://api.mapbox.com/mapbox-gl-js/v3.7.0/mapbox-gl.js';
    s.onload = cb;
    s.onerror = () => { mapContainer.innerHTML = `<div class="empty-state"><p class="heading-sm">Mapbox failed to load.</p></div>`; };
    document.head.appendChild(s);
  }

  load();
});
</script>
<?php page_end(); ?>