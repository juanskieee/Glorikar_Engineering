<?php
// ═══════════════════════════════════════════════════════════
//  GLORIKAR — backend/cron/run-scheduling.php
//  CLI script that runs the scheduling engine for today.
//  Usage:  php backend/cron/run-scheduling.php [Y-m-d]
//  Logs the result to backend/logs/cron.log
// ═══════════════════════════════════════════════════════════

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the CLI.\n");
    exit(1);
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../services/SchedulingEngine.php';

$logFile = __DIR__ . '/../logs/cron.log';

try {
    // Accept an optional target date, otherwise run for today
    $date = $argv[1] ?? date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        throw new InvalidArgumentException("Invalid date: $date (expected Y-m-d).");
    }

    $engine = new SchedulingEngine($pdo);
    $result = $engine->run($date);

    $line = sprintf(
        "[%s] date=%s schedules_created=%d bookings_scheduled=%d bookings_deferred=%d\n",
        date('Y-m-d H:i:s'),
        $date,
        $result['schedules_created'],
        $result['bookings_scheduled'],
        $result['bookings_deferred']
    );
    file_put_contents($logFile, $line, FILE_APPEND);

    echo $line;
    exit(0);
} catch (Throwable $e) {
    $line = sprintf(
        "[%s] ERROR: %s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage()
    );
    file_put_contents($logFile, $line, FILE_APPEND);

    fwrite(STDERR, $line);
    exit(1);
}