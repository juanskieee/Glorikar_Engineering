<?php
/**
 * POST /api/schedule/run.php — admin only. Manually trigger the scheduling engine.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';
require_once __DIR__ . '/../../services/SchedulingEngine.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

csrf_verify();

$data = read_json_input();
$from = $data['date_from'] ?? null;
$to = $data['date_to'] ?? null;

$engine = new \Glorikar\Services\SchedulingEngine();
$summary = $engine->run($from, $to);

json_response(['ok' => true, 'summary' => $summary]);