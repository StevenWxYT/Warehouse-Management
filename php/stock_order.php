<?php
include_once('db.php');
session_start();

$toastMessage = "";

// 获取分类数据
$category_sql = "SELECT category_id, category FROM wmscategory";
$category_result = mysqli_query($conn, $category_sql);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $item_code = $_POST['item_code'];
    $unit_price = $_POST['unit_price']; // ⬅️ 获取单价
    $note = $_POST['note'] ?? '';
    $category_id = $_POST['category_id'];
    $date = date("Y-m-d");
    $time = date("H:i:s");

    // 获取分类名称
    $category = '';
    $cat_query = $conn->prepare("SELECT category FROM wmscategory WHERE category_id = ?");
    $cat_query->bind_param("i", $category_id);
    $cat_query->execute();
    $cat_query->bind_result($category);
    $cat_query->fetch();
    $cat_query->close();

    // 上传图片
    $image_path = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $target_file = $upload_dir . time() . '_' . $file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            $image_path = $target_file;
        }
    }

    // 检查 item_code 是否重复
    $check_stmt = $conn->prepare("SELECT item_id FROM wmsitem WHERE item_code = ?");
    $check_stmt->bind_param("s", $item_code);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $toastMessage = "item_exists";
    } else {
        // 加入 session
        $_SESSION['check_list'] = $_SESSION['check_list'] ?? [];
        $_SESSION['check_list'][] = [
            'item_name' => $item_name,
            'item_code' => $item_code,
            'quantity' => $quantity,
            'unit_price' => $unit_price,
            'category_id' => $category_id,
            'category' => $category,
            'image_path' => $image_path
        ];

        header("Location: check_list.php");
        exit();
    }

    $check_stmt->close();
}

// 获取最近 10 项库存数据
$items_sql = "SELECT item_name, quantity, item_code, note, image_path, unit_price FROM wmsitem ORDER BY item_id DESC LIMIT 10";
$items_result = mysqli_query($conn, $items_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Order Stock</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(-45deg, #fdfbfb, #ebedee, #e0d9f5, #e6f0ff);
      background-size: 400% 400%;
      animation: gradientFlow 15s ease infinite;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    @keyframes gradientFlow {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .container {
      display: flex;
      background: rgba(255, 255, 255, 0.95);
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
      width: 90%;
      max-width: 1000px;
      gap: 30px;
    }

    .order-container, .product-list {
      flex: 1;
    }

    h2 {
      margin-bottom: 25px;
      color: #333;
    }

    input, textarea, select {
      width: 100%;
      padding: 12px;
      margin-bottom: 18px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      transition: 0.3s ease;
    }

    input:focus, textarea:focus, select:focus {
      border-color: #007bff;
      box-shadow: 0 0 12px rgba(0, 123, 255, 0.4);
      outline: none;
    }

    input[type="file"] {
      padding: 0;
    }

    button {
      width: 100%;
      padding: 12px;
      background: #8a76c4;
      border: none;
      color: white;
      font-weight: bold;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s ease;
    }

    button:hover {
      background: #6f5aa6;
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(138, 118, 196, 0.5);
    }

    .product-list {
      background: #f8f8f8;
      padding: 20px;
      border-radius: 12px;
      overflow-y: auto;
      max-height: 500px;
    }

    .product-item {
      display: flex;
      align-items: center;
      background: white;
      padding: 10px 15px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 12px;
    }

    .product-item img {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 10px;
      margin-right: 15px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .product-item-details h4 {
      margin: 0 0 5px;
      color: #333;
    }

    .product-item-details p {
      margin: 0;
      font-size: 14px;
      color: #666;
    }

    .button-group {
      display: flex;
      gap: 15px;
      margin-top: 15px;
      flex-wrap: wrap;
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
      background-color: #ff4d4f;
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
    <div class="order-container">
      <h2>Order Stock</h2>
      <form action="stock_order.php" method="POST" enctype="multipart/form-data">

        <select name="category_id" required>
          <option value="" disabled selected>Select Category</option>
          <?php while($cat = mysqli_fetch_assoc($category_result)): ?>
            <option value="<?= $cat['category_id'] ?>">
              <?= htmlspecialchars($cat['category']) ?>
            </option>
          <?php endwhile; ?>
        </select>

        <input type="text" name="item_name" placeholder="Item Name" required>
        <input type="number" name="quantity" placeholder="Quantity" min="1" required>
        <input type="text" name="item_code" placeholder="Item Code (Must be unique)" required>
        <input type="number" name="unit_price" placeholder="Unit Price (e.g. 10.50)" min="0" step="0.01" required>
        <textarea name="note" placeholder="Additional Notes (optional)" rows="3"></textarea>
        <input type="file" name="image" accept="image/jpeg, image/png">

        <div class="button-group">
          <button type="button" onclick="location.href='add_category.php'">Add Category</button>
          <button type="submit">Add Item</button>
          <button type="button" onclick="location.href='stock_manage.php'">Go back</button>
        </div>
      </form>
    </div>

    <div class="product-list">
      <h2>Available Stocks</h2>
      <?php if (mysqli_num_rows($items_result) > 0): ?>
        <?php while($item = mysqli_fetch_assoc($items_result)): ?>
          <div class="product-item">
            <img src="<?= htmlspecialchars($item['image_path'] ?: 'https://via.placeholder.com/70') ?>" alt="Item">
            <div class="product-item-details">
              <h4><?= htmlspecialchars($item['item_name']) ?> (<?= htmlspecialchars($item['item_code']) ?>)</h4>
              <p>Quantity: <?= htmlspecialchars($item['quantity']) ?></p>
              <p>Unit Price: RM <?= number_format($item['unit_price'], 2) ?></p>
              <?php if (!empty($item['note'])): ?>
                <p>Note: <?= htmlspecialchars($item['note']) ?></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No stock available yet.</p>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function showToast(message, isError = false) {
      const container = document.getElementById("toastContainer");
      const toast = document.createElement("div");
      toast.className = "toast" + (isError ? " error" : "");
      toast.textContent = message;
      container.appendChild(toast);
      setTimeout(() => toast.remove(), 5000);
    }

    <?php if ($toastMessage === "item_exists"): ?>
      showToast("⚠️ This item code already exists", true);
    <?php endif; ?>
  </script>
</body>
</html>
