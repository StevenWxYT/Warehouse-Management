<?php
session_start();
include_once 'php/function.php';

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'salesman'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];

$db = new DBConn();
$func = new DBFunc($db->conn);
$topSelling = $func->getTop10BestSelling();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
        }
        body {
            font-family: Arial, sans-serif;
            /* background: linear-gradient(120deg, #a1c4fd, #c2e9fb, #d4fc79, #96e6a1);
            background-size: 400% 400%;
            animation: gradientFlow 18s ease infinite; */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .dashboard {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            width: 80%;
            max-width: 900px;
            text-align: center;
        }

        table {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
            background: #f9f9f9;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }

        .btn-group button {
            margin: 5px;
            padding: 12px 20px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-group button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<video autoplay muted loop id="bg-video">
    <source src="bg/My most beautiful drone shot – Cinematic FPV on an empty beach.mp4" type="video/mp4">
    Your browser does not support HTML5 video.
</video>
<div class="dashboard">
    <h2>Welcome, <?= htmlspecialchars($username) ?> (<?= htmlspecialchars($role) ?>)</h2>
    <div class="btn-group">
        <form method="post" action="view_orders_history.php" style="display:inline;">
            <?php $_SESSION['from_dashboard'] = true; ?>
            <button type="submit">View Order History</button>
        </form>
        <button onclick="window.location.href='stock_quantity.php'">View Stock Quantity</button>
        <?php if ($role === 'admin'): ?>
            <button onclick="window.location.href='stock_orders.php'">Order Stocks</button>
            <button onclick="window.location.href='stock_manage.php'">Manage Stocks</button>
        <?php endif; ?>
        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <h3>Top 10 Best Selling Products</h3>
    <?php if (!empty($topSelling)): ?>
        <table>
            <tr>
                <th>Image</th><th>SKU</th><th>Rack</th><th>Zone</th><th>Quantity</th>
            </tr>
            <?php foreach ($topSelling as $item): ?>
                <tr>
                    <td><img src="<?= htmlspecialchars($item['image']) ?>" width="50" /></td>
                    <td><?= htmlspecialchars($item['sku']) ?></td>
                    <td><?= htmlspecialchars($item['rack']) ?></td>
                    <td><?= htmlspecialchars($item['zone']) ?></td>
                    <td><?= (int)$item['quantity'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No sales data available.</p>
    <?php endif; ?>
</div>
</body>
</html>
