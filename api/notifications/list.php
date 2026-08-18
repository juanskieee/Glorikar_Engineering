<?php
declare(strict_types=1);
// GET /api/notifications/list.php — authenticated
// Stub — returns empty list until Web Push / notifications are wired up.

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/auth-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

respond(['notifications' => []]);