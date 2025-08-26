<?php
session_start();
include_once('db.php');

$items = $_POST['items'] ?? ($_SESSION['check_list_in'] ?? []);

$message = '';
$toastType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $successCount = 0;

    // 保存 check_list 到 session
    $_SESSION['check_list_in'] = $items; 

    foreach ($items as $item) {
        $item_id = $item['item_id'];
        $item_code = $item['item_code'];
        $item_name = $item['item_name'];
        $unit_price = $item['unit_price'];
        $image_path = $item['image_path'];
        $qty_to_add = (int)$item['quantity'];  // ✅ 补货数量
        $date = date("Y-m-d");
        $time = date("H:i:s");

        if ($qty_to_add > 0) {
            // ✅ 增加库存
            $stmt = $conn->prepare("UPDATE wmsitem SET quantity = quantity + ? WHERE item_id = ?");
            $stmt->bind_param("ii", $qty_to_add, $item_id);
            $stmt->execute();
            $stmt->close();

 // ✅ 插入补货记录到 wmsitem_log
$status = 'restock';
$stmt = $conn->prepare("INSERT INTO wmsitem_log (item_id, item_quantity,status, date, time) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $item_id,$qty_to_add, $status, $date, $time);
// echo "INSERT INTO wmsitem_log (item_id, item_quantity,status, date, time) VALUES ($item_id,$qty_to_add, $status, $date, $time)";
$stmt->execute();
$stmt->close();

$successCount++;

        }
    }

    unset($_SESSION['check_list_in']); // 补货完成清空

 if ($successCount > 0) {
    $toastType = 'success';
    $message = "✅ 成功补货 {$successCount} 项货物。";
} else {
    $toastType = 'error';
    $message = "❌ 没有任何货物被补货。";
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
    .qty-input {
  width: 60px;
  height: 36px;
  text-align: center;
  border: 1px solid #ced4da;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  color: #343a40;
  background: #fff;
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.08);
  transition: all 0.2s ease-in-out;
}

.qty-input:focus {
  outline: none;
  border-color: #4dabf7;
  box-shadow: 0 0 0 3px rgba(77, 171, 247, 0.3);
}

.qty-btn {
  background: #f8f9fa;
  border: 1px solid #ced4da;
  border-radius: 8px;
  padding: 6px 10px;
  cursor: pointer;
  font-size: 16px;
  transition: 0.2s ease-in-out;
}

.qty-btn:hover {
  background: #e9ecef;
  transform: scale(1.05);
}


  </style>
</head>
<body>

<h2>📋 Restock Check List</h2>

<div class="card">
  <?php if (count($items) === 0 && !$message): ?>
    <p class="alert">⚠️ No items available for checking。</p>
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
  <?php foreach ($items as $index => $item): ?>
    <tr>
      <td><img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Item Image" onerror="this.src='wms.jpg'"></td>
      <td><?= htmlspecialchars($item['item_code']) ?></td>
      <td><?= htmlspecialchars($item['item_name']) ?></td>
      <td>
        <div style="display:flex;align-items:center;justify-content:center;gap:6px;">
          <button type="button" class="qty-btn" onclick="updateQty(<?= $index ?>, -1)">➖</button>
        <input type="text" 
       name="items[<?= $index ?>][quantity]" 
       id="qty-<?= $index ?>" 
       value="<?= $item['quantity'] ?>" 
       readonly 
       class="qty-input">

          <button type="button" class="qty-btn" onclick="updateQty(<?= $index ?>, 1)">➕</button>
        </div>
        <input type="hidden" name="items[<?= $index ?>][item_id]" value="<?= $item['item_id'] ?>">
        <input type="hidden" name="items[<?= $index ?>][item_code]" value="<?= htmlspecialchars($item['item_code']) ?>">
        <input type="hidden" name="items[<?= $index ?>][item_name]" value="<?= htmlspecialchars($item['item_name']) ?>">
        <input type="hidden" name="items[<?= $index ?>][unit_price]" id="price-<?= $index ?>" value="<?= $item['unit_price'] ?>">
        <input type="hidden" name="items[<?= $index ?>][image_path]" value="<?= htmlspecialchars($item['image_path']) ?>">
      </td>
      <td id="total-<?= $index ?>"><?= number_format($item['unit_price'] * $item['quantity'], 2) ?></td>
    </tr>
  <?php endforeach; ?>
</tbody>

      </table>
      <button type="submit" class="btn">✅ Confirm and Restock</button>
    </form>
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
        window.location.href = "index.php";
      }
    }, 2500);
  }

function updateQty(index, change) {
  const qtyInput = document.getElementById("qty-" + index);
  const priceInput = document.getElementById("price-" + index);
  const totalCell = document.getElementById("total-" + index);

  let qty = parseInt(qtyInput.value) || 0;
  qty = qty + change;
  if (qty < 1) qty = 1; // 不允许小于 1

  qtyInput.value = qty;

  const unitPrice = parseFloat(priceInput.value);
  const total = (unitPrice * qty).toFixed(2);
  totalCell.textContent = total;
}
</script>

</body>
</html>
