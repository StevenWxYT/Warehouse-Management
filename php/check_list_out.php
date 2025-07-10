<?php
session_start();
include_once('db.php');

$items = $_SESSION['check_list_out'] ?? [];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $successCount = 0;
    $failed = [];

    foreach ($items as $item) {
        $item_id = $item['item_id'];
        $qty_to_deduct = $item['quantity'];
        $item_code = $item['item_code'];
        $item_name = $item['item_name'];
        $unit_price = $item['unit_price'];
        $image_path = $item['image_path'];
        $date = date("Y-m-d");
        $time = date("H:i:s");

        // 获取当前库存
        $stmt = $conn->prepare("SELECT quantity FROM wmsitem WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $stmt->bind_result($current_qty);
        $stmt->fetch();
        $stmt->close();

        if ($current_qty >= $qty_to_deduct) {
            // 扣库存
            $stmt = $conn->prepare("UPDATE wmsitem SET quantity = quantity - ? WHERE item_id = ?");
            $stmt->bind_param("ii", $qty_to_deduct, $item_id);
            $stmt->execute();
            $stmt->close();

            // 写入出库表
            $stmt = $conn->prepare("INSERT INTO wmsstock_out (item_id, item_code, item_name, quantity, unit_price, image_path, date, time)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issidsss", $item_id, $item_code, $item_name, $qty_to_deduct, $unit_price, $image_path, $date, $time);
            $stmt->execute();
            $stmt->close();

            // 写入日志表
            $status = 'out';
            $stmt = $conn->prepare("INSERT INTO wmsitem_log (item_id, status, date, time) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $item_id, $status, $date, $time);
            for ($i = 0; $i < $qty_to_deduct; $i++) {
                $stmt->execute();
            }
            $stmt->close();

            $successCount++;
        } else {
            $failed[] = $item_code . "（库存不足）";
        }
    }

    // 清除出库 session 清单
    unset($_SESSION['check_list_out']);

    // 结果信息
    $message = "✅ 成功出库 {$successCount} 项货物。" .
               (count($failed) > 0 ? "❌ 以下出库失败：" . implode(', ', $failed) : "");
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
    h2 { text-align: center; margin-bottom: 30px; }
    table {
      width: 100%;
      max-width: 1000px;
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
    th { background-color: #eee; }
    img { height: 60px; border-radius: 6px; }
    .btn {
      display: block;
      width: 240px;
      margin: 0 auto;
      padding: 14px;
      background-color: #28a745;
      color: white;
      font-size: 16px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
    }
    .btn:hover { background-color: #218838; }
    .back {
      margin-top: 20px;
      display: block;
      text-align: center;
      color: #555;
      text-decoration: none;
    }
    .message {
      text-align: center;
      margin-bottom: 20px;
      font-weight: bold;
      color: green;
    }
  </style>
</head>
<body>

<h2>📋 Stock Out - Check List</h2>

<?php if (!empty($message)): ?>
  <p class="message"><?= htmlspecialchars($message) ?></p>
  <a href="auto_stock_out.php" class="back">⬅ 返回扫码页面</a>
<?php elseif (count($items) === 0): ?>
  <p style="text-align:center; color: red;">⚠️ 没有货物可供检查。</p>
<?php else: ?>
  <form method="POST">
    <table>
      <thead>
        <tr>
          <th>Image</th>
          <th>Item Code</th>
          <th>Item Name</th>
          <th>Quantity</th>
          <th>Unit Price (RM)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Item Image"></td>
            <td><?= htmlspecialchars($item['item_code']) ?></td>
            <td><?= htmlspecialchars($item['item_name']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item['unit_price'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <button type="submit" class="btn">✅ 确认并出库</button>
  </form>
<?php endif; ?>

</body>
</html>
