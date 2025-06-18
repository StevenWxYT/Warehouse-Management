<?php
session_start();

require_once 'php/db.php'; // Make sure this path is correct

if (!isset($_SESSION['username']) || !in_array($_SESSION['role'], ['admin', 'salesman'])) {
    header("Location: login.php");
    exit();
}

$db = new DBConn();
$conn = $db->conn;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_name'], $_POST['sku'], $_POST['quantity'])) {
    $item_name = trim($_POST['item_name']);
    $sku = trim($_POST['sku']);
    $quantity = (int)$_POST['quantity'];
    $datetime = !empty($_POST['datetime']) ? $_POST['datetime'] : date("Y-m-d H:i:s");
    $imagePath = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed)) {
            $uniqueName = uniqid() . '.' . $ext;
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $targetPath = $uploadDir . $uniqueName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = $targetPath;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO orders_history (item_name, sku, quantity, image, datetime) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $item_name, $sku, $quantity, $imagePath, $datetime);
    $stmt->execute();
}

$result = $conn->query("SELECT * FROM orders_history ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order History</title>
    <style>
        body {
            font-family: Arial;
            /* background: linear-gradient(270deg, red, orange, yellow, green, blue, indigo, violet);
            background-size: 200% 200%;
            animation: rainbowBG 12s ease infinite; */
            color: white;
            text-align: center;
            background: url('bg/istockphoto-524697362-612x612.jpg') no-repeat center center fixed;
            background-size: cover;
        }

        /* @keyframes rainbowBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        } */

        form, table {
            background-color: rgba(0,0,0,0.6);
            margin: 30px auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 1000px;
        }

        input, button, select {
            margin: 8px;
            padding: 10px;
            border-radius: 5px;
            border: none;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid white;
            padding: 12px;
        }

        img {
            width: 80px;
            height: auto;
            border-radius: 5px;
        }

        button {
            background: #28a745;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<h2>Order History</h2>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="item_name" placeholder="Item Name" required>
    <input type="text" name="sku" placeholder="SKU" required>
    <input type="number" name="quantity" placeholder="Quantity" required>
    <input type="datetime-local" name="datetime">
    <input type="file" name="image" accept="image/*">
    <button type="submit">Add Order</button>
</form>

<table>
    <tr>
        <th>Image</th>
        <th>Item Name</th>
        <th>SKU</th>
        <th>Quantity</th>
        <th>Date & Time</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td>
                <?php if ($row['image']): ?>
                    <img src="<?= htmlspecialchars($row['image']) ?>" alt="Image">
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['item_name']) ?></td>
            <td><?= htmlspecialchars($row['sku']) ?></td>
            <td><?= htmlspecialchars($row['quantity']) ?></td>
            <td><?= htmlspecialchars($row['datetime']) ?></td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
