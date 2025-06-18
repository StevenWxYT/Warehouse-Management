<?php
include 'php/function.php';

$db = new DBConn();
$warehouse = new DBFunc($db->conn);

// Create uploads folder if not exist
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add'])) {
        $sku = $_POST['sku'] ?? '';
        $rack = $_POST['rack'] ?? '';
        $zone = $_POST['zone'] ?? '';
        $quantity = $_POST['quantity'] ?? '';

        // Handle image upload
        $imagePath = '';
        if (!empty($_FILES['image']['name'])) {
            $imageName = basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $imageName;
            move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
            $imagePath = $targetFile;
        }

        $warehouse->insertWarehouse(null, $imagePath, $sku, $rack, $zone, $quantity);
    } elseif (isset($_POST['update'])) {
        $id = $_POST['update'];
        $sku = $_POST['sku'] ?? '';
        $rack = $_POST['rack'] ?? '';
        $zone = $_POST['zone'] ?? '';
        $quantity = $_POST['quantity'] ?? '';

        $imagePath = $_POST['current_image'];

        if (!empty($_FILES['image']['name'])) {
            $imageName = basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $imageName;
            move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
            $imagePath = $targetFile;
        }

        $warehouse->updateWarehouse($id, $imagePath, $sku, $rack, $zone, $quantity);
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['delete'];
        $warehouse->deleteWarehouse($id);
    }
}

$data = $warehouse->getAllWarehouse();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Stocks</title>
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
            /* background: linear-gradient(270deg, red, orange, yellow, green, blue, indigo, violet);
            background-size: 1400% 1400%;
            animation: rainbowBG 20s ease infinite; */
            background: url('bg/images (2).jpg') no-repeat center center fixed;
            background-size: cover;
        }

        /* @keyframes rainbowBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        } */

        form, table {
            margin: 20px auto;
            background: white;
            padding: 20px;
            width: 90%;
        }

        input[type="text"], input[type="number"], input[type="file"] {
            width: 90%;
            padding: 8px;
            margin: 5px;
        }

        input[type="submit"] {
            margin: 5px;
            padding: 10px 20px;
            background: #333;
            color: white;
            border: none;
        }

        table {
            border-collapse: collapse;
            width: 90%;
        }

        th, td {
            border: 1px solid #999;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #444;
            color: white;
        }

        img.thumbnail {
            width: 200px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <h1>Manage Stocks</h1>

    <!-- Add new item form -->
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="image" required><br>
        <input type="text" name="sku" placeholder="SKU" required><br>
        <input type="text" name="rack" placeholder="Rack" required><br>
        <input type="text" name="zone" placeholder="Zone" required><br>
        <input type="number" name="quantity" placeholder="Quantity" required><br>
        <input type="submit" name="add" value="Add Stock">
    </form>

    <table>
        <tr>
            <th>Image</th>
            <th>SKU</th>
            <th>Rack</th>
            <th>Zone</th>
            <th>Quantity</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($data as $item): ?>
        <tr>
            <form method="post" enctype="multipart/form-data">
                <td>
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?= $item['image'] ?>" class="thumbnail">
                    <?php endif; ?>
                    <input type="file" name="image"><br>
                    <input type="hidden" name="current_image" value="<?= $item['image'] ?>">
                </td>
                <td><input type="text" name="sku" value="<?= $item['sku'] ?>"></td>
                <td><input type="text" name="rack" value="<?= $item['rack'] ?>"></td>
                <td><input type="text" name="zone" value="<?= $item['zone'] ?>"></td>
                <td><input type="number" name="quantity" value="<?= $item['quantity'] ?>"></td>
                <td>
                    <button type="submit" name="update" value="<?= $item['id'] ?>">Update</button>
                    <button type="submit" name="delete" value="<?= $item['id'] ?>" onclick="return confirm('Are you sure?')">Delete</button>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
