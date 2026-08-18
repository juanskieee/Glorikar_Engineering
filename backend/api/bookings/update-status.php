<?php
/**
 * PATCH /api/bookings/update-status.php — admin only.
 * Move a booking through the status flow; notifies the client.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';
require_once __DIR__ . '/../../services/NotificationService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

csrf_verify();

$data = read_json_input();
$id = $data['id'] ?? '';
$newStatus = $data['status'] ?? '';

if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
    json_error('Not found.', 404);
}

$allowed = ['pending', 'scheduled', 'en_route', 'in_progress', 'completed', 'cancelled'];
if (!in_array($newStatus, $allowed, true)) {
    json_error('Invalid status.', 422);
}

$booking = dbfetch('SELECT id, client_id, status FROM bookings WHERE id = ?', [$id]);
if (!$booking) {
    json_error('Not found.', 404);
}

// Simple forward-only guard for the happy path; cancelling is always allowed.
if ($newStatus !== 'cancelled' && $newStatus === $booking['status']) {
    json_error('Booking is already in that status.', 409);
}

dbq('UPDATE bookings SET status = ? WHERE id = ?', [$newStatus, $id]);

\Glorikar\Services\NotificationService::onBookingStatusChange($id, $newStatus);

json_response(['ok' => true, 'status' => $newStatus]);