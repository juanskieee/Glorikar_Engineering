<?php
/**
 * PATCH /api/schedule/approve.php — admin only. Approve a draft schedule.
 * Also accepts optional stop reordering / team reassignment before approval.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

csrf_verify();

$data = read_json_input();
$id = $data['id'] ?? '';
if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
    json_error('Not found.', 404);
}

$schedule = dbfetch('SELECT id, status FROM schedules WHERE id = ?', [$id]);
if (!$schedule) {
    json_error('Not found.', 404);
}
if ($schedule['status'] !== 'draft') {
    json_error('Only draft schedules can be approved.', 409);
}

// Optional: reassign team
if (!empty($data['team_id']) && preg_match('/^[0-9a-fA-F-]{36}$/', $data['team_id'])) {
    dbq('UPDATE schedules SET team_id = ? WHERE id = ?', [$data['team_id'], $id]);
}

// Optional: reorder stops. $data['stops'] = [ [booking_id, stop_order], ... ]
if (!empty($data['stops']) && is_array($data['stops'])) {
    foreach ($data['stops'] as $stop) {
        $bookingId = $stop['booking_id'] ?? null;
        $order = (int)($stop['stop_order'] ?? 0);
        if ($bookingId && preg_match('/^[0-9a-fA-F-]{36}$/', $bookingId)) {
            dbq('UPDATE schedule_stops SET stop_order = ? WHERE schedule_id = ? AND booking_id = ?', [$order, $id, $bookingId]);
        }
    }
}

dbq('UPDATE schedules SET status = ? WHERE id = ?', ['approved', $id]);

json_response(['ok' => true, 'status' => 'approved']);