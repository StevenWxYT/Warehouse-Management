<?php
include 'db.php'; // 连接数据库

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // 检查是否为空
    if (empty($username) || empty($password) || empty($confirm_password)) {
        echo "❌ Please fill in all fields.";
        exit();
    }

    // 检查密码一致性
    if ($password !== $confirm_password) {
        echo "❌ Passwords do not match.";
        exit();
    }

    // 检查用户名是否已存在
    $checkStmt = $conn->prepare("SELECT id FROM wmsregister WHERE username = ?");
    if ($checkStmt) {
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo "❌ Username already exists.";
            $checkStmt->close();
            exit();
        }
        $checkStmt->close();
    } else {
        echo "❌ Database error (checkStmt): " . htmlspecialchars($conn->error);
        exit();
    }

    // 加密密码
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 插入新用户
    $insertStmt = $conn->prepare("INSERT INTO wmsregister (username, password, confirm_password) VALUES (?, ?, 'user')");
    if ($insertStmt) {
        $insertStmt->bind_param("ss", $username, $hashedPassword);

        if ($insertStmt->execute()) {
            echo "<script>alert('✅Register failed')</script>";
            header("Location:.php");
        } else {
            echo "❌ Registration failed: " . htmlspecialchars($insertStmt->error);
        }

        $insertStmt->close();
    } else {
        echo "❌ Database error (insertStmt): " . htmlspecialchars($conn->error);
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
/* 🌈 背景渐变和流动效果 */
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

/* 🎞️ 背景渐变动画 */
@keyframes gradientFlow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* 📦 表单容器 */
form {
    background: rgba(255, 255, 255, 0.95);
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    width: 300px;
    text-align: center;
    animation: floaty 6s ease-in-out infinite;
}

/* ☁️ 浮动动画效果（可选） */
@keyframes floaty {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

/* ✨ 输入框样式 */
input {
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

/* 🔘 按钮样式 */
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
    box-shadow: 0 0 0 transparent;
}

/* ✨ 按钮悬停效果 */
button:hover {
    background: #0056b3;
    transform: scale(1.05);
    box-shadow: 0 0 15px rgba(0, 123, 255, 0.6);
}

    </style>
</head>
<body>

<form action="register.php" method="POST">
    <h2>Register</h2>
    <input type="text" name="username" placeholder="username" required>
    <input type="password" name="password" placeholder="password" required>
    <input type="password" name="confirm_password" placeholder="confirm_password" required>
    <button type="submit">Register</button>
</form>


</body>
</html>
