<?php
/**
 * POST /api/bookings/create.php — client creates a booking.
 * Geocodes the address server-side, stores lat/lng.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/auth-guard.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../services/MapboxService.php';
require_once __DIR__ . '/../../services/NotificationService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

csrf_verify();
rate_limit('create_booking', 20, 900);

$user = auth_user();
$data = read_json_input();

$address = trim($data['address'] ?? '');
$dateFrom = trim($data['preferred_date_from'] ?? '');
$dateTo = trim($data['preferred_date_to'] ?? '');
$notes = trim($data['notes'] ?? '');
$services = $data['services'] ?? []; // [[service_id, quantity], ...]
$latitude = isset($data['latitude']) && $data['latitude'] !== '' ? (float)$data['latitude'] : null;
$longitude = isset($data['longitude']) && $data['longitude'] !== '' ? (float)$data['longitude'] : null;

// --- Validate -----------------------------------------------------------
$errors = [];
if (mb_strlen($address) < 5) $errors[] = 'A valid address is required.';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $errors[] = 'A preferred start date is required.';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) $errors[] = 'A preferred end date is required.';
if ($dateTo < $dateFrom) $errors[] = 'End date must not be before start date.';

$validServices = [];
foreach ($services as $svc) {
    $serviceId = (int)($svc['service_id'] ?? 0);
    $qty = max(1, (int)($svc['quantity'] ?? 1));
    if ($serviceId <= 0) {
        $errors[] = 'Invalid service selection.';
        break;
    }
    $validServices[] = ['service_id' => $serviceId, 'quantity' => $qty];
}
if (!$validServices) $errors[] = 'At least one service is required.';

if ($errors) json_error(implode(' ', $errors), 422);

// Verify all service ids exist.
$placeholders = implode(',', array_fill(0, count($validServices), '?'));
$existing = dball(
    "SELECT id FROM services WHERE id IN ({$placeholders})",
    array_column($validServices, 'service_id')
);
if (count($existing) !== count($validServices)) {
    json_error('One or more services do not exist.', 422);
}

// --- Geocode (best-effort; fall back to provided coords) ----------------
if ($latitude === null || $longitude === null) {
    $geo = \Glorikar\Services\MapboxService::geocode($address);
    if ($geo) {
        $latitude = $geo['lat'];
        $longitude = $geo['lng'];
    }
}
if ($latitude === null || $longitude === null) {
    json_error('Could not locate that address. Please check it and try again.', 422);
}

// --- Insert ------------------------------------------------------------
try {
    $pdo = db();
    $pdo->beginTransaction();

    $bookingId = uuid();
    dbq(
        'INSERT INTO bookings (id, client_id, status, preferred_date_from, preferred_date_to, address, latitude, longitude, notes)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$bookingId, $user['id'], 'pending', $dateFrom, $dateTo, $address, $latitude, $longitude, $notes !== '' ? $notes : null]
    );

    foreach ($validServices as $svc) {
        dbq(
            'INSERT INTO booking_services (id, booking_id, service_id, quantity) VALUES (?, ?, ?, ?)',
            [uuid(), $bookingId, $svc['service_id'], $svc['quantity']]
        );
    }

    $pdo->commit();

    \Glorikar\Services\NotificationService::notify(
        $user['id'],
        'Booking received',
        'Your booking has been submitted and is pending scheduling.',
        'booking'
    );

    json_response([
        'ok' => true,
        'booking' => [
            'id' => $bookingId,
            'status' => 'pending',
            'preferred_date_from' => $dateFrom,
            'preferred_date_to' => $dateTo,
        ],
    ], 201);
} catch (\Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('create booking failed: ' . $e->getMessage());
    json_error('Could not create your booking. Please try again.', 500);
}