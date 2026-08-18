<?php
/**
 * admin/teams.php — Admin team management: list, create, availability, assign.
 */

$pageTitle = 'Teams';
require __DIR__ . '/../includes/guard.php';
if ($GUARD_ROLE !== 'admin') { http_response_code(403); exit('Forbidden'); }
page_start($pageTitle, $GUARD_USER);
page_header('Teams');
?>
<main class="content">
  <button class="btn btn-primary mb-md" id="new-team-btn">+ New team</button>

  <div id="teams-list">
    <div class="skeleton" style="height:80px;margin-bottom:12px;"></div>
    <div class="skeleton" style="height:80px;margin-bottom:12px;"></div>
    <div class="skeleton" style="height:80px;"></div>
  </div>

  <div id="new-team-form" class="card hidden">
    <h3 class="heading-sm mb-md">Create team</h3>
    <div class="field">
      <label class="field-label label-sm text-secondary" for="team_name">Team name</label>
      <input class="input" type="text" id="team_name" placeholder="Team Delta">
    </div>
    <div class="field">
      <label class="field-label label-sm text-secondary" for="team_vehicle">Vehicle (optional)</label>
      <input class="input" type="text" id="team_vehicle" placeholder="Hiace TBD-000">
    </div>
    <div class="row">
      <button class="btn btn-primary grow" id="save-team-btn">Save</button>
      <button class="btn btn-ghost grow" id="cancel-team-btn">Cancel</button>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const listEl = document.getElementById('teams-list');
  const newBtn = document.getElementById('new-team-btn');
  const formEl = document.getElementById('new-team-form');

  async function load() {
    try {
      const res = await GL.get('/api/teams/list.php');
      const teams = res.teams || [];
      if (!teams.length) {
        listEl.innerHTML = `<?= empty_state_html('users', 'No teams yet', 'Create your first team to start assigning work.') ?>`;
        return;
      }
      listEl.innerHTML = teams.map(t => `
        <div class="card">
          <div class="row" style="justify-content:space-between;">
            <span class="heading-sm">${t.name}</span>
            <span class="status-badge ${t.is_available ? 'completed' : 'cancelled'}">${t.is_available ? 'Available' : 'Off duty'}</span>
          </div>
          <p class="body-sm text-secondary mt-sm">${t.vehicle || 'No vehicle'} · ${t.member_count} member${t.member_count === 1 ? '' : 's'}</p>
          ${t.members && t.members.length ? `
            <div class="mt-sm">
              ${t.members.map(m => `<span class="caption text-secondary" style="display:inline-block;background:var(--surface-raised);border-radius:999px;padding:2px 10px;margin:2px 4px 2px 0;">${m.full_name} <b style="color:var(--text-primary);">${m.role_tag}</b></span>`).join('')}
            </div>` : '<p class="caption mt-sm" style="color:var(--status-pending);">No members yet</p>'}
          <div class="row mt-md">
            <button class="btn btn-ghost btn-sm" data-assign="${t.id}">Assign member</button>
            <button class="btn btn-ghost btn-sm" data-toggle="${t.id}">${t.is_available ? 'Mark off duty' : 'Mark available'}</button>
          </div>
        </div>`).join('');
    } catch (e) {
      listEl.innerHTML = `<?= error_state_html('Could not load teams.') ?>`;
    }
  }

  listEl.addEventListener('click', async function (ev) {
    const assign = ev.target.closest('[data-assign]');
    if (assign) { window.location.href = 'assign-team.php?team_id=' + assign.dataset.assign; return; }

    const toggle = ev.target.closest('[data-toggle]');
    if (toggle) {
      try {
        const t = (await GL.get('/api/teams/list.php')).teams.find(x => x.id === toggle.dataset.toggle);
        await GL.patch('/api/teams/update.php', { id: toggle.dataset.toggle, is_available: !t.is_available });
        load();
      } catch (e) { GL.toast(e.message); }
    }
  });

  newBtn.addEventListener('click', () => formEl.classList.toggle('hidden'));
  document.getElementById('cancel-team-btn').addEventListener('click', () => formEl.classList.add('hidden'));
  document.getElementById('save-team-btn').addEventListener('click', async function () {
    const name = document.getElementById('team_name').value.trim();
    const vehicle = document.getElementById('team_vehicle').value.trim();
    if (name.length < 2) { GL.toast('Enter a team name.'); return; }
    try {
      await GL.post('/api/teams/create.php', { name, vehicle });
      formEl.classList.add('hidden');
      document.getElementById('team_name').value = '';
      document.getElementById('team_vehicle').value = '';
      GL.toast('Team created.');
      load();
    } catch (e) { GL.toast(e.message); }
  });

  load();
});
</script>
<?php page_end(); ?>