<?php
/**
 * helpers.php — shared utilities: JSON responses, input parsing,
 * escaping, CSRF, rate limiting, UUIDs, session helpers.
 */

require_once __DIR__ . '/db.php';

/** Send a JSON response and stop. */
function json_response($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Send a JSON error and stop. Never include stack traces / SQL. */
function json_error(string $message, int $code = 400): void
{
    json_response(['error' => $message], $code);
}

/** Parse JSON request body (or fall back to form data). */
function read_json_input(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (is_array($data)) {
        return $data;
    }
    return $_POST; // form-encoded fallback
}

/** htmlspecialchars for safe output rendering (XSS protection). */
if (!function_exists('e')) {
    function e($str): string
    {
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }
}

/** Generate a UUID v4. */
function uuid(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/* ------------------------- CSRF ------------------------------ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Verify CSRF token on state-changing requests. Call at top of POST/PATCH/DELETE. */
function csrf_verify(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        return;
    }
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        json_error('CSRF token mismatch.', 403);
    }
}

/* --------------------- Rate limiting ------------------------- */
/* Simple sliding-window limiter keyed by IP. 5 attempts / 15 min default. */

function rate_limit(string $key, int $max = 0, int $windowSeconds = 0): void
{
    // Allow env-driven overrides: RATE_LIMIT_MAX / RATE_LIMIT_WINDOW.
    if ($max === 0)   $max   = (int)Env::get('RATE_LIMIT_MAX', 5);
    if ($windowSeconds === 0) $windowSeconds = (int)Env::get('RATE_LIMIT_WINDOW', 900);

    $pdo = db();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $bucket = hash('sha256', $key . '|' . $ip);
    $now = time();

    $stmt = $pdo->prepare(
        'INSERT INTO auth_audit (user_id, event, ip_address, created_at)
         VALUES (NULL, :bucket, :ip, FROM_UNIXTIME(:now))
         ON DUPLICATE KEY UPDATE created_at = created_at'
    );
    // auth_audit is just for this limiter bookkeeping via event column.
    $stmt->execute([':bucket' => 'rl:' . $bucket, ':ip' => $ip, ':now' => $now]);

    // Count events of this bucket within the window.
    $count = dbfetch(
        "SELECT COUNT(*) AS c FROM auth_audit
         WHERE event = :bucket AND created_at >= FROM_UNIXTIME(:from)",
        [':bucket' => 'rl:' . $bucket, ':from' => $now - $windowSeconds]
    );

    // Prune old rows occasionally.
    if ($now % 100 === 0) {
        dbq("DELETE FROM auth_audit WHERE created_at < FROM_UNIXTIME(:cut)", [':cut' => $now - 86400]);
    }

    if ((int)($count['c'] ?? 0) > $max) {
        json_error('Too many attempts. Please try again later.', 429);
    }
}

/* --------------------- Session helpers ----------------------- */

function session_lifetime(): int
{
    return (int)Env::get('SESSION_LIFETIME', 1800);
}

function start_session_secure(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = filter_var(Env::get('COOKIE_SECURE', false), FILTER_VALIDATE_BOOLEAN);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Idle timeout — 30 min by default.
    $lifetime = session_lifetime();
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $lifetime) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}

/** Returns the current user row (array) or null. */
function current_user(): ?array
{
    start_session_secure();
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return dbfetch('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
}

/** Log an auth/audit event (no passwords or tokens logged). */
function log_auth_event(string $event, ?string $userId = null): void
{
    try {
        dbq(
            'INSERT INTO auth_audit (user_id, event, ip_address) VALUES (?, ?, ?)',
            [$userId, $event, $_SERVER['REMOTE_ADDR'] ?? 'unknown']
        );
    } catch (Throwable $e) {
        error_log('audit log failed: ' . $e->getMessage());
    }
}

/* --------------------- Validation ---------------------------- */

function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function is_valid_phone(string $phone): bool
{
    return preg_match('/^\+?[0-9\s\-()]{7,20}$/', $phone) === 1;
}

function normalize_phone(string $phone): string
{
    return preg_replace('/[^\d+]/', '', $phone) ?? $phone;
}

/** Strong-ish password: min 8 chars, letter + number. */
function is_strong_password(string $pw): bool
{
    return strlen($pw) >= 8 && preg_match('/[A-Za-z]/', $pw) && preg_match('/[0-9]/', $pw);
}