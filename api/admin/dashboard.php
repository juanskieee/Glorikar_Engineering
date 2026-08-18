<?php
declare(strict_types=1);
// GET /api/admin/dashboard.php — admin dashboard stats + lists

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$today = date('Y-m-d');

// ── Stats ────────────────────────────────────────────────
$stmt = $pdo->prepare('
    SELECT COUNT(*) AS c
    FROM bookings b
    JOIN schedules s ON s.id = b.schedule_id
    WHERE s.scheduled_date = ?
      AND b.status NOT IN (\'pending\', \'cancelled\')
');
$stmt->execute([$today]);
$stats['today_jobs'] = (int)$stmt->fetch()['c'];

$stmt = $pdo->prepare('
    SELECT COUNT(*) AS c
    FROM bookings b
    JOIN schedules s ON s.id = b.schedule_id
    WHERE s.scheduled_date = ?
      AND b.status = \'completed\'
');
$stmt->execute([$today]);
$stats['completed_today'] = (int)$stmt->fetch()['c'];

$stats['pending'] = (int)$pdo->query('SELECT COUNT(*) AS c FROM bookings WHERE status = \'pending\'')->fetch()['c'];

$stats['active_teams'] = (int)$pdo->query('SELECT COUNT(*) AS c FROM teams WHERE is_available = TRUE')->fetch()['c'];
$stats['total_teams']  = (int)$pdo->query('SELECT COUNT(*) AS c FROM teams')->fetch()['c'];

$stmt = $pdo->prepare('
    SELECT COALESCE(SUM(total_amount), 0) AS s, COUNT(*) AS c
    FROM invoices WHERE DATE(issued_at) = ?
');
$stmt->execute([$today]);
$row                   = $stmt->fetch();
$stats['revenue_today'] = round((float)$row['s'], 2);
$stats['invoices_today'] = (int)$row['c'];

// ── Today's jobs ─────────────────────────────────────────
$stmt = $pdo->prepare('
    SELECT b.id, u.full_name AS client_name, b.address, t.name AS team_name, b.status
    FROM bookings b
    JOIN users u ON u.id = b.client_id
    LEFT JOIN schedules s ON s.id = b.schedule_id
    LEFT JOIN teams t    ON t.id = s.team_id
    WHERE s.scheduled_date = ?
    ORDER BY t.name, b.created_at
');
$stmt->execute([$today]);
$todayJobs = $stmt->fetchAll();

// ── Pending bookings ─────────────────────────────────────
$stmt = $pdo->query('
    SELECT b.id, b.preferred_date_from, b.address, u.full_name AS client_name
    FROM bookings b
    JOIN users u ON u.id = b.client_id
    WHERE b.status = \'pending\'
    ORDER BY b.preferred_date_from ASC
    LIMIT 10
');
$pendingBookings = $stmt->fetchAll();

$svcStmt = $pdo->prepare('
    SELECT s.name FROM services s
    JOIN booking_services bs ON bs.service_id = s.id
    WHERE bs.booking_id = ?
');
foreach ($pendingBookings as &$pb) {
    $svcStmt->execute([$pb['id']]);
    $pb['services'] = array_column($svcStmt->fetchAll(), 'name');
}
unset($pb);

// ── Recent activity ──────────────────────────────────────
$stmt = $pdo->query('
    SELECT b.id, b.status, b.created_at, u.full_name AS client_name
    FROM bookings b
    JOIN users u ON u.id = b.client_id
    ORDER BY b.created_at DESC
    LIMIT 10
');
$recentActivity = [];
foreach ($stmt->fetchAll() as $r) {
    $recentActivity[] = [
        'description' => 'Booking from ' . $r['client_name'],
        'time'        => $r['created_at'],
        'status'      => $r['status'],
    ];
}

respond([
    'stats'            => $stats,
    'today_jobs'       => $todayJobs,
    'pending_bookings' => $pendingBookings,
    'recent_activity'  => $recentActivity,
]);