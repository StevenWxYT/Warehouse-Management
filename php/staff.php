<?php
include_once('db.php');
session_start();
date_default_timezone_set("Asia/Kuala_Lumpur");

// 初始化消息
$message = '';
$toastType = '';

// 表单提交处理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = $_POST['staff_id'] ?? '';
    $action = $_POST['action'] ?? '';
    $today = date('Y-m-d');
    $now = date('Y-m-d H:i:s');

    if (!empty($staff_id)) {
        // 查询今天是否已有记录
        $check_sql = "SELECT * FROM wmsstaff_log WHERE staff_id = ? AND log_date = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param('is', $staff_id, $today);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $update_sql = ($action === 'login') 
                ? "UPDATE wmsstaff_log SET login_time = ? WHERE staff_id = ? AND log_date = ?"
                : "UPDATE wmsstaff_log SET logout_time = ? WHERE staff_id = ? AND log_date = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('sis', $now, $staff_id, $today);
            $update_stmt->execute();
            $message = '出勤记录已更新';
            $toastType = 'success';
        } else {
            $login_time = ($action === 'login') ? $now : NULL;
            $logout_time = ($action === 'logout') ? $now : NULL;

            $insert_sql = "INSERT INTO wmsstaff_log (staff_id, login_time, logout_time, log_date)
                           VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param('isss', $staff_id, $login_time, $logout_time, $today);
            $insert_stmt->execute();
            $message = '出勤记录已添加';
            $toastType = 'success';
        }
    } else {
        $message = '请选择员工';
        $toastType = 'error';
    }
}

// 获取员工名单
$staff_sql = "SELECT id, username FROM wmsregister ORDER BY username ASC";
$staff_result = mysqli_query($conn, $staff_sql);

// 获取出勤记录
$logs_sql = "SELECT 
                wmsstaff_log.*, wmsregister.username 
             FROM 
                wmsstaff_log 
             INNER JOIN 
                wmsregister ON wmsstaff_log.staff_id = wmsregister.id 
             ORDER BY 
                wmsstaff_log.log_date DESC, wmsstaff_log.login_time DESC";
$logs_result = mysqli_query($conn, $logs_sql);
?>

<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <title>员工出勤记录</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #eef1f5;
      padding: 30px;
      margin: 0;
    }
    h1 {
      text-align: center;
      margin-bottom: 20px;
      color: #2c3e50;
    }
    .back-btn {
      text-align: center;
      margin-bottom: 30px;
    }
    .back-btn a button {
      background-color: #6c757d;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .back-btn a button:hover {
      background-color: #5a6268;
    }
    .form-box {
      background-color: #ffffff;
      max-width: 600px;
      margin: 0 auto 30px;
      padding: 25px 30px;
      border-radius: 16px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.08);
    }
    label {
      font-weight: bold;
      color: #333;
    }
    select, button {
      padding: 10px 16px;
      border-radius: 10px;
      border: 1px solid #ccc;
      margin: 10px 5px 0 0;
      font-size: 15px;
    }
    button {
      background-color: #3498db;
      color: white;
      transition: background-color 0.3s ease;
    }
    button.logout {
      background-color: #e74c3c;
    }
    button:hover {
      background-color: #2980b9;
    }
    button.logout:hover {
      background-color: #c0392b;
    }
    .toast {
      max-width: 600px;
      margin: 10px auto;
      padding: 14px;
      border-radius: 10px;
      color: white;
      text-align: center;
      font-weight: bold;
      background-color: <?= $toastType === 'success' ? '#2ecc71' : '#e74c3c' ?>;
      display: <?= $message ? 'block' : 'none' ?>;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background-color: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    th, td {
      padding: 14px 18px;
      text-align: center;
      border-bottom: 1px solid #f0f0f0;
      font-size: 15px;
    }
    th {
      background-color: #f7f9fb;
      font-weight: bold;
      color: #34495e;
    }
    tr:hover {
      background-color: #f0f8ff;
    }
    @media (max-width: 600px) {
      .form-box, .toast {
        width: 95%;
        padding: 20px;
      }
      table, th, td {
        font-size: 13px;
      }
    }
  </style>
</head>
<body>

  <h1>员工出勤记录</h1>

  <!-- 返回库存管理按钮 -->
  <div class="back-btn">
    <a href="stock_manage.php">
      <button>返回库存管理</button>
    </a>
  </div>

  <!-- 提示信息 -->
  <div class="toast"><?= htmlspecialchars($message) ?></div>

  <!-- 员工打卡表单 -->
  <div class="form-box">
    <form method="POST">
      <label for="staff_id">选择员工：</label><br>
      <select name="staff_id" required>
        <option value="">-- 请选择员工 --</option>
        <?php while ($staff = mysqli_fetch_assoc($staff_result)) : ?>
          <option value="<?= $staff['id'] ?>"><?= htmlspecialchars($staff['username']) ?></option>
        <?php endwhile; ?>
      </select>
      <button type="submit" name="action" value="login">登录</button>
      <button type="submit" name="action" value="logout" class="logout">登出</button>
    </form>
  </div>

  <!-- 出勤记录表格 -->
  <table>
    <thead>
      <tr>
        <th>员工姓名</th>
        <th>登录时间</th>
        <th>登出时间</th>
        <th>日期</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($logs_result)) : ?>
        <tr>
          <td><?= htmlspecialchars($row['username']) ?></td>
          <td><?= $row['login_time'] ?? '-' ?></td>
          <td><?= $row['logout_time'] ?? '-' ?></td>
          <td><?= $row['log_date'] ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</body>
</html>
