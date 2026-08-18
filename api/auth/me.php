<?php
// ═══════════════════════════════════════════════════════════
//  GET /api/auth/me.php
//  Returns the logged-in user's profile + fresh CSRF token.
//  Used on every page load to check auth state and get role.
//  Returns: { user: { id, email, full_name, phone, address, role }, csrf_token }
// ═══════════════════════════════════════════════════════════

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/auth-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond_error('Method not allowed', 405);
}

// Fetch fresh user data from DB (don't rely solely on session cache)
$stmt = $pdo->prepare('
    SELECT id, email, full_name, phone, address, latitude, longitude, role, created_at
    FROM users
    WHERE id = ?
    LIMIT 1
');
$stmt->execute([$SESSION_USER['id']]);
$user = $stmt->fetch();

if (!$user) {
    // User deleted from DB but session still active — force logout
    session_unset();
    session_destroy();
    respond_error('User not found', 401, 'USER_NOT_FOUND');
}

// Rotate CSRF token on each me.php call
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

respond([
    'user'       => $user,
    'csrf_token' => $_SESSION['csrf_token'],
]);