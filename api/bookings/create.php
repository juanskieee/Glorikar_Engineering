<?php
declare(strict_types=1);
// POST /api/bookings/create.php
// Body: { services:[{service_id, quantity}], preferred_date_from, preferred_date_to, address, notes? }
// Returns: { booking: {...} }  201

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/auth-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond_error('Method not allowed', 405);
verify_csrf();

$body = json_body();

// ── Validate ──────────────────────────────────────────────
$services  = $body['services']             ?? [];
$dateFrom  = trim($body['preferred_date_from'] ?? '');
$dateTo    = trim($body['preferred_date_to']   ?? '');
$address   = trim($body['address']         ?? '');
$notes     = trim($body['notes']           ?? '');

$errors = [];

if (!is_array($services) || empty($services)) {
    $errors[] = 'At least one service is required.';
} else {
    foreach ($services as $i => $svc) {
        if (empty($svc['service_id']) || !is_numeric($svc['service_id'])) {
            $errors[] = "Service #$i: invalid service_id.";
        }
        $qty = (int)($svc['quantity'] ?? 0);
        if ($qty < 1 || $qty > 20) {
            $errors[] = "Service #$i: quantity must be between 1 and 20.";
        }
    }
}

if (!$dateFrom || !strtotime($dateFrom)) $errors[] = 'preferred_date_from is required (YYYY-MM-DD).';
if (!$dateTo   || !strtotime($dateTo))   $errors[] = 'preferred_date_to is required (YYYY-MM-DD).';

if (empty($errors)) {
    if ($dateFrom > $dateTo)          $errors[] = 'date_from must be before or equal to date_to.';
    if ($dateFrom < date('Y-m-d'))    $errors[] = 'Preferred date cannot be in the past.';
}

if ($address === '') $errors[] = 'Address is required.';

if (!empty($errors)) respond_error(implode(' ', $errors), 422, 'VALIDATION_ERROR');

// ── Validate service IDs exist in DB ─────────────────────
$serviceIds    = array_map(fn($s) => (int)$s['service_id'], $services);
$placeholders  = implode(',', array_fill(0, count($serviceIds), '?'));
$stmt          = $pdo->prepare("SELECT id FROM services WHERE id IN ($placeholders)");
$stmt->execute($serviceIds);
$validIds      = array_column($stmt->fetchAll(), 'id');

$invalidIds = array_diff($serviceIds, $validIds);
if (!empty($invalidIds)) {
    respond_error('Invalid service_id(s): ' . implode(', ', $invalidIds), 422, 'INVALID_SERVICE');
}

// ── Geocode address (Mapbox, graceful fallback) ───────────
$lat = null;
$lng = null;
$token = $_ENV['MAPBOX_ACCESS_TOKEN'] ?? '';
if ($token) {
    $encoded = rawurlencode($address . ', Philippines');
    $url     = "https://api.mapbox.com/geocoding/v5/mapbox.places/{$encoded}.json"
             . "?access_token={$token}&limit=1&country=PH";
    $ctx = stream_context_create(['http' => ['timeout' => 3]]);
    $res = @file_get_contents($url, false, $ctx);
    if ($res) {
        $geo = json_decode($res, true);
        if (!empty($geo['features'][0]['center'])) {
            $lng = $geo['features'][0]['center'][0];
            $lat = $geo['features'][0]['center'][1];
        }
    }
}

// ── Insert booking ─────────────────────────────────────────
$bookingId = bin2hex(random_bytes(16));
$pdo->prepare('
    INSERT INTO bookings
        (id, client_id, status, preferred_date_from, preferred_date_to, address, latitude, longitude, notes)
    VALUES (UUID(), ?, "pending", ?, ?, ?, ?, ?, ?)
')->execute([$SESSION_USER['id'], $dateFrom, $dateTo, $address, $lat, $lng, $notes ?: null]);

// Fetch the new booking id
$booking = $pdo->prepare('
    SELECT id, status, preferred_date_from, preferred_date_to, address, notes, created_at
    FROM bookings
    WHERE client_id = ? ORDER BY created_at DESC LIMIT 1
');
$booking->execute([$SESSION_USER['id']]);
$booking = $booking->fetch();

// ── Insert booking_services ───────────────────────────────
$insStmt = $pdo->prepare('
    INSERT INTO booking_services (id, booking_id, service_id, quantity)
    VALUES (UUID(), ?, ?, ?)
');
foreach ($services as $svc) {
    $insStmt->execute([$booking['id'], (int)$svc['service_id'], (int)$svc['quantity']]);
}

respond(['booking' => $booking], 201);