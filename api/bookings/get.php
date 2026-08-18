<?php
declare(strict_types=1);
// GET /api/bookings/get.php?id=
// Client: own booking only. Admin: any booking.

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/auth-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$id = trim($_GET['id'] ?? '');
if ($id === '') respond_error('id is required.', 422);

$stmt = $pdo->prepare('
    SELECT b.*, u.full_name AS client_name, u.phone AS client_phone
    FROM bookings b
    JOIN users u ON u.id = b.client_id
    WHERE b.id = ?
    LIMIT 1
');
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) respond_error('Booking not found.', 404);

// Clients may only see their own bookings — 404 to avoid leaking existence
if ($SESSION_USER['role'] === 'client' && $booking['client_id'] !== $SESSION_USER['id']) {
    respond_error('Booking not found.', 404);
}

// Services
$svcStmt = $pdo->prepare('
    SELECT bs.quantity, s.id AS service_id, s.name, s.base_price, s.duration_hrs
    FROM booking_services bs
    JOIN services s ON s.id = bs.service_id
    WHERE bs.booking_id = ?
');
$svcStmt->execute([$id]);
$booking['services'] = $svcStmt->fetchAll();

// Schedule info (if any)
$booking['schedule'] = null;
if ($booking['schedule_id']) {
    $schStmt = $pdo->prepare('
        SELECT sc.status, sc.scheduled_date, ss.eta, t.name AS team_name
        FROM schedules sc
        JOIN schedule_stops ss ON ss.schedule_id = sc.id AND ss.booking_id = ?
        JOIN teams t           ON t.id = sc.team_id
        WHERE sc.id = ?
        LIMIT 1
    ');
    $schStmt->execute([$id, $booking['schedule_id']]);
    $booking['schedule'] = $schStmt->fetch() ?: null;
}

// Photos
$photoStmt = $pdo->prepare('SELECT id, photo_url, type, uploaded_at FROM job_photos WHERE booking_id = ? ORDER BY uploaded_at ASC');
$photoStmt->execute([$id]);
$booking['photos'] = $photoStmt->fetchAll();

respond(['booking' => $booking]);