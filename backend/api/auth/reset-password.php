<?php
/**
 * POST /api/auth/reset-password.php — set a new password with a reset token.
 * Token is single-use and expires after 1 hour.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

start_session_secure();
rate_limit('reset');
csrf_verify();

$data = read_json_input();
$token = trim($data['token'] ?? '');
$password = (string)($data['password'] ?? '');

if ($token === '' || !preg_match('/^[0-9a-f]{64}$/', $token)) {
    $token = ''; // only raw 64-hex tokens are accepted below
}

if (!is_strong_password($password)) {
    json_error('Password must be at least 8 characters with letters and numbers.', 422);
}

$row = dbfetch(
    'SELECT pr.id, pr.user_id, pr.expires_at FROM password_resets pr
     WHERE pr.token_hash = ? AND pr.used_at IS NULL',
    [hash('sha256', $token)]
);

if (!$row) {
    json_error('This reset link is invalid or has already been used.', 400);
}

if (strtotime($row['expires_at']) < time()) {
    json_error('This reset link has expired. Please request a new one.', 400);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
dbq('UPDATE users SET password_hash = ? WHERE id = ?', [$hash, $row['user_id']]);
dbq('UPDATE password_resets SET used_at = NOW() WHERE id = ?', [$row['id']]);
// Revoke any sibling tokens for this user (paranoia).
dbq('DELETE FROM password_resets WHERE user_id = ? AND id <> ?', [$row['user_id'], $row['id']]);

log_auth_event('password_reset_completed', $row['user_id']);

json_response([
    'ok' => true,
    'message' => 'Password updated. You can now sign in with your new password.',
]);