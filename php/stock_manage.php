<?php
include("function.php");

$db = new DBConn();
$user = new DBFunc($db);

$db = "SELECT * FROM warehouse WHERE id = ?";

?>