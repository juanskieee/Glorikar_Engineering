<?php
/**
 * GET /api/teams/list.php — admin only. List teams with members and availability.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';

$teams = dball(
    'SELECT t.*, (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.id) AS member_count
       FROM teams t ORDER BY t.created_at ASC'
);

foreach ($teams as &$team) {
    $team['members'] = dball(
        'SELECT tm.id AS member_id, tm.role_tag, u.id AS user_id, u.full_name, u.email, u.phone
           FROM team_members tm JOIN users u ON u.id = tm.user_id
          WHERE tm.team_id = ?',
        [$team['id']]
    );
}
unset($team);

json_response(['ok' => true, 'teams' => $teams]);