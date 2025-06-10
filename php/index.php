<?php
include("function.php");

$db = new DBConn();
$user = new DBFunc($db);

if(isset($_SESSION['username'])){
    header('Location: stock_manage.php');
}
?>