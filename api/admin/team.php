<?php
declare(strict_types=1);
// GET    /api/admin/team.php?id= — single team with members + recent jobs
// PATCH  /api/admin/team.php?id= — update { name, members[], base_location }
// DELETE /api/admin/team.php?id= — delete a team

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';

function glorikar_uuid(): string
{
    $data    = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function syncTeamMembers(PDO $pdo, string $teamId, array $names): void
{
    $pdo->prepare('DELETE FROM team_members WHERE team_id = ?')->execute([$teamId]);

    $find = $pdo->prepare('SELECT id FROM users WHERE full_name = ? AND role = "client" LIMIT 1');
    $ins  = $pdo->prepare('INSERT INTO team_members (id, team_id, user_id, role_tag) VALUES (?, ?, ?, ?)');

    $i = 0;
    foreach (array_filter(array_map('trim', $names)) as $name) {
        $find->execute([$name]);
        $userId = $find->fetchColumn();
        if ($userId === false) continue; // no matching user account for this name
        $role = $i === 0 ? 'lead' : 'technician';
        $ins->execute([glorikar_uuid(), $teamId, $userId, $role]);
        $i++;
    }
}

$id     = trim($_GET['id'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

if ($id === '' && $method !== 'GET') respond_error('id is required.', 422);
if ($id === '') respond_error('id is required.', 422);

if ($method === 'DELETE') {
    $pdo->prepare('DELETE FROM teams WHERE id = ?')->execute([$id]);
    respond(['success' => true]);
}

if ($method === 'PATCH') {
    $body = json_body();

    $stmt = $pdo->prepare('SELECT id FROM teams WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) respond_error('Team not found.', 404);

    $name = trim($body['name'] ?? '');
    $base = trim($body['base_location'] ?? '');
    if ($name !== '') {
        $pdo->prepare('UPDATE teams SET name = ? WHERE id = ?')->execute([$name, $id]);
    }
    if (array_key_exists('base_location', $body)) {
        $pdo->prepare('UPDATE teams SET vehicle = ? WHERE id = ?')->execute([$base, $id]);
    }
    if (array_key_exists('members', $body) && is_array($body['members'])) {
        syncTeamMembers($pdo, $id, $body['members']);
    }

    respond(['success' => true]);
}

if ($method !== 'GET') respond_error('Method not allowed', 405);

$stmt = $pdo->prepare('SELECT id, name, vehicle AS base_location, created_at FROM teams WHERE id = ?');
$stmt->execute([$id]);
$team = $stmt->fetch();

if (!$team) respond_error('Team not found.', 404);

$memberStmt = $pdo->prepare('
    SELECT u.full_name FROM team_members tm
    JOIN users u ON u.id = tm.user_id
    WHERE tm.team_id = ?
    ORDER BY tm.role_tag DESC
');
$memberStmt->execute([$id]);
$team['members']       = array_column($memberStmt->fetchAll(), 'full_name');
$team['member_count']  = count($team['members']);

$jobStmt = $pdo->prepare('
    SELECT b.id, b.status, b.address, u.full_name AS client_name, s.scheduled_date
    FROM bookings b
    JOIN users u ON u.id = b.client_id
    JOIN schedules s ON s.id = b.schedule_id
    WHERE s.team_id = ?
    ORDER BY s.scheduled_date DESC
    LIMIT 10
');
$jobStmt->execute([$id]);
$team['recent_jobs'] = $jobStmt->fetchAll();

respond(['team' => $team]);