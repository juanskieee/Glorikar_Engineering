<?php
declare(strict_types=1);
// GET /api/schedule/drafts.php — admin only
// Returns all draft + approved schedules with full stop detail.

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$schedules = $pdo->query("
    SELECT
        sc.id, sc.scheduled_date, sc.status, sc.total_distance_km, sc.created_at,
        t.id AS team_id, t.name AS team_name, t.vehicle
    FROM schedules sc
    JOIN teams t ON t.id = sc.team_id
    WHERE sc.status IN ('draft', 'approved')
    ORDER BY sc.scheduled_date ASC, sc.created_at ASC
")->fetchAll();

$stopStmt = $pdo->prepare("
    SELECT
        ss.id AS stop_id, ss.stop_order, ss.eta,
        b.id AS booking_id, b.address, b.latitude, b.longitude, b.notes, b.status AS booking_status,
        u.id AS client_id, u.full_name AS client_name, u.phone AS client_phone
    FROM schedule_stops ss
    JOIN bookings b ON b.id = ss.booking_id
    JOIN users u    ON u.id = b.client_id
    WHERE ss.schedule_id = ?
    ORDER BY ss.stop_order ASC
");

$svcStmt = $pdo->prepare('
    SELECT bs.quantity, s.name, s.base_price, s.duration_hrs
    FROM booking_services bs
    JOIN services s ON s.id = bs.service_id
    WHERE bs.booking_id = ?
');

foreach ($schedules as &$sc) {
    $stopStmt->execute([$sc['id']]);
    $stops = $stopStmt->fetchAll();

    foreach ($stops as &$stop) {
        $svcStmt->execute([$stop['booking_id']]);
        $stop['services'] = $svcStmt->fetchAll();
    }
    unset($stop);

    $sc['stops'] = $stops;
}
unset($sc);

respond(['schedules' => $schedules]);