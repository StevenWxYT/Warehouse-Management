<?php
session_start();
include("function.php");

if (!empty($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$db = new DBConn();
$stock = new DBFunc($db->conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $image = $_POST['image'] ?? '';
    $sku = $_POST['sku'] ?? '';
    $rack = $_POST['rack'] ?? '';
    $zone = $_POST['zone'] ?? '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

    // Showing all WMS data inputs (Fetch after insert)
    $fetch = $db->conn->prepare("SELECT * FROM warehouse WHERE id = ?");
    $fetch->bind_param('i', $id);
    $fetch->execute();
    $result = $fetch->get_result();
    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
        // (Optional) display $data here
    }
    $fetch->close();

    // Stock check-ins
    $stockIn = $db->conn->prepare("INSERT INTO warehouse (id, image, sku, rack, zone, quantity) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stockIn) {
        $stockIn->bind_param("issssi", $id, $image, $sku, $rack, $zone, $quantity);
        $stockIn->execute();
        $stockIn->close();
    } else {
        echo "Insert failed: " . $db->conn->error;
    }

    // Stock check-outs (if needed — same as check-in here)
    $stockOut = $db->conn->prepare("INSERT INTO warehouse (id, image, sku, rack, zone, quantity) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stockOut) {
        $stockOut->bind_param("issssi", $id, $image, $sku, $rack, $zone, $quantity);
        $stockOut->execute();
        $stockOut->close();
    } else {
        echo "Insert failed: " . $db->conn->error;
    }
}
?>
