<?php
include 'db.php'; // 连接数据库

$message = ''; // 保存提示信息

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $role = trim($_POST['role'] ?? '');

    // 检查是否为空
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $message = "❌ Please fill in all fields.";
    }
    // 检查 email 格式
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Invalid email format.";
    }
    // 检查密码一致
    elseif ($password !== $confirm_password) {
        $message = "❌ Passwords do not match.";
    } else {
        // 检查用户名是否存在
        $checkStmt = $conn->prepare("SELECT id FROM wmsregister WHERE username = ?");
        if ($checkStmt) {
            $checkStmt->bind_param("s", $username);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $message = "❌ Username already exists.";
                $checkStmt->close();
            } else {
                $checkStmt->close();

                // 加密密码
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // 插入新用户
                $insertStmt = $conn->prepare("INSERT INTO wmsregister (username, email, password, role) VALUES (?, ?, ?, ?)");
                if ($insertStmt) {
                    $insertStmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

                    if ($insertStmt->execute()) {
                        header("Refresh: 2; URL=login.php");
                        $message = "✅ Registration successful! You may now log in.";
                    } else {
                        $message = "❌ Registration failed: " . htmlspecialchars($insertStmt->error);
                    }

                    $insertStmt->close();
                } else {
                    $message = "❌ Database error (insertStmt): " . htmlspecialchars($conn->error);
                }
            }
        } else {
            $message = "❌ Database error (checkStmt): " . htmlspecialchars($conn->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register</title>
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

    .register-container {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      width: 340px;
      text-align: center;
      z-index: 1;
      animation: floaty 6s ease-in-out infinite;
    }

    @keyframes floaty {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }

    .register-container h2 {
      margin-bottom: 20px;
      color: #333;
    }

    .register-container input {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .register-container input:focus {
      border-color: #4CAF50;
      box-shadow: 0 0 10px rgba(76, 175, 80, 0.5);
      outline: none;
    }

    .register-container button {
      width: 100%;
      padding: 12px;
      background: linear-gradient(135deg, #8a76c4);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      transition: background 0.3s ease;
    }

    .register-container button:hover {
      background: linear-gradient(135deg, #8a76c4);
    }

    .message {
      color: #d8000c;
      background-color: #ffdddd;
      border-left: 6px solid #f44336;
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 6px;
      font-weight: bold;
    }

    .message.success {
      color: #155724;
      background-color: #d4edda;
      border-left-color: #28a745;
    }

    .role-selection {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      margin: 10px 0;
    }

    .role-selection label {
      font-weight: bold;
      display: flex;
      align-items: center;
      margin: 5px 0;
      gap: 8px;
    }

    .login-link {
      margin-top: 15px;
    }

    .login-link button {
      width: 100%;
      padding: 10px;
      background: #8a76c4;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      transition: background 0.3s ease;
    }

    .login-link button:hover {
      background: #7b68b4;
    }
  </style>
</head>
<body>

  <div class="register-container">
    <h2>Register</h2>

    <?php if (!empty($message)) : ?>
      <div class="message <?= strpos($message, '✅') === 0 ? 'success' : '' ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="register.php">
      <input type="text" name="username" placeholder="Username" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>

      <div class="role-selection">
        <label><input type="radio" name="role" value="Admin" required> Admin</label>
        <label><input type="radio" name="role" value="Salesman" required> Salesman</label>
      </div>

      <button type="submit">Register</button>
    </form>

    <div class="login-link">
      <form method="get" action="login.php">
        <button type="submit">Go to Login</button>
      </form>
    </div>
  </div>

</body>
</html>
