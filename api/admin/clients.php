<?php
declare(strict_types=1);
// GET /api/admin/clients.php — paginated client list with search

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$search  = trim($_GET['search'] ?? '');

$where  = "role = 'client'";
$params = [];
if ($search !== '') {
    $where .= ' AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)';
    $like   = "%$search%";
    $params = [$like, $like, $like];
}

$stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM users WHERE $where");
$stmt->execute($params);
$total = (int)$stmt->fetch()['c'];

$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("
    SELECT u.id, u.full_name, u.email, u.phone, u.address, u.created_at,
           (SELECT COUNT(*) FROM bookings b WHERE b.client_id = u.id) AS total_bookings
    FROM users u
    WHERE $where
    ORDER BY u.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$clients = $stmt->fetchAll();

respond([
    'clients'  => $clients,
    'total'    => $total,
    'page'     => $page,
    'per_page' => $perPage,
]);