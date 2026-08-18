<?php
declare(strict_types=1);
// POST /api/jobs/complete.php — admin only
// Body: { booking_id }

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond_error('Method not allowed', 405);
verify_csrf();

$bookingId = trim(json_body()['booking_id'] ?? '');
if ($bookingId === '') respond_error('booking_id is required.', 422);

$stmt = $pdo->prepare('SELECT id, status, client_id FROM bookings WHERE id = ? LIMIT 1');
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if (!$booking) respond_error('Booking not found.', 404);

$allowedFromStatuses = ['en_route', 'in_progress'];
if (!in_array($booking['status'], $allowedFromStatuses, true)) {
    respond_error(
        "Cannot mark complete from status \"{$booking['status']}\".",
        400,
        'INVALID_STATUS'
    );
}

$pdo->prepare("UPDATE bookings SET status = 'completed' WHERE id = ?")
    ->execute([$bookingId]);

// TODO: send Web Push to client — "Your job is complete!"

respond(['success' => true, 'booking_id' => $bookingId]);