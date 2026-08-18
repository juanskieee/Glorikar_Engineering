<?php
/**
 * GET /api/bookings/pending.php — admin only. Pending bookings (for scheduling).
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';

$bookings = dball(
    "SELECT b.*, u.full_name AS client_name, u.phone AS client_phone,
            COALESCE(SUM(bs.quantity), 0) AS services_count,
            COALESCE(SUM(s.duration_hrs * bs.quantity), 0) AS duration_hrs
       FROM bookings b
       JOIN users u ON u.id = b.client_id
       LEFT JOIN booking_services bs ON bs.booking_id = b.id
       LEFT JOIN services s ON s.id = bs.service_id
      WHERE b.status = 'pending'
      GROUP BY b.id
      ORDER BY b.preferred_date_from ASC, b.created_at ASC"
);

json_response(['ok' => true, 'bookings' => $bookings]);