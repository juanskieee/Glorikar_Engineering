<?php
/**
 * GET /api/bookings/mine.php — the current client's bookings.
 * Scoped server-side to the session user (never trust client_id).
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/auth-guard.php';

$user = auth_user();

$bookings = dball(
    'SELECT b.*, s.id AS schedule_id2, s.scheduled_date
       FROM bookings b
       LEFT JOIN schedules s ON s.id = b.schedule_id
      WHERE b.client_id = ?
      ORDER BY b.created_at DESC',
    [$user['id']]
);

$result = [];
foreach ($bookings as $b) {
    $services = dball(
        'SELECT s.name, s.duration_hrs, s.base_price, bs.quantity
           FROM booking_services bs
           JOIN services s ON s.id = bs.service_id
          WHERE bs.booking_id = ?',
        [$b['id']]
    );
    $total = 0;
    foreach ($services as $svc) {
        $total += (float)$svc['base_price'] * (int)$svc['quantity'];
    }
    $result[] = [
        'id' => $b['id'],
        'status' => $b['status'],
        'preferred_date_from' => $b['preferred_date_from'],
        'preferred_date_to' => $b['preferred_date_to'],
        'scheduled_date' => $b['scheduled_date'],
        'address' => $b['address'],
        'notes' => $b['notes'],
        'trip_score' => $b['trip_score'] !== null ? (float)$b['trip_score'] : null,
        'created_at' => $b['created_at'],
        'services' => $services,
        'total' => round($total, 2),
    ];
}

json_response(['ok' => true, 'bookings' => $result]);