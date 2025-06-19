<?php
require_once 'db.php';       // DBConn
require_once 'function.php'; // DBFunc

$DBConn = new DBConn();
$db = new DBFunc($DBConn->conn);

$keyword = $_POST['keyword'] ?? '';
$zone = $_POST['zone'] ?? '';

$stocks = ($keyword || $zone) ? $db->searchStock($keyword, $zone) : $db->getAllStock();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Stock Quantity Viewer</title>
    <link rel="stylesheet" href="master.css"> <!-- optional global styling -->
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { margin-bottom: 10px; }
        form { animation: fadeIn 0.5s; }
        input, select, button {
            padding: 8px;
            margin: 4px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input:focus, select:focus {
            border-color: #777;
            outline: none;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(241,196,15, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(241,196,15, 0); }
            100% { box-shadow: 0 0 0 0 rgba(241,196,15, 0); }
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        button {
            background-color: #f39c12;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #d68910;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        tr:hover {
            background-color: #f9f9f9;
        }

        .low-stock td {
            background-color: #ffebcc;
        }

        .badge {
            background-color: #e67e22;
            color: white;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 6px;
        }

        .container {
            max-width: 800px;
            margin: auto;
        }

        a {
            display: inline-block;
            margin-top: 15px;
            color: #2980b9;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>View Stock by Quantity</h2>

    <form method="post">
        <input type="text" name="keyword" placeholder="Search by SKU or name" value="<?= htmlspecialchars($keyword) ?>">
        <select name="zone">
            <option value="">All Zones</option>
            <option <?= $zone == 'A1' ? 'selected' : '' ?>>A1</option>
            <option <?= $zone == 'B2' ? 'selected' : '' ?>>B2</option>
            <option <?= $zone == 'C3' ? 'selected' : '' ?>>C3</option>
        </select>
        <button type="submit">Filter</button>
    </form>

    <table>
        <tr>
            <th>SKU</th>
            <th>Category</th>
            <th>Zone</th>
            <th>Rack</th>
            <th>Quantity</th>
        </tr>
        <?php foreach ($stocks as $stock): ?>
        <tr class="<?= ($stock['quantity'] < 10) ? 'low-stock' : '' ?>">
            <td><?= htmlspecialchars($stock['sku']) ?></td>
            <td><?= htmlspecialchars($stock['category']) ?></td>
            <td><?= htmlspecialchars($stock['zone']) ?></td>
            <td><?= htmlspecialchars($stock['rack']) ?></td>
            <td>
                <?= $stock['quantity'] ?>
                <?php if ($stock['quantity'] < 10): ?>
                    <span class="badge">Low Stock</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="stock_manage.php">← Back to Stock Management</a></p>
</div>
</body>
</html>
