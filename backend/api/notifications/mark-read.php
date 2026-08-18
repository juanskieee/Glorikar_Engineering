<?php
/**
 * POST /api/notifications/mark-read.php — mark a notification (or all) read.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/auth-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

csrf_verify();

$user = auth_user();
$data = read_json_input();
$id = $data['id'] ?? null;

if ($id === null || $id === 'all') {
    dbq('UPDATE notifications SET is_read = 1 WHERE user_id = ?', [$user['id']]);
} else {
    dbq('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?', [$id, $user['id']]);
}

json_response(['ok' => true]);