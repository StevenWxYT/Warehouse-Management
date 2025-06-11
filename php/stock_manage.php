<?php
include("function.php");

$db = new DBConn();
$user = new DBFunc($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $image = $_POST['image'];
    $sku = $_POST['sku'];
    $rack = $_POST['rack'];
    $zone = $_POST['zone'];
    $name = $_POST['name'];
    $dimensions = $_POST['dimensions'];
    $color = $_POST['colour'];
    $weight = $_POST['weight'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];

    $stock->insertWarehouse($id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $price);
    $stock->viewWarehouse($id);
    $stock->updateWarehouse($id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $price);
    $stock->deleteWarehouse($id);
}

$stmt = $this->conn->prepare("SELECT * FROM warehouse WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();

$result = $stmt->fetch_result();
$data = $result->fetch_assoc();

$stmt->close();
?>