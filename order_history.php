<?php
require_once 'db.php';
require_once 'function.php';

$db = new DBConn();
$stock = new DBFunc($db->conn);

// Admin-only access check
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied. Admins only.";
    exit;
}

// Fetch logs
$logs = $stock->getOrderHistory();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History (Admin)</title>
    <link rel="stylesheet" href="master.css">
    <style>
        .qrcode {
            width: 60px;
            height: 60px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }
        th {
            background: #f4f4f4;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📦 Stock Activity Log</h2>

    <?php if ($logs && count($logs)): ?>
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>SKU</th>
                    <th>QR Code</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['timestamp']) ?></td>
                        <td><?= htmlspecialchars($log['user']) ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['sku']) ?></td>
                        <td>
                            <?php
                            $qrPath = "qrcodes/" . $log['sku'] . ".png";
                            if (file_exists($qrPath)): ?>
                                <img src="<?= $qrPath ?>" class="qrcode" alt="QR">
                            <?php else: ?>
                                <span style="color: gray;">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No log data available.</p>
    <?php endif; ?>

    <p><a href="stock_manage.php">&larr; Back to Stock Management</a></p>
</div>
</body>
</html>
