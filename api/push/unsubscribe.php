<?php
declare(strict_types=1);
// DELETE /api/push/unsubscribe.php — authenticated
// Body: { endpoint }
// Removes the browser push subscription for the current user.

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/auth-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') respond_error('Method not allowed', 405);
verify_csrf();

$endpoint = trim(json_body()['endpoint'] ?? '');
if ($endpoint === '') respond_error('endpoint is required.', 422);

$pdo->prepare('DELETE FROM push_subscriptions WHERE user_id = ? AND endpoint = ?')
    ->execute([$SESSION_USER['id'], $endpoint]);

respond(['success' => true]);
