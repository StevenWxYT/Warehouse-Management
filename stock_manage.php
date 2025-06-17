<?php
require_once 'db.php'; // DBConn
require_once 'function.php'; // DBFunc

$db = new DBConn();
$stock = new DBFunc($db->conn);

$categoryFilter = $_POST['category'] ?? '';
$zoneFilter = $_POST['zone'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    if (isset($_POST['delete'])) {
        $stock->deleteStock($id);
        header("Location: stock_manage.php");
        exit;
    }

    if (isset($_POST['update'])) {
        $sku = $_POST['sku'];
        $category = $_POST['category'];
        $zone = $_POST['zone'];
        $rack = $_POST['rack'];
        $quantity = $_POST['quantity'];
        $stock->updateStock($id, $sku, $category, $zone, $rack, $quantity);
        header("Location: stock_manage.php");
        exit;
    }
}

$stocks = $stock->filterStock($categoryFilter, $zoneFilter);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Stock Management</title>
    <link rel="stylesheet" href="master.css">
</head>
<body>
<div class="container">
    <h2>Manage Stock Items</h2>
    <form method="post" class="filter-form">
        <label>Category:</label>
        <select name="category">
            <option value="">All</option>
            <option <?= $categoryFilter == 'Electronics' ? 'selected' : '' ?>>Electronics</option>
            <option <?= $categoryFilter == 'Apparel' ? 'selected' : '' ?>>Apparel</option>
            <option <?= $categoryFilter == 'Tools' ? 'selected' : '' ?>>Tools</option>
        </select>
        <label>Zone:</label>
        <select name="zone">
            <option value="">All</option>
            <option <?= $zoneFilter == 'A1' ? 'selected' : '' ?>>A1</option>
            <option <?= $zoneFilter == 'B2' ? 'selected' : '' ?>>B2</option>
            <option <?= $zoneFilter == 'C3' ? 'selected' : '' ?>>C3</option>
        </select>
        <button type="submit">Filter</button>
        <a href="stock_manage.php" class="reset">Reset</a>
    </form>
    <a href="stock_order.php">+ Add New Stock</a>
    <table>
        <tr>
            <th>SKU</th>
            <th>Category</th>
            <th>Zone</th>
            <th>Rack</th>
            <th>Quantity</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($stocks as $s): ?>
        <tr class="<?= ($s['quantity'] < 10) ? 'low-stock' : '' ?>">
            <form method="post">
                <td><input type="text" name="sku" value="<?= htmlspecialchars($s['sku']) ?>"></td>
                <td>
                    <select name="category">
                        <option <?= $s['category'] == 'Electronics' ? 'selected' : '' ?>>Electronics</option>
                        <option <?= $s['category'] == 'Apparel' ? 'selected' : '' ?>>Apparel</option>
                        <option <?= $s['category'] == 'Tools' ? 'selected' : '' ?>>Tools</option>
                    </select>
                </td>
                <td>
                    <select name="zone">
                        <option <?= $s['zone'] == 'A1' ? 'selected' : '' ?>>A1</option>
                        <option <?= $s['zone'] == 'B2' ? 'selected' : '' ?>>B2</option>
                        <option <?= $s['zone'] == 'C3' ? 'selected' : '' ?>>C3</option>
                    </select>
                </td>
                <td><input type="text" name="rack" value="<?= htmlspecialchars($s['rack']) ?>"></td>
                <td>
                    <input type="number" name="quantity" value="<?= (int)$s['quantity'] ?>">
                    <?php if ($s['quantity'] < 10): ?>
                        <span class="badge">Low Stock</span>
                    <?php endif; ?>
                </td>
                <td>
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <button type="submit" name="update">Update</button>
                    <button type="submit" name="delete" onclick="return confirm('Confirm delete?');">Delete</button>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
