<?php
declare(strict_types=1);
// GET /api/admin/client.php?id= — single client with their bookings

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$id = trim($_GET['id'] ?? '');
if ($id === '') respond_error('id is required.', 422);

$stmt = $pdo->prepare('
    SELECT u.id, u.full_name, u.email, u.phone, u.address, u.created_at,
           (SELECT COUNT(*) FROM bookings b WHERE b.client_id = u.id) AS total_bookings,
           (SELECT COALESCE(SUM(i.total_amount), 0) FROM invoices i
                JOIN bookings b2 ON b2.id = i.booking_id
                WHERE b2.client_id = u.id) AS total_spent
    FROM users u
    WHERE u.id = ? AND u.role = 'client'
');
$stmt->execute([$id]);
$client = $stmt->fetch();

if (!$client) respond_error('Client not found.', 404);

$stmt = $pdo->prepare('
    SELECT b.id, b.status, b.preferred_date_from, b.preferred_date_to, b.address, b.created_at,
           COALESCE(SUM(bs.quantity), 0) AS unit_count
    FROM bookings b
    LEFT JOIN booking_services bs ON bs.booking_id = b.id
    WHERE b.client_id = ?
    GROUP BY b.id
    ORDER BY b.created_at DESC
');
$stmt->execute([$id]);
$bookings = $stmt->fetchAll();

$svcStmt = $pdo->prepare('
    SELECT s.name FROM services s
    JOIN booking_services bs ON bs.service_id = s.id
    WHERE bs.booking_id = ?
');
foreach ($bookings as &$bk) {
    $svcStmt->execute([$bk['id']]);
    $bk['services'] = array_column($svcStmt->fetchAll(), 'name');
}
unset($bk);

respond(['client' => $client, 'bookings' => $bookings]);