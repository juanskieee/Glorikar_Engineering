<?php
/**
 * POST /api/teams/add-member.php — admin only. Add a user as team member.
 * Requires the user to exist (must be registered first).
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

csrf_verify();

$data = read_json_input();
$teamId = $data['team_id'] ?? '';
$userId = $data['user_id'] ?? '';
$roleTag = $data['role_tag'] ?? 'technician';

if (!preg_match('/^[0-9a-fA-F-]{36}$/', $teamId) || !preg_match('/^[0-9a-fA-F-]{36}$/', $userId)) {
    json_error('Invalid team or user.', 422);
}
if (!in_array($roleTag, ['lead', 'technician'], true)) {
    json_error('Invalid role tag.', 422);
}

$team = dbfetch('SELECT id FROM teams WHERE id = ?', [$teamId]);
$user = dbfetch('SELECT id FROM users WHERE id = ?', [$userId]);
if (!$team) json_error('Team not found.', 404);
if (!$user) json_error('User not found.', 404);

$exists = dbfetch('SELECT id FROM team_members WHERE team_id = ? AND user_id = ?', [$teamId, $userId]);
if ($exists) {
    json_error('User is already on this team.', 409);
}

dbq('INSERT INTO team_members (id, team_id, user_id, role_tag) VALUES (?, ?, ?, ?)', [
    uuid(), $teamId, $userId, $roleTag
]);

json_response(['ok' => true], 201);