<?php
declare(strict_types=1);
// GET   /api/admin/invoice.php?id=      → stored invoice with line items
// GET   /api/admin/invoice.php?booking= → estimate derived from a booking
// PATCH /api/admin/invoice.php?id=      → body { paid: true|false }

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';

$id      = trim($_GET['id'] ?? '');
$booking = trim($_GET['booking'] ?? '');
$method  = $_SERVER['REQUEST_METHOD'];

if ($method === 'PATCH') {
    if ($id === '') respond_error('id is required.', 422);

    $body = json_body();
    $stmt = $pdo->prepare('SELECT id FROM invoices WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) respond_error('Invoice not found.', 404);

    if (array_key_exists('paid', $body)) {
        $pdo->prepare('UPDATE invoices SET paid = ? WHERE id = ?')
            ->execute([(int)(bool)$body['paid'], $id]);
    }

    respond(['success' => true]);
}

if ($method !== 'GET') respond_error('Method not allowed', 405);

if ($id === '' && $booking === '') respond_error('id or booking is required.', 422);

if ($id !== '') {
    $stmt = $pdo->prepare('
        SELECT i.id, i.booking_id, i.total_amount, i.issued_at, i.paid, i.notes,
               u.full_name AS client_name, u.email AS client_email, u.phone AS client_phone,
               b.address, s.scheduled_date
        FROM invoices i
        JOIN bookings b ON b.id = i.booking_id
        JOIN users u    ON u.id = b.client_id
        LEFT JOIN schedules s ON s.id = b.schedule_id
        WHERE i.id = ?
    ');
    $stmt->execute([$id]);
    $invoice = $stmt->fetch();

    if (!$invoice) respond_error('Invoice not found.', 404);
} else {
    // No invoice yet — build an estimate from the booking
    $stmt = $pdo->prepare('
        SELECT u.full_name AS client_name, u.email AS client_email, u.phone AS client_phone,
               b.address, s.scheduled_date
        FROM bookings b
        JOIN users u ON u.id = b.client_id
        LEFT JOIN schedules s ON s.id = b.schedule_id
        WHERE b.id = ?
    ');
    $stmt->execute([$booking]);
    $client = $stmt->fetch();

    if (!$client) respond_error('Booking not found.', 404);

    $invoice = array_merge([
        'id'           => null,
        'booking_id'   => $booking,
        'total_amount' => null,
        'issued_at'    => null,
        'paid'         => false,
        'notes'        => null,
    ], $client);
}

$lineStmt = $pdo->prepare('
    SELECT s.name AS description, bs.quantity AS qty, s.base_price AS unit_price
    FROM booking_services bs
    JOIN services s ON s.id = bs.service_id
    WHERE bs.booking_id = ?
');
$lineStmt->execute([$invoice['booking_id']]);
$invoice['line_items'] = $lineStmt->fetchAll();

respond(['invoice' => $invoice]);