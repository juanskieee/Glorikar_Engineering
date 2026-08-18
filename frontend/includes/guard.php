<?php
/**
 * guard.php — require an authenticated session on a frontend page.
 * Redirects to login.php when unauthenticated. Exposes $GUARD_USER.
 */

require_once dirname(__DIR__, 2) . '/backend/includes/auth-guard.php';
require_once __DIR__ . '/helpers.php';

$GUARD_USER = auth_user();
$GUARD_ROLE = $GUARD_USER['role'];