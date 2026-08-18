<?php
declare(strict_types=1);
// PATCH /api/admin/assign-team.php — assign a booking to a team on a date
// Params: ?id= OR body { booking_id } plus body { team_id, scheduled_date }

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';

function glorikar_uuid(): string
{
    $data    = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') respond_error('Method not allowed', 405);

$body       = json_body();
$bookingId  = trim($_GET['id'] ?? $body['booking_id'] ?? $body['id'] ?? '');
$teamId     = trim($body['team_id'] ?? '');
$date       = trim($body['scheduled_date'] ?? '');

if ($bookingId === '' || $teamId === '') respond_error('booking_id and team_id are required.', 422);

$stmt = $pdo->prepare('SELECT id FROM bookings WHERE id = ?');
$stmt->execute([$bookingId]);
if (!$stmt->fetch()) respond_error('Booking not found.', 404);

$stmt = $pdo->prepare('SELECT id FROM teams WHERE id = ?');
$stmt->execute([$teamId]);
if (!$stmt->fetch()) respond_error('Team not found.', 404);

if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = date('Y-m-d');
}

// Reuse an existing schedule for this team/date, or create a draft one
$stmt = $pdo->prepare('SELECT id FROM schedules WHERE team_id = ? AND scheduled_date = ? LIMIT 1');
$stmt->execute([$teamId, $date]);
$schedule = $stmt->fetch();

if ($schedule) {
    $scheduleId = $schedule['id'];
} else {
    $scheduleId = glorikar_uuid();
    $pdo->prepare('INSERT INTO schedules (id, scheduled_date, team_id, status) VALUES (?, ?, ?, \'draft\')')
       ->execute([$scheduleId, $date, $teamId]);
}

$stmt = $pdo->prepare('SELECT COALESCE(MAX(stop_order), 0) AS mx FROM schedule_stops WHERE schedule_id = ?');
$stmt->execute([$scheduleId]);
$stopOrder = (int)$stmt->fetch()['mx'] + 1;

$pdo->prepare('INSERT INTO schedule_stops (id, schedule_id, booking_id, stop_order) VALUES (?, ?, ?, ?)')
   ->execute([glorikar_uuid(), $scheduleId, $bookingId, $stopOrder]);

$pdo->prepare('UPDATE bookings SET schedule_id = ?, status = \'scheduled\' WHERE id = ?')
   ->execute([$scheduleId, $bookingId]);

respond([
    'success'    => true,
    'booking_id' => $bookingId,
    'schedule'   => [
        'id'             => $scheduleId,
        'team_id'        => $teamId,
        'scheduled_date' => $date,
        'stop_order'     => $stopOrder,
    ],
]);