<?php
// ═══════════════════════════════════════════════════════════
//  GLORIKAR — backend/includes/role-guard.php
//  Include at the top of any ADMIN-ONLY endpoint.
//  Must be included AFTER auth-guard.php (or include it here).
//  Usage:  require_once __DIR__ . '/../includes/role-guard.php';
// ═══════════════════════════════════════════════════════════

declare(strict_types=1);

// Pull in auth-guard first (idempotent — safe to include twice)
require_once __DIR__ . '/auth-guard.php';

if ($SESSION_USER['role'] !== 'admin') {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Forbidden', 'code' => 'ADMIN_ONLY']));
}