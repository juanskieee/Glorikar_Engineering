<?php
declare(strict_types=1);
// GET /api/admin/jobs.php — job list, optionally filtered by status

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$status = trim($_GET['status'] ?? '');
$where  = '1=1';
$params = [];
if ($status !== '') {
    $where   .= ' AND b.status = ?';
    $params[] = $status;
}

$stmt = $pdo->prepare("
    SELECT b.id, b.status, b.address, b.preferred_date_from, b.preferred_date_to, b.created_at,
           u.full_name AS client_name,
           t.id AS team_id, t.name AS team_name,
           s.scheduled_date,
           COALESCE(SUM(bs.quantity), 0) AS unit_count
    FROM bookings b
    JOIN users u ON u.id = b.client_id
    LEFT JOIN schedules s          ON s.id = b.schedule_id
    LEFT JOIN teams t              ON t.id = s.team_id
    LEFT JOIN booking_services bs  ON bs.booking_id = b.id
    WHERE $where
    GROUP BY b.id
    ORDER BY COALESCE(s.scheduled_date, b.preferred_date_from) ASC
");
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$svcStmt = $pdo->prepare('
    SELECT s.name FROM services s
    JOIN booking_services bs ON bs.service_id = s.id
    WHERE bs.booking_id = ?
');
foreach ($jobs as &$j) {
    $svcStmt->execute([$j['id']]);
    $j['services'] = array_column($svcStmt->fetchAll(), 'name');
}
unset($j);

respond(['jobs' => $jobs]);