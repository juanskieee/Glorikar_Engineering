<?php
/**
 * auth-guard.php — require an authenticated session for a page or API.
 * Redirects (HTML pages) or returns 401 JSON (API endpoints).
 */

require_once __DIR__ . '/helpers.php';

start_session_secure();

if (empty($_SESSION['user_id'])) {
    $isApi = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/');
    if ($isApi) {
        json_error('Not authenticated.', 401);
    }
    header('Location: ' . Env::get('APP_URL', '/') . '/login.php');
    exit;
}

$GLOBALS['current_user_row'] = dbfetch('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
if (!$GLOBALS['current_user_row']) {
    session_unset();
    session_destroy();
    if (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) {
        json_error('Session invalid.', 401);
    }
    header('Location: ' . Env::get('APP_URL', '/') . '/login.php');
    exit;
}

/** Convenience accessor for the authed user. */
function auth_user(): array
{
    return $GLOBALS['current_user_row'];
}

/** Convenience accessor for the session role. */
function auth_role(): string
{
    return $_SESSION['role'] ?? '';
}