<?php
session_start();
include_once('db.php');

$items = $_SESSION['check_list_out'] ?? [];

$message = '';
$toastType = '';

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

        $stmt = $conn->prepare("SELECT quantity FROM wmsitem WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $stmt->bind_result($current_qty);
        $stmt->fetch();
        $stmt->close();

        if ($current_qty >= $qty_to_deduct) {
            $stmt = $conn->prepare("UPDATE wmsitem SET quantity = quantity - ? WHERE item_id = ?");
            $stmt->bind_param("ii", $qty_to_deduct, $item_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO wmsstock_out (item_id, item_code, item_name, quantity, unit_price, image_path, date, time)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issidsss", $item_id, $item_code, $item_name, $qty_to_deduct, $unit_price, $image_path, $date, $time);
            $stmt->execute();
            $stmt->close();

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

    unset($_SESSION['check_list_out']);

    if (count($failed) === 0) {
        $toastType = 'success';
        $message = "✅ 成功出库 {$successCount} 项货物。";
    } else {
        $toastType = 'error';
        $message = "✅ 成功出库 {$successCount} 项货物。❌ 以下出库失败：" . implode(', ', $failed);
    }
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
      background: linear-gradient(to right, #f8f9fa, #e9ecef);
      margin: 0;
      padding: 40px 20px;
    }

    h2 {
      text-align: center;
      color: #343a40;
      margin-bottom: 30px;
      font-size: 28px;
    }

    .card {
      max-width: 1000px;
      background: #ffffff;
      margin: 0 auto;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
      padding: 30px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 14px 12px;
      text-align: center;
      border-bottom: 1px solid #dee2e6;
    }

    th {
      background-color: #f1f3f5;
      color: #495057;
      font-weight: 600;
    }

    td {
      color: #333;
    }

    img {
      height: 60px;
      border-radius: 8px;
    }

    .btn, .back-btn {
      margin-top: 20px;
      display: block;
      width: 100%;
      max-width: 300px;
      margin-left: auto;
      margin-right: auto;
      padding: 14px 20px;
      font-size: 16px;
      color: white;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: 0.3s ease;
      text-align: center;
      text-decoration: none;
    }

    .btn {
      background: linear-gradient(90deg, #28a745, #218838);
    }

    .btn:hover {
      transform: scale(1.03);
      background: linear-gradient(90deg, #218838, #1e7e34);
    }

    .back-btn {
      background: linear-gradient(90deg, #6c757d, #495057);
    }

    .back-btn:hover {
      background: linear-gradient(90deg, #5a6268, #343a40);
      transform: scale(1.03);
    }

    .alert {
      text-align: center;
      color: #e03131;
      font-weight: 600;
      margin-top: 20px;
    }

    .toast-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
    }

    .toast {
      background-color: #28a745;
      color: white;
      padding: 14px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      font-size: 14px;
      animation: fadeInOut 3s ease forwards;
    }

    .toast.error {
      background-color: #dc3545;
    }

    @keyframes fadeInOut {
      0% { opacity: 0; transform: translateY(-10px); }
      10%, 90% { opacity: 1; transform: translateY(0); }
      100% { opacity: 0; transform: translateY(-10px); }
    }
  </style>
</head>
<body>

<h2>📋 Stock Out - Check List</h2>

<div class="card">
  <?php if (count($items) === 0 && !$message): ?>
    <p class="alert">⚠️ 没有货物可供检查。</p>
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
              <td><img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Item Image" onerror="this.src='wms.jpg'"></td>
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
    <a href="stock_manage.php" class="back-btn">⬅️ 返回 Stock Manage</a>
  <?php endif; ?>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
  const toastType = "<?= $toastType ?>";
  const message = <?= json_encode($message) ?>;

  if (toastType && message) {
    const container = document.getElementById("toastContainer");
    const toast = document.createElement("div");
    toast.className = "toast " + (toastType === 'error' ? 'error' : 'success');
    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
      toast.remove();
      if (toastType === 'success') {
        window.location.href = "stock_manage.php";
      }
    }, 2500);
  }
</script>

</body>
</html>
