<?php
/**
 * seed.php — programmatic seed (used by install.php).
 */

function seedDatabase(PDO $pdo): void
{
    // Services
    $services = [
        ['cleaning',    1.5, 1499.00],
        ['installing',  2.5, 2499.00],
        ['relocation',  3.0, 3499.00],
        ['repair',      2.0, 1999.00],
        ['inspection',  1.0,  999.00],
    ];
    $stmt = $pdo->prepare('INSERT INTO services (name, duration_hrs, base_price) VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE duration_hrs = VALUES(duration_hrs), base_price = VALUES(base_price)');
    foreach ($services as $s) {
        $stmt->execute($s);
    }

    // Boss admin (placeholder hash, replaced by install.php)
    $stmt = $pdo->prepare('INSERT IGNORE INTO users (id, email, password_hash, full_name, phone, address, latitude, longitude, role)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        '11111111-1111-1111-1111-111111111111',
        'boss@glorikar.com',
        '$2y$10$invalidhashplaceholder',
        'Glorikar Boss',
        '+63 900 000 0000',
        'Glorikar HQ, Quezon City, Philippines',
        14.6091, 121.0223, 'admin',
    ]);

    // Teams
    $teams = [
        ['aaaaaaa1-0000-0000-0000-000000000001', 'Team Alpha',   'L300 TBA-123'],
        ['aaaaaaa1-0000-0000-0000-000000000002', 'Team Bravo',   'Hiace TBB-456'],
        ['aaaaaaa1-0000-0000-0000-000000000003', 'Team Charlie', 'Navara TBC-789'],
    ];
    $stmt = $pdo->prepare('INSERT IGNORE INTO teams (id, name, vehicle, is_available) VALUES (?, ?, ?, TRUE)');
    foreach ($teams as $t) {
        $stmt->execute($t);
    }
}