<?php
session_start();
include_once('db.php');

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_code = $_POST['item_code'];
    $stock_out_qty = 1;

    $query = "SELECT item_id, item_name, quantity FROM wmsitem WHERE item_code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $item_code);
    $stmt->execute();
    $stmt->bind_result($item_id, $item_name, $current_qty);
    $stmt->fetch();
    $stmt->close();

    if ($item_id && $current_qty >= $stock_out_qty) {
        // ✅ 加入 session 以供 check list 确认
        $_SESSION['check_list_out'] = $_SESSION['check_list_out'] ?? [];

        // 检查是否重复扫码：相同 item_code 累加数量
        $found = false;
        foreach ($_SESSION['check_list_out'] as &$entry) {
            if ($entry['item_code'] === $item_code) {
                $entry['quantity'] += 1;
                $found = true;
                break;
            }
        }
        unset($entry); // 防止引用错误

        if (!$found) {
            $_SESSION['check_list_out'][] = [
                'item_id' => $item_id,
                'item_code' => $item_code,
                'item_name' => $item_name,
                'quantity' => 1
            ];
        }

        // 跳转到 check list 页面
        header("Location: check_list.php");
        exit();
    } else {
        $message = "❌ Invalid code or not enough stock.";
    }
}
?>
<!-- 以下 HTML 和你原来类似，只保留 UI + Toast + 声音提示 -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Auto Stock Out</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #fdfbfb, #ebedee, #e0d9f5, #e6f0ff);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .container {
      background-color: #ffffffdd;
      padding: 40px 30px;
      border-radius: 20px;
      box-shadow: 0 12px 40px rgba(0,0,0,0.1);
      max-width: 480px;
      width: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    h2 {
      font-size: 24px;
      color: #333;
      margin-bottom: 30px;
    }

    input[type="text"] {
      width: 90%;
      padding: 18px 20px;
      margin-bottom: 24px;
      border: 2px solid #ccc;
      border-radius: 14px;
      font-size: 20px;
      background-color: #fafafa;
    }

    input[type="text"]:focus {
      border-color: #7e57c2;
      box-shadow: 0 0 0 4px rgba(126, 87, 194, 0.2);
      background-color: #fff;
      outline: none;
    }

    .back-btn {
      margin-top: 20px;
      padding: 12px 20px;
      background-color: #8a76c4;
      color: white;
      border-radius: 10px;
      font-size: 16px;
      text-decoration: none;
    }

    .toast-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
    }

    .toast {
      background-color: #4CAF50;
      color: white;
      padding: 14px 20px;
      border-radius: 8px;
      margin-top: 10px;
      animation: fadeInOut 4s forwards;
    }

    .toast.error {
      background-color: #f44336;
    }

    @keyframes fadeInOut {
      0% { opacity: 0; transform: translateY(-10px); }
      10%, 90% { opacity: 1; transform: translateY(0); }
      100% { opacity: 0; transform: translateY(-10px); }
    }
  </style>
</head>
<body>

<div class="toast-container" id="toastContainer"></div>

<div class="container">
  <h2>📦 Scan to Stock Out</h2>
  <form method="POST" id="scanForm">
    <input type="text" name="item_code" placeholder="Scan item code..." autofocus autocomplete="off">
  </form>
  <a href="stock_manage.php" class="back-btn">⬅ Back to Stock Manage</a>
</div>

<audio id="successSound" src="success-beep.mp3" preload="auto"></audio>
<audio id="errorSound" src="error-buzz.mp3" preload="auto"></audio>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const input = document.querySelector('input[name="item_code"]');
    const form = document.getElementById("scanForm");
    input.focus();

    input.addEventListener("change", function () {
      form.submit();
    });

    <?php if ($message): ?>
      const toast = document.createElement("div");
      toast.className = "toast error";
      toast.textContent = <?= json_encode($message) ?>;
      document.getElementById("toastContainer").appendChild(toast);
      document.getElementById("errorSound").play();
    <?php endif; ?>
  });
</script>
</body>
</html>
