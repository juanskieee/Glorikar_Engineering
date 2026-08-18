<?php
/**
 * db.php — PDO MySQL connection (single shared connection).
 * All queries MUST use prepared statements.
 */

require_once __DIR__ . '/env.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = Env::get('DB_HOST', '127.0.0.1');
        $port = Env::get('DB_PORT', '3306');
        $name = Env::get('DB_NAME', 'glorikar');
        $user = Env::get('DB_USER', 'root');
        $pass = Env::get('DB_PASS', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Never leak connection details to the client.
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database connection failed.']);
            exit;
        }
    }

    return $pdo;
}

/** Shortcut: execute a prepared statement with bound params. */
function dbq(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/** Fetch first row or null. */
function dbfetch(string $sql, array $params = []): ?array
{
    $row = dbq($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/** Fetch all rows. */
function dball(string $sql, array $params = []): array
{
    return dbq($sql, $params)->fetchAll();
}