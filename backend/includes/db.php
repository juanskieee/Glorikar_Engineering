<?php
// ═══════════════════════════════════════════════════════════
//  GLORIKAR — backend/includes/db.php
//  Returns a shared PDO instance. Include this file in any
//  PHP endpoint that needs database access.
//  Usage:  require_once __DIR__ . '/../includes/db.php';
//          $row = $pdo->prepare('SELECT ...');
// ═══════════════════════════════════════════════════════════

declare(strict_types=1);

// ── Load dependencies + .env ─────────────────────────────
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    // Composer installed — phpdotenv populates $_ENV and $_SERVER.
    require_once $autoload;
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} else {
    // Fallback manual .env parser — used only until
    // `composer install` is run inside backend/.
    (function () {
        $envFile = __DIR__ . '/../../backend/.env';
        if (!file_exists($envFile)) {
            http_response_code(500);
            die(json_encode(['error' => '.env file not found']));
        }
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!isset($_ENV[$key])) {
                $_ENV[$key]    = $value;
                putenv("$key=$value");
            }
        }
    })();
}

// ── PDO singleton ─────────────────────────────────────────
function pdo(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host   = $_ENV['DB_HOST']   ?? 'localhost';
    $name   = $_ENV['DB_NAME']   ?? 'glorikar';
    $user   = $_ENV['DB_USER']   ?? '';
    $pass   = $_ENV['DB_PASS']   ?? '';
    $port   = $_ENV['DB_PORT']   ?? '3306';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // MySQL SSL (Aiven) — enabled only when DB_SSL_CA is set.
    // Uses the committed CA cert at backend/aiven-ca.pem.
    if (!empty($_ENV['DB_SSL_CA'])) {
        $options[PDO::MYSQL_ATTR_SSL_CA]                 = __DIR__ . '/../../backend/aiven-ca.pem';
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        // Never expose real DB error to client in production
        error_log('DB connection failed: ' . $e->getMessage());
        die(json_encode(['error' => 'Database connection failed']));
    }

    return $pdo;
}

// Alias for convenience
$pdo = pdo();