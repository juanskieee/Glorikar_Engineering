<?php
/**
 * GET /api/services/list.php — list the 5 service types.
 * Used by the client booking flow. Authenticated.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/auth-guard.php';

$services = dball('SELECT id, name, duration_hrs, base_price FROM services ORDER BY id ASC');
json_response(['ok' => true, 'services' => $services]);