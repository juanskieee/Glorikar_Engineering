<?php
declare(strict_types=1);
// GET /api/teams/list.php — admin only

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$teams = $pdo->query('SELECT * FROM teams ORDER BY created_at ASC')->fetchAll();

$memberStmt = $pdo->prepare('
    SELECT tm.id, tm.role_tag, u.id AS user_id, u.full_name, u.phone, u.email
    FROM team_members tm
    JOIN users u ON u.id = tm.user_id
    WHERE tm.team_id = ?
    ORDER BY tm.role_tag DESC  -- lead first
');

foreach ($teams as &$t) {
    $memberStmt->execute([$t['id']]);
    $t['members'] = $memberStmt->fetchAll();
}
unset($t);

respond(['teams' => $teams]);