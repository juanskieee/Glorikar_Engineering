<?php
declare(strict_types=1);
// POST /api/admin/run-scheduler.php — run the scheduling engine
// Body (optional): { date: "Y-m-d" } — defaults to today

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond_error('Method not allowed', 405);

$body = json_body();
$date = trim($body['date'] ?? '');
if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

require_once __DIR__ . '/../../backend/services/SchedulingEngine.php';

$engine = new SchedulingEngine($pdo);
$result = $engine->run($date);

respond(array_merge(['success' => true, 'date' => $date], $result));