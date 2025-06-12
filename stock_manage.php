<?php
include("function.php");

if(!empty($_SESSION['username'])){
    header('Location: login.php');
    exit();
}

$db = new DBConn();
$stock = new DBFunc($db->conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $image = $_POST['image'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $rack = $_POST['rack'] ?? '';
    $zone = $_POST['zone'] ?? '';
    $name = $_POST['name'] ?? '';
    $dimensions = $_POST['dimensions'] ?? '';
    $color = $_POST['colour'] ?? '';
    $weight = isset($_POST['weight']) ? (float)$_POST['weight'] : 0.0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $description = $_POST['description'] ?? '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;

    if ($action === 'insert') {
        $stock->insertWarehouse($id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $description, $price);
        echo "✅ Record inserted successfully.";
    } elseif ($action === 'update') {
        $stock->updateWarehouse($id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $description, $price);
        echo "✅ Record updated successfully.";
    } elseif ($action === 'delete') {
        $stock->deleteWarehouse($id);
        echo "🗑️ Record deleted successfully.";
    } elseif ($action === 'view') {
        $data = $stock->viewWarehouse($id);
        if ($data) {
            echo "<h3>📦 Warehouse Record:</h3><pre>" . print_r($data, true) . "</pre>";
        } else {
            echo "⚠️ Record not found.";
        }
    } else {
        echo "❌ Invalid action.";
    }
} else {
    echo "⚠️ No action provided or invalid request.";
}
?>