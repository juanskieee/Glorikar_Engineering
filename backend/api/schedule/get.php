<?php
/**
 * GET /api/schedule/get.php?id=X — admin only. Full schedule detail with stops.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';

$id = $_GET['id'] ?? '';
if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
    json_error('Not found.', 404);
}

$schedule = dbfetch(
    'SELECT s.*, t.name AS team_name, t.vehicle AS team_vehicle,
            (SELECT GROUP_CONCAT(u.full_name SEPARATOR ", ") FROM team_members tm
             JOIN users u ON u.id = tm.user_id WHERE tm.team_id = s.team_id) AS team_members
       FROM schedules s
       JOIN teams t ON t.id = s.team_id
      WHERE s.id = ?',
    [$id]
);
if (!$schedule) {
    json_error('Not found.', 404);
}

$stops = dball(
    'SELECT ss.id, ss.stop_order, ss.eta, ss.booking_id,
            b.client_id, b.status AS booking_status, b.address,
            b.latitude, b.longitude, b.preferred_date_from,
            u.full_name AS client_name, u.phone AS client_phone,
            COALESCE(SUM(s.duration_hrs * bs.quantity), 0) AS duration_hrs
       FROM schedule_stops ss
       JOIN bookings b ON b.id = ss.booking_id
       JOIN users u ON u.id = b.client_id
       LEFT JOIN booking_services bs ON bs.booking_id = b.id
       LEFT JOIN services s ON s.id = bs.service_id
      WHERE ss.schedule_id = ?
      GROUP BY ss.id
      ORDER BY ss.stop_order ASC',
    [$id]
);

json_response([
    'ok' => true,
    'schedule' => [
        'id' => $schedule['id'],
        'scheduled_date' => $schedule['scheduled_date'],
        'status' => $schedule['status'],
        'team_id' => $schedule['team_id'],
        'team_name' => $schedule['team_name'],
        'team_vehicle' => $schedule['team_vehicle'],
        'team_members' => $schedule['team_members'],
        'total_distance_km' => $schedule['total_distance_km'] !== null ? (float)$schedule['total_distance_km'] : null,
        'created_at' => $schedule['created_at'],
        'stops' => $stops,
    ],
]);