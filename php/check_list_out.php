<?php
session_start();
include_once('db.php');

$items = $_SESSION['check_list_out'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $successCount = 0;
    $failed = [];

    foreach ($items as $item) {
        $item_id = $item['item_id'];
        $qty_to_deduct = $item['quantity'];

        // 获取当前库存
        $check_sql = "SELECT quantity FROM wmsitem WHERE item_id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $stmt->bind_result($current_qty);
        $stmt->fetch();
        $stmt->close();

        if ($current_qty >= $qty_to_deduct) {
            // 1. 扣除库存
            $update_sql = "UPDATE wmsitem SET quantity = quantity - ? WHERE item_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ii", $qty_to_deduct, $item_id);
            $stmt->execute();
            $stmt->close();

            // 2. 写入日志
            $status = 'out';
            $date = date("Y-m-d");
            $time = date("H:i:s");

            $insert_log = "INSERT INTO wmsitem_log (item_id, status, date, time) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_log);
            $stmt->bind_param("isss", $item_id, $status, $date, $time);
            for ($i = 0; $i < $qty_to_deduct; $i++) {
                $stmt->execute(); // 记录多次
            }
            $stmt->close();

            $successCount++;
        } else {
            $failed[] = $item['item_code'] . "（库存不足）";
        }
    }

    // 清除 session
    unset($_SESSION['check_list_out']);

    // 提示结果
    $_SESSION['checklist_message'] =
        "✅ 成功出库 {$successCount} 项货物。" .
        (count($failed) > 0 ? "❌ 以下货物出库失败：" . implode(', ', $failed) : "");

    header("Location: auto_stock_out.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Check List - Stock Out</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f5f6fa;
      margin: 0;
      padding: 40px;
    }

    h2 {
      text-align: center;
      margin-bottom: 30px;
    }

    table {
      width: 100%;
      max-width: 800px;
      margin: 0 auto 30px auto;
      border-collapse: collapse;
      background-color: #fff;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 16px;
      border: 1px solid #ddd;
      text-align: center;
    }

    th {
      background-color: #eee;
    }

    .btn {
      display: block;
      width: 200px;
      margin: 0 auto;
      padding: 12px;
      background-color: #28a745;
      color: white;
      font-size: 16px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      text-align: center;
    }

    .btn:hover {
      background-color: #218838;
    }

    .back {
      margin-top: 20px;
      display: block;
      text-align: center;
      color: #555;
      text-decoration: none;
    }
  </style>
</head>
<body>

<h2>📋 Stock Out - Check List</h2>

<?php if (count($items) === 0): ?>
  <p style="text-align:center; color: red;">⚠️ 没有货物可供检查。</p>
<?php else: ?>
  <form method="POST">
    <table>
      <thead>
        <tr>
          <th>Item Code</th>
          <th>Item Name</th>
          <th>Quantity</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['item_code']) ?></td>
            <td><?= htmlspecialchars($item['item_name']) ?></td>
            <td><?= $item['quantity'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <button type="submit" class="btn">✅ 确认并出库</button>
  </form>
<?php endif; ?>

<a href="auto_stock_out.php" class="back">⬅ 返回扫码页面</a>

</body>
</html>
