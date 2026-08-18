<?php
/**
 * POST /api/auth/forgot-password.php — send a password reset email.
 * Rate-limited; always returns the same generic message (no user enumeration).
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../services/MailService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

rate_limit('forgot');

start_session_secure();

$data = read_json_input();
$email = mb_strtolower(trim($data['email'] ?? ''));

if (!is_valid_email($email)) {
    json_error('Enter a valid email address.', 422);
}

$user = dbfetch('SELECT id, email, full_name FROM users WHERE email = ?', [$email]);

// Always succeed on the surface; only send when an account exists.
if ($user) {
    // Revoke any previous unused tokens for this user.
    dbq('DELETE FROM password_resets WHERE user_id = ?', [$user['id']]);

    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
    dbq(
        'INSERT INTO password_resets (id, user_id, token_hash, expires_at) VALUES (?, ?, ?, ?)',
        [uuid(), $user['id'], hash('sha256', $token), $expires]
    );

    $base = rtrim((string)\Env::get('RESET_LINK_BASE', ''), '/');
    if ($base === '') {
        // Dev fallback — production should set RESET_LINK_BASE to the canonical
        // host so the link can't be rewritten via a forged Host header.
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = $scheme . '://' . $host;
    }
    $resetUrl = $base . '/reset-password.php?token=' . $token;

    $name = $user['full_name'] ?: $user['email'];
    $body = '<p>Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>'
        . '<p>We received a request to reset your Glorikar Engineering password. '
        . 'Click the button below to choose a new one. This link expires in 1 hour.</p>';
    $sent = \Glorikar\Services\MailService::send(
        $user['email'],
        $name,
        'Reset your Glorikar Engineering password',
        \Glorikar\Services\MailService::template('Password reset', $body, $resetUrl, 'Reset my password')
    );

    if ($sent) {
        log_auth_event('password_reset_requested', $user['id']);
    } else {
        log_auth_event('password_reset_email_failed', $user['id']);
    }
}

// Generic response regardless of whether the account exists.
json_response([
    'ok' => true,
    'message' => 'If that email is registered, a reset link has been sent. Check your inbox (and spam folder).',
]);