<?php
/**
 * GET /api/schedule/drafts.php — admin only. Draft schedules awaiting approval.
 * Also supports ?status=approved|dispatched|done via /api/schedule/list.php alias.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';

$status = $_GET['status'] ?? 'draft';

$schedules = dball(
    'SELECT s.*, t.name AS team_name, t.vehicle,
            (SELECT COUNT(*) FROM schedule_stops ss WHERE ss.schedule_id = s.id) AS stop_count
       FROM schedules s
       JOIN teams t ON t.id = s.team_id
      WHERE s.status = ?
      ORDER BY s.scheduled_date ASC, s.created_at ASC',
    [$status]
);

json_response(['ok' => true, 'schedules' => $schedules]);