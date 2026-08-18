<?php
/**
 * GET /api/bookings/get.php?id=X
 * Returns a single booking. Object-level access control:
 * clients may only view their own booking (404 if not theirs to avoid leaking existence).
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/auth-guard.php';

$id = $_GET['id'] ?? '';
if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
    json_error('Not found.', 404);
}

$user = auth_user();
$isAdmin = $user['role'] === 'admin';

$sql = $isAdmin
    ? 'SELECT b.*, u.full_name AS client_name, u.phone AS client_phone, u.email AS client_email
         FROM bookings b JOIN users u ON u.id = b.client_id
        WHERE b.id = ?'
    : 'SELECT b.*, u.full_name AS client_name, u.phone AS client_phone, u.email AS client_email
         FROM bookings b JOIN users u ON u.id = b.client_id
        WHERE b.id = ? AND b.client_id = ?';

$booking = $isAdmin
    ? dbfetch($sql, [$id])
    : dbfetch($sql, [$id, $user['id']]);

// 404 (not 403) for foreign objects.
if (!$booking) {
    json_error('Not found.', 404);
}

$services = dball(
    'SELECT s.id, s.name, s.duration_hrs, s.base_price, bs.quantity
       FROM booking_services bs
       JOIN services s ON s.id = bs.service_id
      WHERE bs.booking_id = ?',
    [$booking['id']]
);

$total = 0;
foreach ($services as $svc) {
    $total += (float)$svc['base_price'] * (int)$svc['quantity'];
}

$schedule = null;
if ($booking['schedule_id']) {
    $schedule = dbfetch(
        'SELECT s.id, s.scheduled_date, s.status AS schedule_status, s.team_id, t.name AS team_name, t.vehicle
           FROM schedules s JOIN teams t ON t.id = s.team_id
          WHERE s.id = ?',
        [$booking['schedule_id']]
    );
    if ($schedule) {
        $schedule['stops'] = dball(
            'SELECT ss.stop_order, ss.eta, ss.booking_id
               FROM schedule_stops ss WHERE ss.schedule_id = ?
              ORDER BY ss.stop_order ASC',
            [$schedule['id']]
        );
    }
}

$photos = dball('SELECT id, photo_url, type, uploaded_at FROM job_photos WHERE booking_id = ?', [$booking['id']]);

json_response([
    'ok' => true,
    'booking' => [
        'id' => $booking['id'],
        'status' => $booking['status'],
        'preferred_date_from' => $booking['preferred_date_from'],
        'preferred_date_to' => $booking['preferred_date_to'],
        'address' => $booking['address'],
        'latitude' => (float)$booking['latitude'],
        'longitude' => (float)$booking['longitude'],
        'notes' => $booking['notes'],
        'trip_score' => $booking['trip_score'] !== null ? (float)$booking['trip_score'] : null,
        'created_at' => $booking['created_at'],
        'client' => $isAdmin ? [
            'name' => $booking['client_name'],
            'phone' => $booking['client_phone'],
            'email' => $booking['client_email'],
        ] : null,
        'services' => $services,
        'total' => round($total, 2),
        'schedule' => $schedule,
        'photos' => $photos,
    ],
]);