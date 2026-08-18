<?php
/**
 * POST /api/jobs/generate-invoice.php — admin only.
 * Generate (or re-generate) an invoice for a completed booking.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

csrf_verify();

$data = read_json_input();
$bookingId = $data['booking_id'] ?? '';
if (!preg_match('/^[0-9a-fA-F-]{36}$/', $bookingId)) {
    json_error('Not found.', 404);
}

$booking = dbfetch(
    'SELECT b.id, b.status, b.address, b.created_at, u.full_name, u.email, u.address AS client_address
       FROM bookings b JOIN users u ON u.id = b.client_id
      WHERE b.id = ?',
    [$bookingId]
);
if (!$booking) {
    json_error('Not found.', 404);
}
if ($booking['status'] !== 'completed') {
    json_error('Invoices can only be generated for completed jobs.', 409);
}

$items = dball(
    'SELECT s.name, s.base_price, bs.quantity, (s.base_price * bs.quantity) AS line_total
       FROM booking_services bs JOIN services s ON s.id = bs.service_id
      WHERE bs.booking_id = ?',
    [$bookingId]
);
$total = array_sum(array_map(fn($i) => (float)$i['line_total'], $items));

$existing = dbfetch('SELECT id, total_amount, issued_at, paid, notes FROM invoices WHERE booking_id = ?', [$bookingId]);
if ($existing) {
    json_response([
        'ok' => true,
        'invoice' => [
            'id' => $existing['id'],
            'booking_id' => $bookingId,
            'total_amount' => round((float)$existing['total_amount'], 2),
            'issued_at' => $existing['issued_at'],
            'paid' => (bool)$existing['paid'],
            'notes' => $existing['notes'],
        ],
        'items' => $items,
    ]);
}

$invoiceId = uuid();
dbq('INSERT INTO invoices (id, booking_id, total_amount, notes) VALUES (?, ?, ?, ?)', [
    $invoiceId, $bookingId, round($total, 2), null
]);

json_response([
    'ok' => true,
    'invoice' => [
        'id' => $invoiceId,
        'booking_id' => $bookingId,
        'total_amount' => round($total, 2),
        'issued_at' => date('Y-m-d H:i:s'),
        'paid' => false,
        'notes' => null,
    ],
    'items' => $items,
], 201);