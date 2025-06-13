<?php
include("function.php");

$db = new DBConn();
$stock = new DBFunc($db->conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sku = $_POST['sku'];
    $image = $_POST['image'];
    $rack = $_POST['rack'];
    $zone = $_POST['zone'];
    $quantity = $_POST['quantity'];

    $stmt = 
}
?>