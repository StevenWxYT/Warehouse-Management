<?php
session_start();
require_once 'php/function.php';

$db = new DBConn();
$user = new DBFunc($db->conn);

// Hardcoded item images by SKU
$imageMap = [
    'LP' => 'bg/lp.jpg',
    'MIKI MOUSE' => 'bg/images (3).jpg',
    'girl' => 'bg/download.jpg',
];

// Save scanned SKU to session
$scannedSku = $_GET['sku'] ?? null;
if ($scannedSku) {
    $_SESSION['scanned_skus'][] = strtoupper($scannedSku);
}

// Retrieve all scanned SKUs
$scannedSkus = $_SESSION['scanned_skus'] ?? [];

// Get all stock items
$allItems = $user->getAllStockItems();
$allItemsAssoc = [];
foreach ($allItems as $item) {
    $allItemsAssoc[strtoupper($item['sku'])] = $item;
}

// Prepare scanned item cards
$scannedItems = [];
foreach ($scannedSkus as $sku) {
    if (isset($allItemsAssoc[$sku])) {
        $scannedItems[] = $allItemsAssoc[$sku];
    }
}

// QR code generator
function generateQRCode($sku) {
    $tempDir = 'qrcodes/';
    if (!file_exists($tempDir)) mkdir($tempDir);
    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/Warehouse-Management/stock_out.php?sku=' . urlencode($sku);
    $filePath = $tempDir . $sku . '.png';
    if (!file_exists($filePath)) {
        require_once 'phpqrcode/qrlib.php';
        QRcode::png($url, $filePath, QR_ECLEVEL_L, 4);
    }
    return $filePath;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Out</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        color: black;
        text-align: center;
        margin: 0;
        padding: 20px;
        background: url('bg/hands-typing-on-laptop-programming-600nw-2480023489.webp') no-repeat center center fixed;
        background-size: cover;
    }

    h1 {
        color: white;
        margin-bottom: 20px;
    }

    form {
        margin-bottom: 30px;
    }

    input[type="text"] {
        padding: 10px;
        width: 250px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    input[type="submit"] {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        background: #333;
        color: white;
        cursor: pointer;
    }

    .item {
        background: rgba(255, 255, 255, 1);
        border-radius: 12px;
        padding: 20px;
        margin: 20px auto;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 1);
        max-width: 700px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        opacity: 1;
        animation: fadeOut 30s forwards;
        color: black;
    }

    @keyframes fadeOut {
        0%   { opacity: 1; }
        90%  { opacity: 1; }
        100% { opacity: 0; display: none; }
    }

    .qr img {
        width: 100px;
        height: 100px;
    }

    .image img {
        max-width: 120px;
        height: auto;
        border-radius: 8px;
    }

    .item-details {
        text-align: left;
        flex-grow: 1;
        padding: 0 20px;
        color: black;
    }

    .label {
        font-weight: bold;
        color: black;
    }

    table {
        width: 90%;
        margin: 30px auto;
        border-collapse: collapse;
        background: rgba(255, 255, 255, 1);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 1);
        color: black;
    }

    table th, table td {
        padding: 12px;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    table th {
        background: rgba(46, 125, 50, 1);
        color: white;
    }

    table tr:nth-child(even) {
        background: rgba(240, 240, 240, 1);
    }
</style>



</head>
<body>

<h1>Stock Out Items</h1>
<!-- SKU Search Form -->
<form method="GET">
    <input type="text" name="sku" placeholder="Scan SKU" autofocus>
    <input type="submit" value="Scan">
</form>

<!-- All Scanned Item Cards -->
<?php foreach ($scannedItems as $item): 
    $sku = strtoupper($item['sku']);
    $quantity = $item['quantity'];
    $name = $item['item_name'] ?? 'Unknown';
    $order_time = $item['order_time'] ?? date('Y-m-d H:i:s');
    $qrPath = generateQRCode($sku);
    $imagePath = $imageMap[$sku] ?? 'images/default.png';
?>
<div class="item">
    <div class="qr">
        <img src="<?php echo $qrPath; ?>" alt="QR Code">
    </div>
    <div class="item-details">
        <div><span class="label">Name:</span> <?php echo htmlspecialchars($name); ?></div>
        <div><span class="label">SKU:</span> <?php echo htmlspecialchars($sku); ?></div>
        <div><span class="label">Quantity:</span> <?php echo htmlspecialchars($quantity); ?></div>
        <div><span class="label">Order Time:</span> <?php echo htmlspecialchars($order_time); ?></div>
    </div>
    <div class="image">
        <img src="<?php echo $imagePath; ?>" alt="Item Image">
    </div>
</div>
<?php endforeach; ?>

<!-- Summary Table -->
<table>
    <tr>
        <th>Name</th>
        <th>SKU</th>
        <th>Quantity</th>
        <th>Order Time</th>
    </tr>
    <?php foreach ($allItems as $item): 
        $sku = $item['sku'];
        $quantity = $item['quantity'];
        $name = $item['item_name'] ?? 'Unknown';
        $order_time = $item['order_time'] ?? date('Y-m-d H:i:s');
    ?>
    <tr>
        <td><?php echo htmlspecialchars($name); ?></td>
        <td><?php echo htmlspecialchars($sku); ?></td>
        <td><?php echo htmlspecialchars($quantity); ?></td>
        <td><?php echo htmlspecialchars($order_time); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
