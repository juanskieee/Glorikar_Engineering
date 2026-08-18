<?php
declare(strict_types=1);
// POST /api/jobs/generate-invoice.php — admin only
// Body: { booking_id }
// Creates an invoices row for the booking if one doesn't exist yet.

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond_error('Method not allowed', 405);
verify_csrf();

$bookingId = trim(json_body()['booking_id'] ?? '');
if ($bookingId === '') respond_error('booking_id is required.', 422);

$stmt = $pdo->prepare('SELECT id FROM bookings WHERE id = ? LIMIT 1');
$stmt->execute([$bookingId]);
if (!$stmt->fetch()) respond_error('Booking not found.', 404);

// Return existing invoice if already generated
$stmt = $pdo->prepare('SELECT * FROM invoices WHERE booking_id = ? LIMIT 1');
$stmt->execute([$bookingId]);
$existing = $stmt->fetch();
if ($existing) {
    respond(['invoice' => $existing]);
}

// total_amount = sum of service price × quantity
$stmt = $pdo->prepare('
    SELECT COALESCE(SUM(s.base_price * bs.quantity), 0) AS total
    FROM booking_services bs
    JOIN services s ON s.id = bs.service_id
    WHERE bs.booking_id = ?
');
$stmt->execute([$bookingId]);
$total = round((float)$stmt->fetch()['total'], 2);

$pdo->prepare('
    INSERT INTO invoices (id, booking_id, total_amount)
    VALUES (UUID(), ?, ?)
')->execute([$bookingId, $total]);

$stmt = $pdo->prepare('SELECT * FROM invoices WHERE booking_id = ? LIMIT 1');
$stmt->execute([$bookingId]);
$invoice = $stmt->fetch();

// Attach line items for convenience
$lineStmt = $pdo->prepare('
    SELECT s.name AS description, bs.quantity AS qty, s.base_price AS unit_price
    FROM booking_services bs
    JOIN services s ON s.id = bs.service_id
    WHERE bs.booking_id = ?
');
$lineStmt->execute([$bookingId]);
$invoice['line_items'] = $lineStmt->fetchAll();

respond(['invoice' => $invoice], 201);