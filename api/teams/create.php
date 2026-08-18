<?php
declare(strict_types=1);
// POST /api/teams/create.php — admin only
// Body: { name, vehicle? }

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond_error('Method not allowed', 405);
verify_csrf();

$body    = json_body();
$name    = trim($body['name']    ?? '');
$vehicle = trim($body['vehicle'] ?? '');

if ($name === '')          respond_error('Team name is required.', 422);
if (strlen($name) > 100)   respond_error('Team name too long (max 100 chars).', 422);
if (strlen($vehicle) > 100) respond_error('Vehicle name too long (max 100 chars).', 422);

$pdo->prepare('
    INSERT INTO teams (id, name, vehicle, is_available)
    VALUES (UUID(), ?, ?, TRUE)
')->execute([$name, $vehicle ?: null]);

$team = $pdo->prepare('SELECT * FROM teams WHERE name = ? ORDER BY created_at DESC LIMIT 1');
$team->execute([$name]);
$team = $team->fetch();
$team['members'] = [];

respond(['team' => $team], 201);