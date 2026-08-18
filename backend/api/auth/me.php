<?php
/**
 * GET /api/auth/me.php — current user + role from the server session.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/auth-guard.php';

$user = auth_user();

json_response([
    'ok' => true,
    'user' => [
        'id' => $user['id'],
        'email' => $user['email'],
        'full_name' => $user['full_name'],
        'phone' => $user['phone'],
        'address' => $user['address'],
        'role' => $user['role'],
        'latitude' => $user['latitude'] !== null ? (float)$user['latitude'] : null,
        'longitude' => $user['longitude'] !== null ? (float)$user['longitude'] : null,
        'created_at' => $user['created_at'],
    ],
]);