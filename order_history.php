<?php
require_once 'db.php';       // DBConn
require_once 'function.php'; // DBFunc

$db = new DBConn();
$stock = new DBFunc($db->conn);

// Role-based access check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied. Admins only.";
    exit;
}

// Fetch activity logs
$logs = $stock->getOrderHistory();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order History (Admin)</title>
    <link rel="stylesheet" href="master.css">
</head>
<body>
<div class="container">
    <h2>Stock Activity Log</h2>
    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>User</th>
                <th>Action</th>
                <th>Item SKU</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($logs && count($logs)): ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['timestamp']) ?></td>
                    <td><?= htmlspecialchars($log['user']) ?></td>
                    <td><?= htmlspecialchars($log['action']) ?></td>
                    <td><?= htmlspecialchars($log['sku']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No log data available.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <p><a href="stock_manage.php">&larr; Back to Stock Management</a></p>
</div>
</body>
</html>
