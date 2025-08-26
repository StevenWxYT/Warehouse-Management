<?php
include_once('db.php');
session_start();

$check_list = $_SESSION['check_list'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        $all_success = true;

        foreach ($check_list as $item) {
            $item_name = $item['item_name'];
            $item_code = $item['item_code'];
            $quantity = $item['quantity'];
            $unit_price = $item['unit_price'];
            $image_path = $item['image_path'] ?? '';
            $date = date("Y-m-d");
            $time = date("H:i:s");
            $category_id = $item['category_id'] ?? 1;

            $check_stmt = $conn->prepare("SELECT item_id FROM wmsitem WHERE item_code = ?");
            $check_stmt->bind_param("s", $item_code);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO wmsitem (item_name, quantity, item_code, image_path, date, time, category_id, unit_price)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sissssdi", $item_name, $quantity, $item_code, $image_path, $date, $time, $category_id, $unit_price);
                $stmt->execute();

                $new_item_id = $stmt->insert_id;

                $log_stmt = $conn->prepare("INSERT INTO wmsitem_log (item_id,item_quantity, status, date, time) VALUES (?, ?,'in', ?, ?)");
                $log_stmt->bind_param("iiss", $new_item_id,$quantity, $date, $time);
                $log_stmt->execute();
                $log_stmt->close();

                $stmt->close();
            } else {
                $all_success = false;
            }

            $check_stmt->close();
        }

        unset($_SESSION['check_list']);
        $status = $all_success ? "success" : "error";
        header("Location: check_list.php?status=" . $status);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Check List</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body {
      margin: 0;
      padding: 40px 20px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #f3f4f7, #e0e6ff);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .card {
      background: white;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 1000px;
    }

    h2 {
      text-align: center;
      color: #5a4dac;
      margin-bottom: 25px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }

    th, td {
      text-align: center;
      padding: 14px 18px;
      border-bottom: 1px solid #e0e0e0;
      vertical-align: middle;
    }

    th {
      background-color: #8a76c4;
      color: white;
      font-weight: 600;
    }

    tr:last-child td {
      border-bottom: none;
    }

    .product-img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    .btn {
      display: inline-block;
      margin-top: 30px;
      padding: 12px 28px;
      background-color: #8a76c4;
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .btn:hover {
      background-color: #6f5aa6;
      transform: scale(1.05);
    }

    .quantity-control {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 8px;
    }

    .qty-btn {
      width: 30px;
      height: 30px;
      background: #8a76c4;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
    }

    .qty-btn:hover {
      background: #6f5aa6;
    }

    .qty-value {
      min-width: 40px;
      display: inline-block;
      text-align: center;
      font-weight: bold;
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
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      font-size: 14px;
      animation: fadeInOut 5s forwards;
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
  <div class="card">
    <h2>🧾 Check List — Confirm New Items</h2>

    <?php if (!empty($check_list)): ?>
      <form method="post">
        <table>
 <thead>
  <tr>
    <th>Photo</th>
    <th>Item Name</th>
    <th>Item Code</th>
    <th>Quantity</th>
    <th>Total Price (RM)</th>
    <th>Category</th>
  </tr>
</thead>
<tbody>
  <?php foreach ($check_list as $index => $item): ?>
    <tr>
      <td>
        <img src="<?= htmlspecialchars($item['image_path'] ?? 'https://via.placeholder.com/60') ?>"
            class="product-img" alt="Item Image">
      </td>
      <td><?= htmlspecialchars($item['item_name']) ?></td>
      <td><?= htmlspecialchars($item['item_code']) ?></td>
      <td>
        <div class="quantity-control">
          <button type="button" class="qty-btn" onclick="changeQty(<?= $index ?>, -1)">-</button>
          <span class="qty-value" id="qty-<?= $index ?>"><?= htmlspecialchars($item['quantity']) ?></span>
          <button type="button" class="qty-btn" onclick="changeQty(<?= $index ?>, 1)">+</button>
        </div>
        <input type="hidden" name="items[<?= $index ?>][quantity]" id="input-qty-<?= $index ?>" value="<?= htmlspecialchars($item['quantity']) ?>">
        <input type="hidden" name="items[<?= $index ?>][unit_price]" id="input-price-<?= $index ?>" value="<?= htmlspecialchars($item['unit_price']) ?>">
      </td>
      <td id="price-<?= $index ?>"><?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
      <td><?= htmlspecialchars($item['category'] ?? '-') ?></td>
    </tr>
  <?php endforeach; ?>
</tbody>

        </table>

        <div class="btn-container" style="text-align:center; margin-top:20px;">
          <button type="submit" name="confirm" class="btn">Confirm & Save</button>
        </div>
      </form>
    <?php else: ?>
      <div class="no-data">
        No items to check.<br><br>
        <a href="index.php" class="btn">Go Back</a>
      </div>
    <?php endif; ?>
  </div>

  <div class="toast-container" id="toastContainer"></div>

  <script>
     function changeQty(index, delta) {
      const qtyEl = document.getElementById("qty-" + index);
      const inputQty = document.getElementById("input-qty-" + index);
      const inputPrice = document.getElementById("input-price-" + index);
      const priceEl = document.getElementById("price-" + index);

      let qty = parseInt(qtyEl.textContent);
      const unitPrice = parseFloat(inputPrice.value);

      qty = Math.max(1, qty + delta); // 最少为 1
      qtyEl.textContent = qty;
      inputQty.value = qty;

      priceEl.textContent = (qty * unitPrice).toFixed(2);
    }

    function getQueryParam(name) {
      const url = new URL(window.location.href);
      return url.searchParams.get(name);
    }

    function showToast(message, isError = false) {
      const container = document.getElementById("toastContainer");
      const toast = document.createElement("div");
      toast.className = "toast" + (isError ? " error" : "");
      toast.textContent = message;
      container.appendChild(toast);
      setTimeout(() => toast.remove(), 5000);
    }

    const status = getQueryParam("status");
    if (status === "success") {
      showToast("✅ Items successfully saved.");
    } else if (status === "error") {
      showToast("⚠️ Some items already exist and were not saved.", true);
    }
  </script>
</body>
</html>
