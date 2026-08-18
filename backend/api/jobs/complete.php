<?php
/**
 * POST /api/jobs/complete.php — admin only. Mark a booking completed,
 * optionally generate the invoice at the same time.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';
require_once __DIR__ . '/../../services/NotificationService.php';

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
    'SELECT b.id, b.status, b.client_id FROM bookings b WHERE b.id = ?',
    [$bookingId]
);
if (!$booking) {
    json_error('Not found.', 404);
}

dbq('UPDATE bookings SET status = ? WHERE id = ?', ['completed', $bookingId]);

// Compute total from booking services.
$total = dbfetch(
    'SELECT COALESCE(SUM(s.base_price * bs.quantity), 0) AS t
       FROM booking_services bs JOIN services s ON s.id = bs.service_id
      WHERE bs.booking_id = ?',
    [$bookingId]
);

$invoiceId = uuid();
$amount = round((float)($total['t'] ?? 0), 2);
$notes = trim($data['notes'] ?? '');
dbq('INSERT INTO invoices (id, booking_id, total_amount, notes) VALUES (?, ?, ?, ?)', [
    $invoiceId, $bookingId, $amount, $notes !== '' ? $notes : null
]);

\Glorikar\Services\NotificationService::onBookingStatusChange($bookingId, 'completed');

// Mark any schedule containing this booking done if all stops are complete.
if ($booking['schedule_id'] ?? null) {
    // (handled by schedule/list in the UI; kept simple here)
}

json_response([
    'ok' => true,
    'booking_id' => $bookingId,
    'invoice_id' => $invoiceId,
    'total_amount' => $amount,
]);