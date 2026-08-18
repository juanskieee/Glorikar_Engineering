<?php
declare(strict_types=1);
// GET /api/admin/invoices.php — invoice list + summary stats
// Query: ?paid=0|1

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') respond_error('Method not allowed', 405);

$paid   = $_GET['paid'] ?? '';
$where  = '1=1';
$params = [];
if ($paid === '0' || $paid === '1') {
    $where   .= ' AND i.paid = ?';
    $params[] = (int)$paid;
}

$stmt = $pdo->prepare("
    SELECT i.id, i.booking_id, i.total_amount, i.issued_at, i.paid,
           u.full_name AS client_name
    FROM invoices i
    JOIN bookings b ON b.id = i.booking_id
    JOIN users u    ON u.id = b.client_id
    WHERE $where
    ORDER BY i.issued_at DESC
");
$stmt->execute($params);
$invoices = $stmt->fetchAll();

$svcStmt = $pdo->prepare('
    SELECT s.name FROM services s
    JOIN booking_services bs ON bs.service_id = s.id
    WHERE bs.booking_id = ?
');
foreach ($invoices as &$inv) {
    $svcStmt->execute([$inv['booking_id']]);
    $inv['services'] = array_column($svcStmt->fetchAll(), 'name');
}
unset($inv);

$stats = [
    'total_amount'  => round((float)$pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM invoices')->fetchColumn(), 2),
    'paid_amount'   => round((float)$pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE paid = TRUE')->fetchColumn(), 2),
    'unpaid_amount' => round((float)$pdo->query('SELECT COALESCE(SUM(total_amount), 0) FROM invoices WHERE paid = FALSE')->fetchColumn(), 2),
    'paid_count'    => (int)$pdo->query('SELECT COUNT(*) FROM invoices WHERE paid = TRUE')->fetchColumn(),
    'unpaid_count'  => (int)$pdo->query('SELECT COUNT(*) FROM invoices WHERE paid = FALSE')->fetchColumn(),
];

respond(['invoices' => $invoices, 'stats' => $stats]);