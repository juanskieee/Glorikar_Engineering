<?php
declare(strict_types=1);
// GET /api/jobs/get-invoice.php — authenticated (clients may fetch their own)
// Params: ?booking_id=  OR  ?invoice_id=
// Returns invoice + booking summary.

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/auth-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$bookingId = trim($_GET['booking_id'] ?? '');
$invoiceId = trim($_GET['invoice_id'] ?? '');

if ($bookingId === '' && $invoiceId === '') {
    respond_error('booking_id or invoice_id is required.', 422);
}

// Resolve the booking
if ($invoiceId !== '') {
    $stmt = $pdo->prepare('SELECT * FROM invoices WHERE id = ? LIMIT 1');
    $stmt->execute([$invoiceId]);
    $invoice = $stmt->fetch();
    if (!$invoice) respond_error('Invoice not found.', 404);
    $bookingId = $invoice['booking_id'];
} else {
    $stmt = $pdo->prepare('SELECT * FROM invoices WHERE booking_id = ? LIMIT 1');
    $stmt->execute([$bookingId]);
    $invoice = $stmt->fetch();
}

$stmt = $pdo->prepare('
    SELECT b.id, b.status, b.client_id, b.address, b.preferred_date_from, b.preferred_date_to,
           b.notes, b.created_at, s.scheduled_date
    FROM bookings b
    LEFT JOIN schedules s ON s.id = b.schedule_id
    WHERE b.id = ?
');
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if (!$booking) respond_error('Booking not found.', 404);

// Clients may only view their own invoice; admins may view any
$isAdmin = ($SESSION_USER['role'] === 'admin');
if (!$isAdmin && $booking['client_id'] !== $SESSION_USER['id']) {
    respond_error('Forbidden', 403, 'NOT_OWNER');
}

if (!$invoice) {
    respond_error('Invoice not generated yet.', 404, 'NO_INVOICE');
}

// Line items from the booking's services
$lineStmt = $pdo->prepare('
    SELECT s.name AS description, bs.quantity AS qty, s.base_price AS unit_price
    FROM booking_services bs
    JOIN services s ON s.id = bs.service_id
    WHERE bs.booking_id = ?
');
$lineStmt->execute([$bookingId]);
$invoice['line_items'] = $lineStmt->fetchAll();

respond([
    'invoice' => $invoice,
    'booking' => $booking,
]);