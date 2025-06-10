<?php
include("function.php");

$db = new DBConn();
$user = new DBFunc($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $user->loginUser($username, $password);
}
?>