<?php
/**
 * run-scheduling.php — nightly cron entry point.
 * Cron: 0 22 * * * php /path/to/backend/cron/run-scheduling.php
 * Triggers the scheduling engine for the upcoming window.
 */

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../services/MapboxService.php';
require_once __DIR__ . '/../services/ClusterService.php';
require_once __DIR__ . '/../services/TripScorer.php';
require_once __DIR__ . '/../services/RouteOptimizer.php';
require_once __DIR__ . '/../services/SchedulingEngine.php';

// Guard: only run via CLI or an internal token to avoid public triggering.
if (php_sapi_name() !== 'cli') {
    $token = Env::get('CRON_TOKEN', '');
    $reqToken = $_GET['token'] ?? '';
    if ($token === '' || !hash_equals($token, $reqToken)) {
        http_response_code(403);
        exit('Forbidden');
    }
}

$engine = new \Glorikar\Services\SchedulingEngine();
$summary = $engine->run();

$line = date('Y-m-d H:i:s') . ' scheduling run: '
    . json_encode($summary, JSON_UNESCAPED_SLASHES) . PHP_EOL;
file_put_contents(__DIR__ . '/scheduling.log', $line, FILE_APPEND);

if (php_sapi_name() === 'cli') {
    echo $line;
}