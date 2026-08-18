<?php
// ═══════════════════════════════════════════════════════════
//  GLORIKAR — backend/includes/csrf.php
//  Session bootstrap + CSRF token provisioning for pages.
//  Safe to require from auth-guard.php and from public pages
//  (login/register) that need a token but no auth check.
// ═══════════════════════════════════════════════════════════

declare(strict_types=1);

// ── Session bootstrap (idempotent) ───────────────────────
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

// ── Ensure a CSRF token exists for this session ──────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function get_csrf_token(): string
{
    return $_SESSION['csrf_token'] ?? '';
}