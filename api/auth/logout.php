<?php
// ═══════════════════════════════════════════════════════════
//  POST /api/auth/logout.php
//  Destroys the session. No body needed.
//  Returns: { success: true }
// ═══════════════════════════════════════════════════════════

require_once __DIR__ . '/../_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_error('Method not allowed', 405);
}

if (session_status() === PHP_SESSION_NONE) session_start();

// Wipe session data
$_SESSION = [];

// Destroy session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

respond(['success' => true]);