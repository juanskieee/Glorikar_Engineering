<?php
// ═══════════════════════════════════════════════════════════
//  POST /api/auth/change-password.php
//  Body: { current_password, new_password }
//  Returns: { success: true }
// ═══════════════════════════════════════════════════════════

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/auth-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_error('Method not allowed', 405);
}

verify_csrf();

$body             = json_body();
$current_password = $body['current_password'] ?? '';
$new_password     = $body['new_password']     ?? '';

// ── Validate ──────────────────────────────────────────────
if ($current_password === '' || $new_password === '') {
    respond_error('Both current and new password are required.', 422);
}
if (strlen($new_password) < 8) {
    respond_error('New password must be at least 8 characters.', 422);
}
if (strlen($new_password) > 128) {
    respond_error('New password too long.', 422);
}

// ── Verify current password ───────────────────────────────
$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$SESSION_USER['id']]);
$user = $stmt->fetch();

if (!$user || !password_verify($current_password, $user['password_hash'])) {
    respond_error('Current password is incorrect.', 401, 'BAD_CREDENTIALS');
}

// ── Hash and save new password ────────────────────────────
$new_hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
$pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
    ->execute([$new_hash, $SESSION_USER['id']]);

// Invalidate all other sessions by regenerating session ID
session_regenerate_id(true);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

respond(['success' => true]);