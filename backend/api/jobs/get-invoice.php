<?php
/**
 * GET /api/jobs/get-invoice.php?booking_id=X
 * Clients see their own invoice (404 otherwise); admins see any.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/auth-guard.php';

$bookingId = $_GET['booking_id'] ?? '';
if (!preg_match('/^[0-9a-fA-F-]{36}$/', $bookingId)) {
    json_error('Not found.', 404);
}

$user = auth_user();
$isAdmin = $user['role'] === 'admin';

$invoice = $isAdmin
    ? dbfetch('SELECT * FROM invoices WHERE booking_id = ?', [$bookingId])
    : dbfetch(
        'SELECT inv.* FROM invoices inv JOIN bookings b ON b.id = inv.booking_id
          WHERE inv.booking_id = ? AND b.client_id = ?',
        [$bookingId, $user['id']]
    );

// 404 for foreign objects.
if (!$invoice) {
    json_error('Not found.', 404);
}

$booking = dbfetch(
    'SELECT b.*, u.full_name AS client_name, u.email, u.address AS client_address
       FROM bookings b JOIN users u ON u.id = b.client_id WHERE b.id = ?',
    [$bookingId]
);

$items = dball(
    'SELECT s.name, s.base_price, bs.quantity, (s.base_price * bs.quantity) AS line_total
       FROM booking_services bs JOIN services s ON s.id = bs.service_id
      WHERE bs.booking_id = ?',
    [$bookingId]
);

json_response([
    'ok' => true,
    'invoice' => [
        'id' => $invoice['id'],
        'booking_id' => $bookingId,
        'total_amount' => round((float)$invoice['total_amount'], 2),
        'issued_at' => $invoice['issued_at'],
        'paid' => (bool)$invoice['paid'],
        'notes' => $invoice['notes'],
    ],
    'booking' => [
        'id' => $bookingId,
        'address' => $booking['address'],
        'created_at' => $booking['created_at'],
        'client_name' => $booking['client_name'],
        'client_email' => $booking['email'],
        'client_address' => $booking['client_address'],
    ],
    'items' => $items,
]);