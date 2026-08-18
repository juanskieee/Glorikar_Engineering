<?php
declare(strict_types=1);
// GET /api/admin/job.php?id= — full detail for a single job

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$id = trim($_GET['id'] ?? '');
if ($id === '') respond_error('id is required.', 422);

$stmt = $pdo->prepare('
    SELECT b.id, b.status, b.client_id, b.address, b.preferred_date_from, b.preferred_date_to,
           b.notes, b.created_at,
           u.full_name AS client_name, u.phone AS client_phone,
           s.scheduled_date, t.id AS team_id, t.name AS team_name
    FROM bookings b
    JOIN users u ON u.id = b.client_id
    LEFT JOIN schedules s ON s.id = b.schedule_id
    LEFT JOIN teams t     ON t.id = s.team_id
    WHERE b.id = ?
');
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) respond_error('Job not found.', 404);

$svcStmt = $pdo->prepare('
    SELECT s.name, s.duration_hrs, s.base_price, bs.quantity
    FROM services s
    JOIN booking_services bs ON bs.service_id = s.id
    WHERE bs.booking_id = ?
');
$svcStmt->execute([$id]);
$services = $svcStmt->fetchAll();

$job['services']   = array_column($services, 'name');
$job['unit_count'] = array_sum(array_column($services, 'quantity'));

$photoStmt = $pdo->prepare('
    SELECT photo_url, type FROM job_photos
    WHERE booking_id = ?
    ORDER BY uploaded_at
');
$photoStmt->execute([$id]);
$job['photos_before'] = [];
$job['photos_after']  = [];
foreach ($photoStmt->fetchAll() as $p) {
    $job[$p['type'] === 'before' ? 'photos_before' : 'photos_after'][] = $p['photo_url'];
}

$logStmt = $pdo->prepare('
    SELECT bsl.*, u.full_name AS changed_by_name
    FROM booking_status_log bsl
    LEFT JOIN users u ON u.id = bsl.changed_by
    WHERE bsl.booking_id = ?
    ORDER BY bsl.changed_at ASC
');
$logStmt->execute([$id]);
$job['status_log'] = $logStmt->fetchAll();

respond(['job' => $job]);