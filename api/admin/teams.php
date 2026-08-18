<?php
declare(strict_types=1);
// GET  /api/admin/teams.php — team list with members + this week's job counts
// POST /api/admin/teams.php — create a team { name, members[], base_location }

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

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $body         = json_body();
    $name         = trim($body['name'] ?? '');
    if ($name === '') respond_error('name is required.', 422);
    $members      = is_array($body['members'] ?? null) ? $body['members'] : [];
    $baseLocation = trim($body['base_location'] ?? '');

    $teamId = glorikar_uuid();
    // vehicle column doubles as base location until a dedicated column is added
    $pdo->prepare('INSERT INTO teams (id, name, vehicle, is_available) VALUES (?, ?, ?, TRUE)')
        ->execute([$teamId, $name, $baseLocation]);

    syncTeamMembers($pdo, $teamId, $members);

    respond([
        'team' => [
            'id'            => $teamId,
            'name'          => $name,
            'base_location' => $baseLocation,
            'members'       => $members,
        ],
    ], 201);
}

if ($method !== 'GET') respond_error('Method not allowed', 405);

$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));

$stmt = $pdo->prepare('
    SELECT t.id, t.name, t.vehicle AS base_location,
           (SELECT COUNT(*) FROM team_members tm WHERE tm.team_id = t.id) AS member_count,
           (SELECT COUNT(*) FROM schedules s
                JOIN schedule_stops ss ON ss.schedule_id = s.id
                WHERE s.team_id = t.id
                  AND s.scheduled_date BETWEEN ? AND ?) AS jobs_this_week
    FROM teams t
    ORDER BY t.created_at ASC
');
$stmt->execute([$monday, $sunday]);
$teams = $stmt->fetchAll();

$memberStmt = $pdo->prepare('
    SELECT u.full_name FROM team_members tm
    JOIN users u ON u.id = tm.user_id
    WHERE tm.team_id = ?
    ORDER BY tm.role_tag DESC
');
foreach ($teams as &$t) {
    $memberStmt->execute([$t['id']]);
    $t['members'] = array_column($memberStmt->fetchAll(), 'full_name');
}
unset($t);

respond(['teams' => $teams]);