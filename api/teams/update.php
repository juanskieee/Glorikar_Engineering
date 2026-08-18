<?php
declare(strict_types=1);
// PATCH /api/teams/update.php — admin only
// Body: { id, name?, vehicle?, is_available? }

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') respond_error('Method not allowed', 405);
verify_csrf();

$body = json_body();
$id   = trim($body['id'] ?? '');
if ($id === '') respond_error('id is required.', 422);

$stmt = $pdo->prepare('SELECT * FROM teams WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$team = $stmt->fetch();
if (!$team) respond_error('Team not found.', 404);

// Build partial update
$fields = [];
$params = [];

if (isset($body['name'])) {
    $name = trim($body['name']);
    if ($name === '')          respond_error('name cannot be empty.', 422);
    if (strlen($name) > 100)   respond_error('name too long.', 422);
    $fields[] = 'name = ?';
    $params[] = $name;
}

if (array_key_exists('vehicle', $body)) {
    $vehicle = trim($body['vehicle'] ?? '');
    if (strlen($vehicle) > 100) respond_error('vehicle too long.', 422);
    $fields[] = 'vehicle = ?';
    $params[] = $vehicle ?: null;
}

if (isset($body['is_available'])) {
    if (!is_bool($body['is_available'])) respond_error('is_available must be boolean.', 422);
    $fields[] = 'is_available = ?';
    $params[] = $body['is_available'] ? 1 : 0;
}

if (empty($fields)) respond_error('No fields to update.', 422);

$params[] = $id;
$pdo->prepare('UPDATE teams SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($params);

$stmt->execute([$id]);
respond(['team' => $stmt->fetch()]);