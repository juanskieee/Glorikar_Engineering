<?php
// ═══════════════════════════════════════════════════════════
//  POST /api/auth/register.php
//  Body: { email, password, full_name, phone, address }
//  Returns: { user: { id, email, full_name, role } }
// ═══════════════════════════════════════════════════════════

require_once __DIR__ . '/../_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_error('Method not allowed', 405);
}

// ── Rate limit (simple IP-based, 5 req / 15 min) ─────────
// For production, use Redis or a rate_limit table.
// Basic in-session throttle as a baseline:
if (session_status() === PHP_SESSION_NONE) session_start();
$rl_key = 'reg_attempts';
$rl_window = 15 * 60;
$rl_max    = 5;
$now = time();
if (!isset($_SESSION[$rl_key])) $_SESSION[$rl_key] = [];
// Purge old timestamps
$_SESSION[$rl_key] = array_filter($_SESSION[$rl_key], fn($t) => ($now - $t) < $rl_window);
if (count($_SESSION[$rl_key]) >= $rl_max) {
    respond_error('Too many registration attempts. Please wait 15 minutes.', 429, 'RATE_LIMITED');
}
$_SESSION[$rl_key][] = $now;

// ── Input ─────────────────────────────────────────────────
$body = json_body();

$email     = trim(strtolower($body['email']     ?? ''));
$password  = $body['password']  ?? '';
$full_name = trim($body['full_name'] ?? '');
$phone     = trim($body['phone']     ?? '');
$address   = trim($body['address']   ?? '');

// ── Validate ──────────────────────────────────────────────
$errors = [];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address.';
}
if (strlen($email) > 255) {
    $errors[] = 'Email too long.';
}
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}
if (strlen($password) > 128) {
    $errors[] = 'Password too long.';
}
if ($full_name === '') {
    $errors[] = 'Full name is required.';
}
if (strlen($full_name) > 255) {
    $errors[] = 'Full name too long.';
}
if ($address === '') {
    $errors[] = 'Address is required.';
}
if (!empty($errors)) {
    respond_error(implode(' ', $errors), 422, 'VALIDATION_ERROR');
}

// ── Check duplicate email ─────────────────────────────────
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    // Generic message — don't confirm email existence
    respond_error('Invalid email or password.', 409, 'EMAIL_EXISTS');
}

// ── Hash password ─────────────────────────────────────────
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// ── Geocode address via Mapbox (optional — graceful fallback) ─
$lat = null;
$lng = null;
$mapbox_token = $_ENV['MAPBOX_ACCESS_TOKEN'] ?? '';
if ($mapbox_token && $address !== '') {
    $encoded  = rawurlencode($address . ', Philippines');
    $url      = "https://api.mapbox.com/geocoding/v5/mapbox.places/{$encoded}.json"
              . "?access_token={$mapbox_token}&limit=1&country=PH";
    $ctx = stream_context_create(['http' => ['timeout' => 3]]);
    $res = @file_get_contents($url, false, $ctx);
    if ($res) {
        $geo = json_decode($res, true);
        if (!empty($geo['features'][0]['center'])) {
            $lng = $geo['features'][0]['center'][0];
            $lat = $geo['features'][0]['center'][1];
        }
    }
}

// ── Insert user ───────────────────────────────────────────
$id = bin2hex(random_bytes(16)); // UUID-like unique id
$stmt = $pdo->prepare('
    INSERT INTO users (id, email, password_hash, full_name, phone, address, latitude, longitude, role)
    VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, \'client\')
');
$stmt->execute([$email, $hash, $full_name, $phone ?: null, $address, $lat, $lng]);

// Fetch the new user's id
$stmt = $pdo->prepare('SELECT id, email, full_name, role FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

// ── Auto-login after registration ─────────────────────────
session_start();
session_regenerate_id(true);
$_SESSION['user_id']   = $user['id'];
$_SESSION['email']     = $user['email'];
$_SESSION['role']      = $user['role'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['last_activity'] = time();
$_SESSION['csrf_token']  = bin2hex(random_bytes(32));

respond([
    'user'       => $user,
    'csrf_token' => $_SESSION['csrf_token'],
], 201);