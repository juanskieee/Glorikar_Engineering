<?php
declare(strict_types=1);
// GET /api/bookings/mine.php?status=
// Returns all bookings for the logged-in client.

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/auth-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$validStatuses = ['pending','scheduled','en_route','in_progress','completed','cancelled'];
$statusFilter  = $_GET['status'] ?? '';

$params = [$SESSION_USER['id']];
$where  = 'WHERE b.client_id = ?';

if ($statusFilter !== '') {
    if (!in_array($statusFilter, $validStatuses, true)) {
        respond_error('Invalid status filter.', 422);
    }
    $where   .= ' AND b.status = ?';
    $params[] = $statusFilter;
}

$stmt = $pdo->prepare("
    SELECT
        b.id, b.status, b.preferred_date_from, b.preferred_date_to,
        b.address, b.notes, b.trip_score, b.created_at
    FROM bookings b
    $where
    ORDER BY b.created_at DESC
");
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Attach services to each booking
$svcStmt = $pdo->prepare('
    SELECT bs.quantity, s.id AS service_id, s.name, s.base_price, s.duration_hrs
    FROM booking_services bs
    JOIN services s ON s.id = bs.service_id
    WHERE bs.booking_id = ?
');

foreach ($bookings as &$b) {
    $svcStmt->execute([$b['id']]);
    $b['services'] = $svcStmt->fetchAll();
}
unset($b);

respond(['bookings' => $bookings]);