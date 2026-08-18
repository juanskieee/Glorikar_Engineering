<?php
/**
 * PATCH /api/teams/update.php — admin only. Rename / vehicle / availability toggle.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

csrf_verify();

$data = read_json_input();
$id = $data['id'] ?? '';
if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
    json_error('Not found.', 404);
}

$team = dbfetch('SELECT id FROM teams WHERE id = ?', [$id]);
if (!$team) {
    json_error('Not found.', 404);
}

$updates = [];
$params = [':id' => $id];

if (isset($data['name']) && mb_strlen(trim($data['name'])) >= 2) {
    $updates[] = 'name = :name';
    $params[':name'] = trim($data['name']);
}
if (array_key_exists('vehicle', $data)) {
    $updates[] = 'vehicle = :vehicle';
    $params[':vehicle'] = trim($data['vehicle']) !== '' ? trim($data['vehicle']) : null;
}
if (array_key_exists('is_available', $data)) {
    $updates[] = 'is_available = :available';
    $params[':available'] = $data['is_available'] ? 1 : 0;
}

if ($updates) {
    dbq('UPDATE teams SET ' . implode(', ', $updates) . ' WHERE id = :id', $params);
}

json_response(['ok' => true]);