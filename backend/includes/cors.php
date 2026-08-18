<?php
/**
 * cors.php — restrict API access to known frontend origins.
 * Include at the very top of every /api/* endpoint.
 */

require_once __DIR__ . '/env.php';

$allowedOrigins = array_filter(array_map('trim', explode(',', Env::get('ALLOWED_ORIGINS', ''))));
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
    header('Access-Control-Allow-Credentials: true');
}

// No wildcard — requests from unknown origins simply get no CORS headers,
// so browsers will block the response.

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}