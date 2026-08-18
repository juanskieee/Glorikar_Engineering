<?php
// ═══════════════════════════════════════════════════════════
//  POST /api/auth/login.php
//  Body: { email, password }
//  Returns: { user: { id, email, full_name, role }, csrf_token }
// ═══════════════════════════════════════════════════════════

require_once __DIR__ . '/../_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_error('Method not allowed', 405);
}

// ── Rate limit (5 attempts / 15 min per IP) ───────────────
if (session_status() === PHP_SESSION_NONE) session_start();
$rl_key    = 'login_attempts';
$rl_window = 15 * 60;
$rl_max    = 5;
$now = time();
if (!isset($_SESSION[$rl_key])) $_SESSION[$rl_key] = [];
$_SESSION[$rl_key] = array_filter($_SESSION[$rl_key], fn($t) => ($now - $t) < $rl_window);
if (count($_SESSION[$rl_key]) >= $rl_max) {
    respond_error('Too many login attempts. Please wait 15 minutes.', 429, 'RATE_LIMITED');
}

// ── Input ─────────────────────────────────────────────────
$body     = json_body();
$email    = trim(strtolower($body['email']    ?? ''));
$password = $body['password'] ?? '';

if ($email === '' || $password === '') {
    respond_error('Invalid email or password.', 401, 'BAD_CREDENTIALS');
}

// ── Lookup user ───────────────────────────────────────────
$stmt = $pdo->prepare('SELECT id, email, password_hash, full_name, role FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

// Constant-time path: always run password_verify even on miss
// to prevent timing attacks that reveal whether an email exists.
$dummy_hash = '$2y$12$invalidhashpaddingtomatch256charsinputxx';
$hash       = $user ? $user['password_hash'] : $dummy_hash;

if (!$user || !password_verify($password, $hash)) {
    // Log the attempt (never the password) to the server log
    error_log("Failed login attempt for email=" . ($email !== '' ? $email : '(empty)') . " from IP=" . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $_SESSION[$rl_key][] = $now;
    // Generic message — never reveal if email exists
    respond_error('Invalid email or password.', 401, 'BAD_CREDENTIALS');
}

// ── Rehash if bcrypt cost changed ────────────────────────
if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
    $new_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
        ->execute([$new_hash, $user['id']]);
}

// ── Start authenticated session ───────────────────────────
session_regenerate_id(true);  // prevent session fixation
$_SESSION['user_id']     = $user['id'];
$_SESSION['email']       = $user['email'];
$_SESSION['role']        = $user['role'];
$_SESSION['full_name']   = $user['full_name'];
$_SESSION['last_activity'] = time();
$_SESSION['csrf_token']  = bin2hex(random_bytes(32));

// Clear rate-limit counter on success
unset($_SESSION[$rl_key]);

respond([
    'user' => [
        'id'        => $user['id'],
        'email'     => $user['email'],
        'full_name' => $user['full_name'],
        'role'      => $user['role'],
    ],
    'csrf_token' => $_SESSION['csrf_token'],
]);