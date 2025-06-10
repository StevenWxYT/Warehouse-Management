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
    $stmt = $conn->prepare("SELECT password, role FROM users WHERE username = ?");
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
    .login-container button {
  width: 100%;
  padding: 12px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.3s ease;
  box-shadow: 0 0 0 transparent;
}

.login-container button:hover {
  background-color: #45a049;
  box-shadow: 0 0 15px rgba(76, 175, 80, 0.6);
}
.login-container button:hover {
  background-color: #45a049;
  box-shadow: 0 0 18px rgba(76, 175, 80, 0.7);
  transform: scale(1.03);
}
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background: url('https://i.imgur.com/zN1Z2gL.jpg') no-repeat center center fixed; /* 🎨 猫咪插画背景 */
  background-size: cover;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  overflow: hidden;
  position: relative;
}

/* 🐾 背景猫爪图案 */
.decor {
  position: absolute;
  width: 60px;
  height: 60px;
  background-image: url('https://cdn-icons-png.flaticon.com/512/616/616408.png'); /* ✅ 真正的猫爪图标 */
  background-size: contain;
  background-repeat: no-repeat;
  opacity: 0.3;
  transition: transform 0.6s ease, opacity 0.3s ease;
}
.decor:hover {
  transform: rotate(360deg) scale(1.1);
  opacity: 0.6;
}

/* 猫爪图案位置设置 */
.decor.one { top: 10%; left: 10%; }
.decor.two { top: 20%; right: 15%; }
.decor.three { bottom: 15%; left: 20%; }
.decor.four { bottom: 10%; right: 10%; }

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

/* 🧊 输入框动画 */
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

/* 💡 按钮亮光 + 动画 */
.login-container button {
  width: 100%;
  padding: 12px;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.3s ease;
  box-shadow: 0 0 0 transparent;
}
.login-container button:hover {
  background-color: #45a049;
  box-shadow: 0 0 15px rgba(76, 175, 80, 0.6);
  transform: scale(1.03);
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
