<?php
declare(strict_types=1);
// PATCH /api/admin/job-status.php — advance a booking through its status flow
// Params: ?id= OR body { booking_id } plus body { status, notes }

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';
require_once __DIR__ . '/../../backend/services/PushService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') respond_error('Method not allowed', 405);
verify_csrf();

$body   = json_body();
$id     = trim($_GET['id'] ?? $body['booking_id'] ?? $body['id'] ?? '');
$status = trim($body['status'] ?? '');

if ($id === '' || $status === '') respond_error('id and status are required.', 422);

$transitions = [
    'pending'     => ['scheduled', 'cancelled'],
    'scheduled'   => ['en_route',  'cancelled'],
    'en_route'    => ['in_progress'],
    'in_progress' => ['completed', 'cancelled'],
];

$stmt = $pdo->prepare('SELECT id, status, client_id FROM bookings WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) respond_error('Booking not found.', 404);

$allowed = $transitions[$booking['status']] ?? [];
if (!in_array($status, $allowed, true)) {
    respond_error(
        "Cannot transition from \"{$booking['status']}\" to \"$status\".",
        400,
        'INVALID_TRANSITION'
    );
}

$pdo->prepare('UPDATE bookings SET status = ? WHERE id = ?')->execute([$status, $id]);

$notes = trim($body['notes'] ?? '');
$pdo->prepare('
    INSERT INTO booking_status_log (booking_id, from_status, to_status, changed_by, notes)
    VALUES (?, ?, ?, ?, ?)
')->execute([
    $id,
    $booking['status'],
    $status,
    $_SESSION['user_id'] ?? null,
    $notes !== '' ? $notes : null,
]);

// Notify the client about the status change
$pushMessages = [
    'scheduled'   => ['Booking Confirmed', 'Your service has been scheduled.'],
    'en_route'    => ['Team On The Way', 'Your technician team is en route.'],
    'in_progress' => ['Service Started', 'The team has started work at your location.'],
    'completed'   => ['Service Complete', 'Your job is done. Your invoice is ready.'],
    'cancelled'   => ['Booking Cancelled', 'Your booking has been cancelled.'],
];
if (isset($pushMessages[$status])) {
    [$pushTitle, $pushBody] = $pushMessages[$status];
    PushService::send(
        (string)$booking['client_id'],
        $pushTitle,
        $pushBody,
        '/client/booking-status.php?id=' . $id
    );
}

respond(['booking' => ['id' => $id, 'status' => $status]]);