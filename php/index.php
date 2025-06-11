<?php
include("function.php");

$db = new DBConn();
$user = new DBFunc($db);

if(!empty($_SESSION['username'])){
    header('Location: login.php');
    exit();
}
?>