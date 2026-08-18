<?php
/**
 * install.php — one-time setup: create DB schema, seed data, admin account.
 * Usage: php backend/install.php
 * The admin password is generated randomly and printed to the console
 * (never hardcoded, so no known credential ends up in the repo).
 */

require_once __DIR__ . '/includes/env.php';

echo "Glorikar Engineering installer\n";
echo "=============================\n\n";

// --- 1. Create database if missing -------------------------------------
$host = Env::get('DB_HOST', '127.0.0.1');
$port = Env::get('DB_PORT', '3306');
$dbName = Env::get('DB_NAME', 'glorikar');
$user = Env::get('DB_USER', 'root');
$pass = Env::get('DB_PASS', '');

try {
    $rootPdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass);
    $rootPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "[1/3] Database '{$dbName}' ready.\n";
} catch (PDOException $e) {
    echo "ERROR creating database: {$e->getMessage()}\n";
    exit(1);
}

// --- 2. Connect to the created DB and apply schema ----------------------
$dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

$schema = file_get_contents(__DIR__ . '/db/schema.sql');
$schema = preg_replace('/^USE\s+`?glorikar`?;?\s*$/mi', '', $schema); // strip USE (we connect to db directly)
$pdo->exec($schema);
echo "[2/3] Schema applied (users, services, bookings, teams, schedules, invoices, photos, push, notifications).\n";

// --- 3. Seed services + teams, then set a fresh random admin password ----
require_once __DIR__ . '/db/seed.php';
seedDatabase($pdo);

// Random 14-char password (letters + digits, meets is_strong_password).
$alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
$adminPassword = '';
for ($i = 0; $i < 14; $i++) {
    $adminPassword .= $alphabet[random_int(0, strlen($alphabet) - 1)];
}
$hash = password_hash($adminPassword, PASSWORD_BCRYPT);
$pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?')
    ->execute([$hash, 'boss@glorikar.com']);
echo "[3/3] Seed data inserted; admin password set.\n\n";

echo "Done.\n";
echo "Login: boss@glorikar.com / {$adminPassword} (role: admin)\n";
echo "Save this password now - re-running the installer regenerates it.\n";