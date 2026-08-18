<?php
// ═══════════════════════════════════════════════════════════
//  GLORIKAR — api/_bootstrap.php
//  Shared setup for every API endpoint.
//  Sets JSON headers, CORS, error handling.
// ═══════════════════════════════════════════════════════════

declare(strict_types=1);

// ── Error display off (never leak stack traces) ───────────
ini_set('display_errors', '0');
error_reporting(E_ALL);

// ── JSON response header ──────────────────────────────────
header('Content-Type: application/json; charset=utf-8');

// ── CORS ─────────────────────────────────────────────────
// In production: replace * with your actual frontend origin(s)
$allowed_origins = [
    'http://localhost',
    'http://127.0.0.1',
    'https://glorikar.com',         // update to your real domain
    'https://www.glorikar.com',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: ' . ($allowed_origins[0]));
}
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

// Pre-flight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Helper: read JSON body ────────────────────────────────
function json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// ── Helper: success response ──────────────────────────────
function respond(mixed $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ── Helper: error response ────────────────────────────────
function respond_error(string $message, int $status = 400, string $code = ''): never
{
    http_response_code($status);
    echo json_encode(array_filter([
        'error' => $message,
        'code'  => $code ?: null,
    ]), JSON_UNESCAPED_UNICODE);
    exit;
}

// ── CSRF check (for state-changing requests) ─────────────
function verify_csrf(): void
{
    if (in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'OPTIONS'], true)) return;
    $token   = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $session = $_SESSION['csrf_token'] ?? '';
    if ($token === '' || $session === '' || !hash_equals($session, $token)) {
        respond_error('CSRF token mismatch', 403, 'CSRF_INVALID');
    }
}

// ── DB bootstrap ─────────────────────────────────────────
require_once __DIR__ . '/../backend/includes/db.php';