<?php
include("function.php");

$db = new DBConn();
$user = new DBFunc($db->conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $user->loginUser($username, $password);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <link rel="stylesheet" href="master.css">
</head>
<body>
  <video autoplay muted loop id="bg-video">
    <source src="bg/My most beautiful drone shot – Cinematic FPV on an empty beach.mp4" type="video/mp4">
    Your browser does not support HTML5 video.
</video>
  <main class="form">
    <form action="index.php" method="POST">
      <label for="username">Username</label>
      <input type="text" name="username" required><br>
      <label for="password">Password</label>
      <input type="password" name="password" required><br>
      <button type="submit" name="login">Login</button><br>
      <p>Don't have an account?</p>
      <a href="register.php">
        <button type="button" class="link-button">Click here</button>
      </a>
    </form>
  </main>
</body>
</html>
