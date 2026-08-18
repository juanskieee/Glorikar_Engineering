<?php
declare(strict_types=1);
// POST /api/push/subscribe.php — authenticated
// Body: { endpoint, keys: { p256dh, auth } }
// Upserts the browser push subscription for the current user.

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/auth-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond_error('Method not allowed', 405);
verify_csrf();

$body     = json_body();
$endpoint = trim($body['endpoint'] ?? '');
$p256dh   = trim($body['keys']['p256dh'] ?? '');
$auth     = trim($body['keys']['auth'] ?? '');

if ($endpoint === '' || $p256dh === '' || $auth === '') {
    respond_error('endpoint and keys.p256dh/keys.auth are required.', 422);
}

$pdo->prepare('
    INSERT INTO push_subscriptions (user_id, endpoint, p256dh_key, auth_key)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        p256dh_key = VALUES(p256dh_key),
        auth_key   = VALUES(auth_key)
')->execute([$SESSION_USER['id'], $endpoint, $p256dh, $auth]);

respond(['success' => true]);
