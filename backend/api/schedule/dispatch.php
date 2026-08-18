<?php
/**
 * PATCH /api/schedule/dispatch.php — admin only. Dispatch an approved schedule.
 * Sets status -> dispatched and pushes an ETA notification to every client on it.
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
if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
    json_error('Not found.', 404);
}

$schedule = dbfetch('SELECT id, status FROM schedules WHERE id = ?', [$id]);
if (!$schedule) {
    json_error('Not found.', 404);
}
if ($schedule['status'] !== 'approved') {
    json_error('Only approved schedules can be dispatched.', 409);
}

dbq('UPDATE schedules SET status = ? WHERE id = ?', ['dispatched', $id]);

// Notify every client on this schedule with their ETA slot.
$stops = dball(
    'SELECT ss.booking_id, ss.eta, ss.stop_order, b.client_id
       FROM schedule_stops ss JOIN bookings b ON b.id = ss.booking_id
      WHERE ss.schedule_id = ? ORDER BY ss.stop_order ASC',
    [$id]
);

$byClient = [];
foreach ($stops as $stop) {
    if (!isset($byClient[$stop['client_id']])) {
        $byClient[$stop['client_id']] = [];
    }
    $byClient[$stop['client_id']][] = $stop;
}

foreach ($byClient as $clientId => $clientStops) {
    $eta = $clientStops[0]['eta'] ?? null;
    $msg = $eta
        ? "Your technician is on the way. ETA slot: {$eta}."
        : 'Your technician is on the way.';
    \Glorikar\Services\NotificationService::notify($clientId, 'Technician on the way', $msg, 'dispatch');
    \Glorikar\Services\NotificationService::pushToUser($clientId, 'Technician on the way', $msg);
}

json_response(['ok' => true, 'status' => 'dispatched', 'notified' => count($byClient)]);