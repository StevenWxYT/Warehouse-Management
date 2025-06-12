<?php
include("function.php");

if(!empty($_SESSION['username'])){
    header('Location: login.php');
    exit();
}

$db = new DBConn();
$stock = new DBFunc($db->conn);


?>