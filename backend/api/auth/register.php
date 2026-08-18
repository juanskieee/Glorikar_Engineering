<?php
/**
 * POST /api/auth/register.php — create a client account.
 * Rate-limited, bcrypt-hashed password, session regenerated on success.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

start_session_secure();
rate_limit('register');
csrf_verify();

$data = read_json_input();
$email = trim($data['email'] ?? '');
$password = (string)($data['password'] ?? '');
$fullName = trim($data['full_name'] ?? '');
$phone = trim($data['phone'] ?? '');
$address = trim($data['address'] ?? '');
$latitude = isset($data['latitude']) ? (float)$data['latitude'] : null;
$longitude = isset($data['longitude']) ? (float)$data['longitude'] : null;

// Validate
$errors = [];
if (!is_valid_email($email)) $errors[] = 'A valid email is required.';
if (!is_strong_password($password)) $errors[] = 'Password must be at least 8 characters with letters and numbers.';
if (mb_strlen($fullName) < 2) $errors[] = 'Full name is required.';
if ($phone !== '' && !is_valid_phone($phone)) $errors[] = 'Invalid phone number.';
if (mb_strlen($address) < 5) $errors[] = 'Address is required.';
if ($latitude !== null && ($latitude < -90 || $latitude > 90)) $errors[] = 'Invalid latitude.';
if ($longitude !== null && ($longitude < -180 || $longitude > 180)) $errors[] = 'Invalid longitude.';

if ($errors) {
    json_error(implode(' ', $errors), 422);
}

// Unique email check
if (dbfetch('SELECT id FROM users WHERE email = ?', [mb_strtolower($email)])) {
    json_error('That email is already registered.', 409);
}

// Geocode if lat/lng not supplied (best-effort, never fatal)
if ($latitude === null || $longitude === null) {
    $geo = \Glorikar\Services\MapboxService::geocode($address);
    if ($geo) {
        $latitude = $geo['lat'];
        $longitude = $geo['lng'];
    }
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$id = uuid();

dbq(
    'INSERT INTO users (id, email, password_hash, full_name, phone, address, latitude, longitude, role)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
    [ $id, mb_strtolower($email), $hash, $fullName, $phone !== '' ? normalize_phone($phone) : null, $address, $latitude, $longitude, 'client' ]
);

// Log in the new user immediately.
session_regenerate_id(true);
$_SESSION['user_id'] = $id;
$_SESSION['role'] = 'client';
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

log_auth_event('register', $id);

json_response([
    'ok' => true,
    'csrf_token' => $_SESSION['csrf_token'],
    'user' => [
        'id' => $id,
        'email' => mb_strtolower($email),
        'full_name' => $fullName,
        'role' => 'client',
    ],
], 201);