<?php
declare(strict_types=1);
// GET /api/bookings/all.php — admin only
// Query: ?status=&client_id=&date_from=&date_to=&page=1&per_page=20

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$validStatuses = ['pending','scheduled','en_route','in_progress','completed','cancelled'];

$page    = max(1, (int)($_GET['page']     ?? 1));
$perPage = min(100, max(1, (int)($_GET['per_page'] ?? 20)));
$offset  = ($page - 1) * $perPage;

$where  = [];
$params = [];

if (!empty($_GET['status'])) {
    if (!in_array($_GET['status'], $validStatuses, true)) respond_error('Invalid status.', 422);
    $where[]  = 'b.status = ?';
    $params[] = $_GET['status'];
}

if (!empty($_GET['client_id'])) {
    $where[]  = 'b.client_id = ?';
    $params[] = $_GET['client_id'];
}

if (!empty($_GET['date_from'])) {
    $where[]  = 'b.preferred_date_from >= ?';
    $params[] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $where[]  = 'b.preferred_date_to <= ?';
    $params[] = $_GET['date_to'];
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings b $whereClause");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

// Paginated rows
$rowStmt = $pdo->prepare("
    SELECT
        b.id, b.status, b.preferred_date_from, b.preferred_date_to,
        b.address, b.latitude, b.longitude, b.notes, b.trip_score, b.created_at,
        u.id AS client_id, u.full_name AS client_name, u.phone AS client_phone
    FROM bookings b
    JOIN users u ON u.id = b.client_id
    $whereClause
    ORDER BY b.created_at DESC
    LIMIT ? OFFSET ?
");
$rowStmt->execute([...$params, $perPage, $offset]);
$bookings = $rowStmt->fetchAll();

// Attach services
$svcStmt = $pdo->prepare('
    SELECT bs.quantity, s.id AS service_id, s.name, s.base_price
    FROM booking_services bs
    JOIN services s ON s.id = bs.service_id
    WHERE bs.booking_id = ?
');
foreach ($bookings as &$b) {
    $svcStmt->execute([$b['id']]);
    $b['services'] = $svcStmt->fetchAll();
}
unset($b);

respond([
    'bookings' => $bookings,
    'total'    => $total,
    'page'     => $page,
    'per_page' => $perPage,
    'pages'    => (int)ceil($total / $perPage),
]);