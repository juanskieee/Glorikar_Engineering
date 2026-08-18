<?php
declare(strict_types=1);
// PATCH /api/notifications/read-all.php — authenticated
// Stub — marks all notifications as read once push is wired up.

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/auth-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') respond_error('Method not allowed', 405);

respond(['success' => true]);