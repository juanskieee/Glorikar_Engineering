<?php
declare(strict_types=1);
// GET /api/bookings/pending.php — admin only
// Returns all pending bookings, oldest first.

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$stmt = $pdo->query('
    SELECT
        b.id, b.status, b.preferred_date_from, b.preferred_date_to,
        b.address, b.latitude, b.longitude, b.notes, b.trip_score, b.created_at,
        u.id AS client_id, u.full_name AS client_name, u.phone AS client_phone,
        u.email AS client_email
    FROM bookings b
    JOIN users u ON u.id = b.client_id
    WHERE b.status = "pending"
    ORDER BY b.created_at ASC
');
$bookings = $stmt->fetchAll();

// Attach services
$svcStmt = $pdo->prepare('
    SELECT bs.quantity, s.id AS service_id, s.name, s.base_price
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