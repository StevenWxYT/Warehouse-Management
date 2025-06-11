<?php
include("function.php");

if(!empty($_SESSION['username'])){
    header('Location: login.php');
    exit();
}

$db = new DBConn();
$stock = new DBFunc($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
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

    // Showing all WMS data inputs
    $stock = $this->conn->prepare("SELECT * FROM warehouse WHERE id = ?");
    $stock->bind_param('i', $id);
    $stock->execute();

    // Stock check-ins and viewing
    $stockIn = $this->conn->prepare("INSERT INTO warehouse (id, sku, rack, zone, name, dimensions, colour, weight, quantity, description, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stockIn->bind_param("issssssssisd", $id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $description, $price);
    $stockIn->execute();
    if ($stockIn->num_rows == 1) {
        $stockIn = $this->conn->prepare("SELECT * FROM warehouse WHERE id = ?");
        $stockIn->bind_param("i", $id);
        $stockIn->execute();
    } else {
        echo "No stocks have been added, please input new stock summary." . $this->conn->error();
    }

    // Stock check-outs and viewing
    $stockOut = $this->conn->prepare("INSERT INTO warehouse (id, sku, rack, zone, name, dimensions, colour, weight, quantity, description, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stockOut->bind_param("issssssssisd", $id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $description, $price);
    $stockOut->execute();
    if ($stockOut->num_rows == 1) {
        $stockOut = $this->conn->prepare("SELECT * FROM warehouse WHERE id = ?");
        $stockOut->bind_param("i", $id);
        $stockOut->execute();
    } else {
        echo "No stocks have been added, please input new stock summary." . $this->conn->error();
    }
}
?>