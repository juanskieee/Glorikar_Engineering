<?php
declare(strict_types=1);
// POST /api/schedule/run.php — admin only
// Body: { date?: "YYYY-MM-DD" }  (defaults to tomorrow)
// Runs the full scheduling engine and returns a summary.

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';
require_once __DIR__ . '/../../backend/services/SchedulingEngine.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond_error('Method not allowed', 405);

// Allow cron to bypass CSRF (CRON_MODE is set by the cron wrapper)
if (!defined('CRON_MODE')) verify_csrf();

$body = json_body();
$date = trim($body['date'] ?? '');

if ($date === '') {
    $date = date('Y-m-d', strtotime('+1 day'));
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
    respond_error('Invalid date format. Use YYYY-MM-DD.', 422);
}

$engine = new SchedulingEngine($pdo);

try {
    $result = $engine->run($date);
} catch (Throwable $e) {
    error_log('Scheduling engine error: ' . $e->getMessage());
    respond_error('Scheduling failed. Check server logs.', 500, 'ENGINE_ERROR');
}

respond(array_merge(['date' => $date], $result));