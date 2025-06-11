<?php
include 'db.php'; // 包含你的数据库连接 $conn

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo "❌ 请输入用户名和密码";
        exit();
    }

    // 查询数据库获取用户
    $stmt = $conn->prepare("SELECT username, role FROM username WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($hashedPassword, $role);
            $stmt->fetch();

            if (password_verify($password, $hashedPassword)) {
                session_start();
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;

                header("Location: welcome.php");
                exit();
            } else {
                echo "❌ 密码错误";
            }
        } else {
            echo "❌ 用户不存在";
        }

        $stmt->close();
    } else {
        echo "❌ 数据库错误：" . htmlspecialchars($conn->error);
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login Page</title>
  <style>
/* 🌈 流动渐变背景 */
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background: linear-gradient(120deg, #a1c4fd, #c2e9fb, #d4fc79, #96e6a1);
  background-size: 400% 400%;
  animation: gradientFlow 18s ease infinite;
  height: 100vh;
  overflow: hidden;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
}

/* 🎞️ 背景动画 */
@keyframes gradientFlow {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

/* ☁️ 轻微浮动效果给 container（可选） */
.login-container {
  background: rgba(255, 255, 255, 0.95);
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  width: 320px;
  text-align: center;
  z-index: 1;
  animation: floaty 6s ease-in-out infinite;
}

@keyframes floaty {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

@keyframes gradientFlow {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

/* 📦 登入容器 */
.login-container {
  background: rgba(255, 255, 255, 0.95);
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  width: 320px;
  text-align: center;
  z-index: 1;
}

.login-container h2 {
  margin-bottom: 20px;
  color: #333;
}

/* 🧊 输入框样式 */
.login-container input {
  width: 100%;
  padding: 12px;
  margin: 10px 0;
  border: 1px solid #ccc;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.login-container input:focus {
  border-color: #4CAF50;
  box-shadow: 0 0 10px rgba(76, 175, 80, 0.5);
  outline: none;
}

/* ✨ 渐变按钮 */
.login-container button {
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, #6fcf97, #56cc9d);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  transition: background 0.3s ease;
}

.login-container button:hover {
  background: linear-gradient(135deg, #56cc9d, #45a077);
}

  </style>
</head>
<body>

   <div class="decor one"></div>
  <div class="decor two"></div>
  <div class="decor three"></div>
  <div class="decor four"></div>

  <div class="login-container">
    <h2>Login</h2>
    <form method="POST" action="login.php">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
  </div>

</body>
</html>
