<?php
include_once('db.php');
session_start();
date_default_timezone_set("Asia/Kuala_Lumpur");

$today = date('Y-m-d');
$time_now = date('H:i:s');
$user = null;

// 检查是否有登录用户
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];

    // 从 wmsregister 表中获取用户信息
    $sql = "SELECT username, email, role FROM wmsregister WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <title>员工信息</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f0f2f5;
      padding: 40px;
      text-align: center;
    }

    .card {
      background: white;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      max-width: 500px;
      margin: 0 auto;
    }

    h2 {
      color: #333;
      margin-bottom: 20px;
    }

    .info {
      font-size: 16px;
      margin: 10px 0;
      color: #555;
    }

    .label {
      font-weight: bold;
      color: #222;
    }

    .back-btn {
      margin-top: 30px;
    }

    .back-btn a button {
      padding: 10px 20px;
      background-color: #3498db;
      border: none;
      color: white;
      border-radius: 8px;
      font-size: 15px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .back-btn a button:hover {
      background-color: #2980b9;
    }

    .not-logged-in {
      color: #e74c3c;
      font-size: 18px;
      margin-top: 40px;
    }
  </style>
</head>
<body>

  <div class="card">
    <h2>员工信息</h2>

    <?php if ($user): ?>
      <div class="info"><span class="label">姓名：</span><?= htmlspecialchars($user['username']) ?></div>
      <div class="info"><span class="label">电子邮箱：</span><?= htmlspecialchars($user['email']) ?></div>
      <div class="info"><span class="label">角色：</span><?= htmlspecialchars($user['role']) ?></div>
      <div class="info"><span class="label">当前日期：</span><?= $today ?></div>
      <div class="info"><span class="label">当前时间：</span><?= $time_now ?></div>
    <?php else: ?>
      <div class="not-logged-in">⚠️ 当前没有登录的用户</div>
    <?php endif; ?>

    <div class="back-btn">
      <a href="stock_manage.php">
        <button>返回库存管理</button>
      </a>
    </div>
  </div>

</body>
</html>
