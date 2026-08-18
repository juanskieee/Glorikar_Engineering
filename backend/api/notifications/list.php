<?php
/**
 * GET /api/notifications/list.php — current user's notifications.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/auth-guard.php';

$user = auth_user();

$notifications = dball(
    'SELECT id, title, message, type, is_read, created_at
       FROM notifications WHERE user_id = ?
      ORDER BY created_at DESC LIMIT 100',
    [$user['id']]
);

json_response(['ok' => true, 'notifications' => $notifications]);