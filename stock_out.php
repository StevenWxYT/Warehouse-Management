<?php
include("function.php");

$db = new DBConn();
$stock = new DBFunc($db->conn);

$message = '';
$error = '';
$showItem = null;

// Handle stock out
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sku = $_POST['sku'] ?? '';
    $deductQty = (int)($_POST['quantity'] ?? 0);

    if (!empty($sku) && $deductQty > 0) {
        $result = $stock->stockOut($sku, $deductQty);
        $message = $result['success'] ? $result['message'] : '';
        $error = !$result['success'] ? $result['message'] : '';
    } else {
        $error = "Invalid input.";
    }
}

// Load all stock
$allStock = $stock->getAllStock();

// Lookup scanned item
if (!empty($_GET['scan'])) {
    $scanSku = htmlspecialchars($_GET['scan']);
    foreach ($allStock as $item) {
        if ($item['sku'] === $scanSku) {
            $showItem = $item;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stock Out</title>
    <link rel="stylesheet" href="master.css">
    <style>
        .scan-input { margin-top: 10px; width: 100%; padding: 10px; font-size: 16px; }
        .item-card {
            margin: 20px 0;
            padding: 15px;
            border: 2px solid #28a745;
            border-radius: 12px;
            background: #f0fff5;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: fadeOut 25s ease forwards;
        }
        .item-card img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .item-info { font-size: 16px; }
        .table-wrap { margin-top: 30px; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #007bff;
            color: white;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Stock Out</h2>

    <!-- Scan input -->
    <form method="get">
        <input type="text" name="scan" class="scan-input" placeholder="Scan or enter SKU..." autofocus>
    </form>

    <!-- Item card shown after scan -->
    <?php if ($showItem): ?>
        <div class="item-card">
            <img src="<?= htmlspecialchars($showItem['image']) ?>" alt="Item">
            <div class="item-info">
                <strong>SKU:</strong> <?= htmlspecialchars($showItem['sku']) ?><br>
                <strong>Quantity:</strong> <?= $showItem['quantity'] ?><br>
                <?php if (file_exists("qrcodes/{$showItem['sku']}.png")): ?>
                    <img src="qrcodes/<?= $showItem['sku'] ?>.png" alt="QR" width="80">
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Feedback message -->
    <?php if (!empty($message)) echo "<p class='success'>$message</p>"; ?>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <!-- Stock Out Form -->
    <form method="POST" class="form-card">
        <label for="sku">Select SKU:</label>
        <select name="sku" id="sku" required>
            <option value="">-- Choose SKU --</option>
            <?php foreach ($allStock as $item): ?>
                <option value="<?= htmlspecialchars($item['sku']) ?>"
                    <?= ($showItem && $showItem['sku'] == $item['sku']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($item['sku']) ?> (Qty: <?= $item['quantity'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="quantity">Quantity to Deduct:</label>
        <input type="number" name="quantity" id="quantity" min="1" required>

        <button type="submit">Deduct Stock</button>
    </form>

    <!-- Stock Table -->
    <div class="table-wrap">
        <h3>Current Stock Overview</h3>
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Quantity</th>
                    <th>Order Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allStock as $item): ?>
                    <tr style="background: <?= $item['quantity'] < 10 ? '#ffe0e0' : '#fff' ?>">
                        <td><?= htmlspecialchars($item['sku']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= htmlspecialchars($item['order_time'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p><a href="dashboard.php">← Back to Dashboard</a></p>
</div>

<!-- Auto-remove scan from URL after 25s -->
<script>
    setTimeout(() => {
        const url = new URL(window.location);
        url.searchParams.delete('scan');
        window.history.replaceState(null, '', url);
    }, 25000);
</script>
</body>
</html>
