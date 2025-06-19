<?php
require_once 'php/function.php';

$db = new DBConn();
$user = new DBFunc($db->conn);

$sku = $_GET['sku'] ?? '';

$item = $user->getItemBySKU($sku); // You'll create this method below

?>

<!DOCTYPE html>
<html>
<head>
    <title>Scanned Item Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            margin: 0;
            padding: 30px;
            text-align: center;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            padding: 20px;
            max-width: 400px;
            margin: auto;
        }
        img {
            width: 200px;
            height: auto;
            border-radius: 10px;
        }
        .info {
            margin-top: 15px;
            text-align: left;
        }
        .info strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if ($item): ?>
        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Item Image"><br><br>
        <div class="info">
            <strong>Item Name:</strong> <?php echo htmlspecialchars($item['item_name']); ?><br>
            <strong>Quantity:</strong> <?php echo htmlspecialchars($item['quantity']); ?>
        </div>
    <?php else: ?>
        <p>Item not found!</p>
    <?php endif; ?>
</div>

</body>
</html>
