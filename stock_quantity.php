<?php
require_once 'db.php';       // DBConn
require_once 'function.php'; // DBFunc

$DBConn = new DBConn();
$db = new DBFunc($DBConn->conn);

$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
$zone = isset($_POST['zone']) ? $_POST['zone'] : '';
// Fetch filtered stocks; if no filters, fetch all
if($keyword || $zone) {
    $stocks = $db->searchStock($keyword, $zone);
} else {
    $stocks = $db->getAllStock();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Stock Viewer</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { margin-bottom: 10px; }
        form { animation: fadeIn 0.5s; }
        input, select, button { padding: 8px; margin: 4px 0; border: 1px solid #ccc; border-radius: 4px; }
        input:focus, select:focus { border-color: #777; outline: none; animation: pulse 1s infinite; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(241,196,15, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(241,196,15, 0); }
            100% { box-shadow: 0 0 0 0 rgba(241,196,15, 0); }
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        button { background-color: #f39c12; color: white; cursor: pointer; }
        button:hover { background-color: #d68910; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        tr:hover { background-color: #f9f9f9; }
        .low-stock td { background-color: #ffebcc; }
        .badge { background-color: #e67e22; color: white; padding: 3px 6px; border-radius: 4px; font-size: 12px; }
        .container { max-width: 800px; margin: auto; }
    </style>
</head>
<body>
<div class="container">
    <h2>View Stock by Quantity</h2>
    <form method="post">
        <input type="text" name="keyword" placeholder="Search by SKU or name" value="<?php echo htmlspecialchars($keyword); ?>">
        <select name="zone">
            <option value="">All Zones</option>
            <option <?php if($zone=='A1') echo 'selected'; ?>>A1</option>
            <option <?php if($zone=='B2') echo 'selected'; ?>>B2</option>
            <option <?php if($zone=='C3') echo 'selected'; ?>>C3</option>
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
        <?php foreach($stocks as $stock): ?>
        <tr class="<?php echo ($stock['quantity'] < 10) ? 'low-stock' : ''; ?>">
            <td><?php echo htmlspecialchars($stock['sku']); ?></td>
            <td><?php echo htmlspecialchars($stock['category']); ?></td>
            <td><?php echo htmlspecialchars($stock['zone']); ?></td>
            <td><?php echo htmlspecialchars($stock['rack']); ?></td>
            <td>
                <?php echo $stock['quantity']; ?>
                <?php if($stock['quantity'] < 10): ?>
                    <span class="badge">Low Stock</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="stock_manage.php">Back to Stock Management</a></p>
</div>
</body>
</html>
