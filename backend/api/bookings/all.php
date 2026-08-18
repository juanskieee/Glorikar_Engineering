<?php
/**
 * GET /api/bookings/all.php — admin only. All bookings, optionally filtered.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';

$status = $_GET['status'] ?? '';
$sql = "SELECT b.*, u.full_name AS client_name, u.phone AS client_phone,
               COALESCE(SUM(bs.quantity), 0) AS services_count
          FROM bookings b
          JOIN users u ON u.id = b.client_id
          LEFT JOIN booking_services bs ON bs.booking_id = b.id
          GROUP BY b.id
          ORDER BY b.created_at DESC";
$params = [];

if ($status !== '') {
    $sql = str_replace('GROUP BY b.id', 'WHERE b.status = :status GROUP BY b.id', $sql);
    $params[':status'] = $status;
}

$bookings = dball($sql, $params);
json_response(['ok' => true, 'bookings' => $bookings]);