<?php
/**
 * admin/assign-team.php — assign a registered user to a team.
 */

$pageTitle = 'Assign member';
require __DIR__ . '/../includes/guard.php';
if ($GUARD_ROLE !== 'admin') { http_response_code(403); exit('Forbidden'); }
$teamId = $_GET['team_id'] ?? '';
page_start($pageTitle, $GUARD_USER);
page_header('Assign member', 'teams.php');
?>
<main class="content">
  <div class="card">
    <div class="field">
      <label class="field-label label-sm text-secondary" for="team_select">Team</label>
      <select class="select" id="team_select"></select>
    </div>
    <div class="field">
      <label class="field-label label-sm text-secondary" for="member_select">Technician / member</label>
      <select class="select" id="member_select"></select>
    </div>
    <div class="field">
      <label class="field-label label-sm text-secondary" for="role_select">Role</label>
      <select class="select" id="role_select">
        <option value="technician">Technician</option>
        <option value="lead">Team lead</option>
      </select>
    </div>
    <button class="btn btn-primary" id="save-btn">Add to team</button>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async function () {
  const teamSel = document.getElementById('team_select');
  const memberSel = document.getElementById('member_select');
  const roleSel = document.getElementById('role_select');
  const saveBtn = document.getElementById('save-btn');

  async function load() {
    try {
      const [teams, members] = await Promise.all([
        GL.get('/api/teams/list.php'),
        GL.get('/api/teams/available-members.php?team_id=' + encodeURIComponent(teamId)),
      ]);
      teamSel.innerHTML = teams.teams.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
      memberSel.innerHTML = members.users.map(u => `<option value="${u.id}">${u.full_name} (${u.email})</option>`).join('');

      if (teamId && teams.teams.some(t => t.id === teamId)) teamSel.value = teamId;

      if (!members.users.length) {
        memberSel.innerHTML = '<option value="">No available members — register technicians first</option>';
        memberSel.disabled = true;
      }
    } catch (e) {
      GL.toast('Could not load data.');
    }
  }

  const teamId = <?= json_encode($teamId) ?>;

  saveBtn.addEventListener('click', async function () {
    if (!teamSel.value || !memberSel.value) { GL.toast('Pick a team and a member.'); return; }
    try {
      await GL.post('/api/teams/add-member.php', {
        team_id: teamSel.value,
        user_id: memberSel.value,
        role_tag: roleSel.value,
      });
      GL.toast('Member added.');
      window.location.href = 'teams.php';
    } catch (e) {
      GL.toast(e.message);
    }
  });

  load();
});
</script>
<?php page_end(); ?>