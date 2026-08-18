<?php
/**
 * role-guard.php — require an ADMIN session.
 * Include on all admin pages and admin API endpoints.
 */

require_once __DIR__ . '/auth-guard.php';

if ($_SESSION['role'] !== 'admin') {
    $isApi = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/');
    if ($isApi) {
        json_error('Forbidden.', 403);
    }
    http_response_code(403);
    exit('Forbidden');
}