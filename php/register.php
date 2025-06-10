<?php
include ("db.php");
include ("function.php");

$db = new DBConn();
$user = new DBFunc($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (!empty($username) && !empty($password) && !empty($role)) {
        $user->registerUser($username, $password, $role);
    } else {
        echo "Error: " . $this->conn->error;
    }
}
?>