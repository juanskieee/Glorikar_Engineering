<?php
declare(strict_types=1);
// PATCH /api/schedule/approve.php — admin only
// Body: { id }

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') respond_error('Method not allowed', 405);
verify_csrf();

$id = trim(json_body()['id'] ?? '');
if ($id === '') respond_error('id is required.', 422);

$stmt = $pdo->prepare('SELECT id, status FROM schedules WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$schedule = $stmt->fetch();

if (!$schedule)                        respond_error('Schedule not found.', 404);
if ($schedule['status'] !== 'draft')   respond_error('Only draft schedules can be approved.', 400, 'INVALID_STATUS');

$pdo->prepare('UPDATE schedules SET status = \'approved\' WHERE id = ?')->execute([$id]);

respond(['schedule' => ['id' => $id, 'status' => 'approved']]);