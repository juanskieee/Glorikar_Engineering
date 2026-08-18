<?php
declare(strict_types=1);
// GET /api/admin/schedule.php
//   ?id=         → single schedule with its ordered stops
//   ?from=&to=   → week grid of { days: [{ date, total_jobs, jobs_by_team }] }

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$svcStmt = $pdo->prepare('
    SELECT s.name FROM services s
    JOIN booking_services bs ON bs.service_id = s.id
    WHERE bs.booking_id = ?
');

$stopStmt = $pdo->prepare('
    SELECT ss.stop_order, ss.eta,
           b.id AS booking_id, u.full_name AS client_name, b.address, b.status
    FROM schedule_stops ss
    JOIN bookings b ON b.id = ss.booking_id
    JOIN users u    ON u.id = b.client_id
    WHERE ss.schedule_id = ?
    ORDER BY ss.stop_order ASC
');

// ── Single schedule ──────────────────────────────────────
$id = trim($_GET['id'] ?? '');
if ($id !== '') {
    $stmt = $pdo->prepare('
        SELECT s.id, s.scheduled_date, s.status, s.total_distance_km,
               t.id AS team_id, t.name AS team_name
        FROM schedules s
        JOIN teams t ON t.id = s.team_id
        WHERE s.id = ?
    ');
    $stmt->execute([$id]);
    $schedule = $stmt->fetch();

    if (!$schedule) respond_error('Schedule not found.', 404);

    $stopStmt->execute([$id]);
    $stops = $stopStmt->fetchAll();
    foreach ($stops as &$st) {
        $svcStmt->execute([$st['booking_id']]);
        $st['services'] = array_column($svcStmt->fetchAll(), 'name');
    }
    unset($st);

    $schedule['stops'] = $stops;
    respond(['schedule' => $schedule]);
}

// ── Week grid ────────────────────────────────────────────
$from = trim($_GET['from'] ?? '');
$to   = trim($_GET['to']   ?? '');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    $from = date('Y-m-d', strtotime('monday this week'));
    $to   = date('Y-m-d', strtotime('sunday this week'));
}

$days    = [];
$cursor  = new DateTime($from);
$end     = new DateTime($to);
$end->setTime(23, 59, 59);
$interval = new DateInterval('P1D');

$schedStmt = $pdo->prepare('
    SELECT s.id, t.id AS team_id, t.name AS team_name
    FROM schedules s
    JOIN teams t ON t.id = s.team_id
    WHERE s.scheduled_date = ?
    ORDER BY t.name
');

while ($cursor <= $end) {
    $d = $cursor->format('Y-m-d');

    $schedStmt->execute([$d]);
    $schedules = $schedStmt->fetchAll();

    $jobsByTeam = [];
    $totalJobs  = 0;

    foreach ($schedules as $sch) {
        $stopStmt->execute([$sch['id']]);
        $jobs = $stopStmt->fetchAll();
        foreach ($jobs as &$jb) {
            $svcStmt->execute([$jb['booking_id']]);
            $jb['services'] = array_column($svcStmt->fetchAll(), 'name');
        }
        unset($jb);

        $totalJobs += count($jobs);
        $jobsByTeam[] = [
            'team_id'   => $sch['team_id'],
            'team_name' => $sch['team_name'],
            'jobs'      => $jobs,
        ];
    }

    $days[] = ['date' => $d, 'total_jobs' => $totalJobs, 'jobs_by_team' => $jobsByTeam];
    $cursor->add($interval);
}

respond(['days' => $days]);