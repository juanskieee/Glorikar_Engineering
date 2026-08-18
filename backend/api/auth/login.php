<?php
/**
 * POST /api/auth/login.php — email + password login.
 * Rate-limited; never reveals whether an email exists.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

rate_limit('login');

start_session_secure();

$data = read_json_input();
$email = mb_strtolower(trim($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');

if (!is_valid_email($email) || $password === '') {
    json_error('Invalid email or password.', 401);
}

$user = dbfetch('SELECT * FROM users WHERE email = ?', [$email]);

// Single generic error for missing user OR wrong password (no user enumeration).
if (!$user || !password_verify($password, $user['password_hash'])) {
    log_auth_event('login_failed', $user['id'] ?? null);
    json_error('Invalid email or password.', 401);
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

log_auth_event('login_success', $user['id']);

json_response([
    'ok' => true,
    'csrf_token' => $_SESSION['csrf_token'],
    'user' => [
        'id' => $user['id'],
        'email' => $user['email'],
        'full_name' => $user['full_name'],
        'role' => $user['role'],
    ],
]);