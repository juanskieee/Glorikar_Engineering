<?php
/**
 * POST /api/teams/create.php — admin only. Create a team.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

csrf_verify();

$data = read_json_input();
$name = trim($data['name'] ?? '');
$vehicle = trim($data['vehicle'] ?? '');

if (mb_strlen($name) < 2) {
    json_error('Team name is required.', 422);
}

$id = uuid();
dbq('INSERT INTO teams (id, name, vehicle, is_available) VALUES (?, ?, ?, ?)', [
    $id, $name, $vehicle !== '' ? $vehicle : null, true
]);

json_response(['ok' => true, 'team' => ['id' => $id, 'name' => $name, 'vehicle' => $vehicle]], 201);