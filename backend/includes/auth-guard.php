<?php
// ═══════════════════════════════════════════════════════════
//  GLORIKAR — backend/includes/auth-guard.php
//  Include at the top of any endpoint that requires login.
//  Sets $SESSION_USER array with id, email, role, full_name.
//  Usage:  require_once __DIR__ . '/../includes/auth-guard.php';
// ═══════════════════════════════════════════════════════════

declare(strict_types=1);

// ── Session bootstrap ─────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),   // true in production
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ── Idle timeout (30 min) ────────────────────────────────
$timeout = 30 * 60; // seconds
if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active']) > $timeout) {
    session_unset();
    session_destroy();
    http_response_code(401);
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Session expired', 'code' => 'SESSION_EXPIRED']));
}
$_SESSION['last_active'] = time();

// ── Auth check ────────────────────────────────────────────
if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Unauthorized', 'code' => 'NOT_LOGGED_IN']));
}

// ── Expose session user to the including script ───────────
$SESSION_USER = [
    'id'        => $_SESSION['user_id'],
    'email'     => $_SESSION['email']     ?? '',
    'role'      => $_SESSION['role'],
    'full_name' => $_SESSION['full_name'] ?? '',
];