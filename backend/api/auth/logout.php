<?php
/**
 * POST /api/auth/logout.php — destroy the session.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/helpers.php';

start_session_secure();
$userId = $_SESSION['user_id'] ?? null;
log_auth_event('logout', $userId);

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

json_response(['ok' => true]);