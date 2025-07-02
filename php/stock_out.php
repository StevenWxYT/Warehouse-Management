<?php
include_once('db.php');
session_start();

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_code = $_POST['item_code'];
    $stock_out_qty = 1;

    $query = "SELECT item_id, quantity FROM wmsitem WHERE item_code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $item_code);
    $stmt->execute();
    $stmt->bind_result($item_id, $current_qty);
    $stmt->fetch();
    $stmt->close();

    if ($item_id && $current_qty >= $stock_out_qty) {
        $new_qty = $current_qty - $stock_out_qty;

        // 更新库存数量
        $update = "UPDATE wmsitem SET quantity = ? WHERE item_id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param('ii', $new_qty, $item_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // 写入 item_log
            $log_sql = "INSERT INTO item_log (item_id, item_code, quantity, status, date, time)
                        VALUES (?, ?, ?, 'out', CURDATE(), CURTIME())";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param('isi', $item_id, $item_code, $stock_out_qty);
            $log_stmt->execute();
            $log_stmt->close();

            $message = "✅ Stock out success for item code: $item_code";
            $success = true;
        } else {
            $message = "❌ Failed to update stock.";
        }
        $stmt->close();
    } else {
        $message = "❌ Invalid code or not enough stock.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Auto Stock Out</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
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
      justify-content: center;
      align-items: center;
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 30px;
    }

    .header h2 {
      font-size: 24px;
      color: #333;
      margin: 0;
    }

    .header span {
      font-size: 28px;
    }

    form {
      display: flex;
      flex-direction: column;
      width: 100%;
      align-items: center;
    }

    input[type="text"] {
      width: 90%;
      padding: 18px 20px;
      margin-bottom: 24px;
      border: 2px solid #ccc;
      border-radius: 14px;
      font-size: 20px;
      font-weight: 300;
      outline: none;
      transition: all 0.3s ease;
      background-color: #fafafa;
      box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
    }

    input[type="text"]:focus {
      border-color: #7e57c2;
      box-shadow: 0 0 0 4px rgba(126, 87, 194, 0.2);
      background-color: #fff;
    }

    input[type="text"]::placeholder {
      color: #bbb;
      font-size: 16px;
      font-weight: 400;
    }

    .back-btn {
      margin-top: 20px;
      text-decoration: none;
      padding: 12px 20px;
      background-color: #8a76c4;
      color: white;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 500;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      transition: background-color 0.3s ease;
    }

    .back-btn:hover {
      background-color: #7b66b1;
    }

    /* ✅ Toast 样式 */
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
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      font-size: 14px;
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

  <!-- ✅ Toast 容器 -->
  <div class="toast-container" id="toastContainer"></div>

  <div class="container">
    <div class="header">
      <span>📦</span>
      <h2>Scan to Stock Out</h2>
    </div>

    <form method="POST" id="scanForm">
      <input type="text" name="item_code" placeholder="Scan item code..." autofocus autocomplete="off">
    </form>

    <!-- 返回按钮 -->
    <a href="stock_manage.php" class="back-btn">Back to Manage Stock</a>
  </div>

  <!-- 声音提示 -->
  <audio id="successSound" src="success-beep.mp3" preload="auto"></audio>
  <audio id="errorSound" src="error-buzz.mp3" preload="auto"></audio>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const input = document.querySelector('input[name="item_code"]');
      const form = document.getElementById("scanForm");
      input.focus();
      input.value = "";

      input.addEventListener("change", function () {
        form.submit();
      });

      <?php if ($message): ?>
        const success = <?= json_encode($success) ?>;
        const message = <?= json_encode($message) ?>;
        const toastContainer = document.getElementById("toastContainer");

        const toast = document.createElement("div");
        toast.className = "toast" + (success ? "" : " error");
        toast.textContent = message;
        toastContainer.appendChild(toast);

        if (success) {
          document.getElementById("successSound").play();
        } else {
          document.getElementById("errorSound").play();
        }
      <?php endif; ?>
    });
  </script>
</body>
</html>
