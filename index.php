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
  <style>
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      font-family: Arial, sans-serif;
      background: linear-gradient(120deg, #a1c4fd, #c2e9fb, #d4fc79, #96e6a1);
      background-size: 400% 400%;
      animation: gradientFlow 18s ease infinite;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    @keyframes gradientFlow {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    form {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      width: 300px;
      text-align: center;
      animation: floaty 6s ease-in-out infinite;
    }
    @keyframes floaty {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }
    input, select {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    input:focus {
      border-color: #4CAF50;
      box-shadow: 0 0 12px rgba(76, 175, 80, 0.5);
      outline: none;
    }
    button {
      width: 100%;
      padding: 12px;
      background: #007bff;
      border: none;
      color: white;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      border-radius: 8px;
      transition: all 0.3s ease;
    }
    button:hover {
      background: #0056b3;
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(0, 123, 255, 0.6);
    }
    .link-button {
      background: transparent;
      color: #007bff;
      border: none;
      padding: 0;
      font-size: 14px;
      cursor: pointer;
      text-decoration: underline;
    }
  </style>
</head>
<body>
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
