<?php
declare(strict_types=1);
// PATCH /api/schedule/dispatch.php — admin only
// Body: { id }
// Dispatches an approved schedule → updates bookings to en_route.

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';
require_once __DIR__ . '/../../backend/services/PushService.php';



if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') respond_error('Method not allowed', 405);
verify_csrf();

$id = trim(json_body()['id'] ?? '');
if ($id === '') respond_error('id is required.', 422);

$stmt = $pdo->prepare('SELECT id, status FROM schedules WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$schedule = $stmt->fetch();

if (!$schedule)                          respond_error('Schedule not found.', 404);
if ($schedule['status'] !== 'approved')  respond_error('Only approved schedules can be dispatched.', 400, 'INVALID_STATUS');

// Transition schedule
$pdo->prepare('UPDATE schedules SET status = "dispatched" WHERE id = ?')->execute([$id]);

// Transition all linked bookings to en_route
$pdo->prepare("
    UPDATE bookings SET status = 'en_route'
    WHERE schedule_id = ? AND status = 'scheduled'
")->execute([$id]);

// Gather client ids for push notifications
$clientStmt = $pdo->prepare("
    SELECT DISTINCT u.id AS client_id, u.full_name, b.id AS booking_id, ss.eta
    FROM schedule_stops ss
    JOIN bookings b ON b.id = ss.booking_id
    JOIN users u    ON u.id = b.client_id
    WHERE ss.schedule_id = ?
    ORDER BY ss.stop_order ASC
");
$clientStmt->execute([$id]);
$clients = $clientStmt->fetchAll();

// Notify every client in the dispatched schedule
foreach ($clients as $client) {
    $msg = 'Your technician team is en route.';
    if (!empty($client['eta'])) $msg .= ' ETA: ' . $client['eta'];
    PushService::send(
        (string)$client['client_id'],
        'Team On The Way',
        $msg,
        '/client/booking-status.php?id=' . $client['booking_id']
    );
}

respond([
    'schedule'         => ['id' => $id, 'status' => 'dispatched'],
    'notified_clients' => count($clients),
]);