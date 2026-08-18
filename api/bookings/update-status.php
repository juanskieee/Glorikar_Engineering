<?php
declare(strict_types=1);
// PATCH /api/bookings/update-status.php — admin only
// Body: { id, status }

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') respond_error('Method not allowed', 405);
verify_csrf();

$body   = json_body();
$id     = trim($body['id']     ?? '');
$status = trim($body['status'] ?? '');

if ($id === '' || $status === '') respond_error('id and status are required.', 422);

// Legal transitions
$transitions = [
    'pending'     => ['scheduled', 'cancelled'],
    'scheduled'   => ['en_route',  'cancelled'],
    'en_route'    => ['in_progress'],
    'in_progress' => ['completed', 'cancelled'],
];

$stmt = $pdo->prepare('SELECT id, status FROM bookings WHERE id = ? LIMIT 1');
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

// TODO: send Web Push notification to client on status change

respond(['booking' => ['id' => $id, 'status' => $status]]);