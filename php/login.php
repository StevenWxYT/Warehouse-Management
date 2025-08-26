<?php
session_start();
include_once('db.php');

$message = '';
$messageType = ''; // success 或 error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT id, username, email, role, password FROM wmsregister WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];

            $message = "✅ Register Successfull, Welcome " . htmlspecialchars($row['username']);
            $messageType = 'success';
            header("Location: index.php");
            exit;
        } else {
            $message = "❌ 密码错误，请重新输入";
            $messageType = 'error';
        }
    } else {
        $message = "❌ 用户不存在，请先注册";
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Page</title>
  <style>
      body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: linear-gradient(120deg, #fdfbfb, #ebedee, #e0d9f5, #e6f0ff);
        background-size: 400% 400%;
        animation: gradientFlow 18s ease infinite;
        height: 100vh;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
      }
      @keyframes gradientFlow {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
      }
      .login-container {
        background: rgba(255, 255, 255, 0.95);
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        width: 320px;
        text-align: center;
        animation: floaty 6s ease-in-out infinite;
      }
      @keyframes floaty {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
      }
      .login-container h2 {
        margin-bottom: 20px;
        color: #333;
      }
      .login-container input {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 8px;
        transition: all 0.3s ease;
      }
      .login-container input:focus {
        border-color: #8a76c4;
        outline: none;
      }
      .login-container button {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #8a76c4, #715abf);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.3s ease;
      }
      .login-container button:hover {
        background: #7b68b4;
      }
      .message {
        margin-bottom: 15px;
        padding: 10px;
        border-radius: 8px;
        font-weight: bold;
        color: white;
      }
      .error {
        background-color: #dc3545;
      }
  </style>
</head>
<body>

  <div class="login-container">
    <h2>Login</h2>

    <?php if (!empty($message)) : ?>
      <div class="message error">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
  </div>

</body>
</html>
