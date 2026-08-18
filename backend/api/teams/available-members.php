<?php
/**
 * GET /api/teams/available-members.php — admin only.
 * List users (technicians) that can be added to a team.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';

$teamId = $_GET['team_id'] ?? '';

$sql = "SELECT id, full_name, email, phone, role FROM users WHERE role = 'client'";
$params = [];

if (preg_match('/^[0-9a-fA-F-]{36}$/', $teamId)) {
    $sql .= " AND id NOT IN (
        SELECT user_id FROM team_members WHERE team_id = ?
    )";
    $params[] = $teamId;
}

$sql .= ' ORDER BY full_name ASC';
$users = dball($sql, $params);

json_response(['ok' => true, 'users' => $users]);