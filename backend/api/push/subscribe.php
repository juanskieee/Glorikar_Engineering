<?php
/**
 * POST /api/push/subscribe.php — store a Web Push subscription for the user.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/auth-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

$user = auth_user();
$data = read_json_input();

$endpoint = trim($data['endpoint'] ?? '');
$p256dh = trim($data['keys']['p256dh'] ?? '');
$auth = trim($data['keys']['auth'] ?? '');

if (!str_starts_with($endpoint, 'https://') || $p256dh === '' || $auth === '') {
    json_error('Invalid push subscription.', 422);
}

// Reuse existing subscription if present.
$existing = dbfetch('SELECT id FROM push_subscriptions WHERE endpoint = ?', [$endpoint]);
if ($existing) {
    dbq('UPDATE push_subscriptions SET user_id = ?, p256dh = ?, auth = ? WHERE endpoint = ?', [
        $user['id'], $p256dh, $auth, $endpoint
    ]);
} else {
    dbq('INSERT INTO push_subscriptions (id, user_id, endpoint, p256dh, auth) VALUES (?, ?, ?, ?, ?)', [
        uuid(), $user['id'], $endpoint, $p256dh, $auth
    ]);
}

json_response(['ok' => true], 201);