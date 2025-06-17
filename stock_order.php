<?php
require_once 'db.php';       // DBConn
require_once 'function.php'; // DBFunc

$db = new DBConn();
$stock = new DBFunc($db->conn);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku = trim($_POST['sku']);
    $category = trim($_POST['category']);
    $zone = trim($_POST['zone']);
    $rack = trim($_POST['rack']);
    $quantity = (int)$_POST['quantity'];

    $imagePath = '';
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {
        $uploadsDir = 'uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $filename = basename($_FILES['image']['name']);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $sanitizedFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($filename, PATHINFO_FILENAME));
        $targetFile = $uploadsDir . time() . '_' . $sanitizedFilename . '.' . $extension;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    $success = $stock->insertStock($sku, $category, $zone, $rack, $quantity, $imagePath);
    $message = $success ? "Stock order placed successfully." : "Failed to place stock order.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Stock Order</title>
    <link rel="stylesheet" href="master.css">
</head>
<body>
<div class="container">
    <h2>Order New Stock</h2>
    <?php if ($message): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>SKU:</label>
        <input type="text" name="sku" required>

        <label>Category:</label>
        <select name="category" required>
            <option value="">-- Select Category --</option>
            <option value="Electronics">Electronics</option>
            <option value="Apparel">Apparel</option>
            <option value="Tools">Tools</option>
        </select>

        <label>Zone:</label>
        <select name="zone" required>
            <option value="">-- Select Zone --</option>
            <option value="A1">A1</option>
            <option value="B2">B2</option>
            <option value="C3">C3</option>
        </select>

        <label>Rack:</label>
        <input type="text" name="rack" required>

        <label>Quantity:</label>
        <input type="number" name="quantity" min="1" required>

        <label>Image:</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit">Place Order</button>
    </form>

    <p><a href="stock_manage.php">← Back to Stock Management</a></p>
</div>
</body>
</html>
