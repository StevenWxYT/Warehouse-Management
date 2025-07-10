<?php
session_start();
include_once('db.php');

$message = '';
$toastType = '';

// 获取分类
$category_sql = "SELECT category_id, category FROM wmscategory";
$category_result = mysqli_query($conn, $category_sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['item_code'])) {
        $item_code = $_POST['item_code'];
        $stock_out_qty = 1;

        $query = "SELECT item_id, item_name, quantity, unit_price, image_path FROM wmsitem WHERE item_code = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $item_code);
        $stmt->execute();
        $stmt->bind_result($item_id, $item_name, $current_qty, $unit_price, $image_path);
        $stmt->fetch();
        $stmt->close();

        if ($item_id && $current_qty >= $stock_out_qty) {
            $_SESSION['check_list_out'] = $_SESSION['check_list_out'] ?? [];

            $found = false;
            foreach ($_SESSION['check_list_out'] as &$entry) {
                if ($entry['item_code'] === $item_code) {
                    $entry['quantity'] += 1;
                    $found = true;
                    break;
                }
            }
            unset($entry);

            if (!$found) {
                $_SESSION['check_list_out'][] = [
                    'item_id' => $item_id,
                    'item_code' => $item_code,
                    'item_name' => $item_name,
                    'quantity' => 1,
                    'unit_price' => $unit_price,
                    'image_path' => $image_path
                ];
            }

            $toastType = 'success';
            $message = "✅ $item_name 已添加至出库清单。";
        } else {
            $toastType = 'error';
            $message = "❌ 物品不存在或库存不足。";
        }
    }

    // 如果按下 Add Item 按钮，则跳转至出库确认页
    if (isset($_POST['go_to_checklist'])) {
        header("Location: check_list_out.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Auto Stock Out</title>
  <style>
body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
    }

    .wrapper {
      display: flex;
      width: 90%;
      max-width: 1200px;
      height: 90vh;
      background-color: #fff;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .left, .right {
      flex: 1;
      padding: 30px;
      box-sizing: border-box;
      overflow-y: auto;
    }

    .left {
      background: #f9f9fb;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .right {
      background-color: #fff;
      border-left: 1px solid #eee;
      position: relative;
    }

    h2 {
      font-size: 22px;
      margin-bottom: 20px;
      color: #333;
    }

    select, input[type="text"] {
      width: 100%;
      padding: 14px 16px;
      margin-bottom: 20px;
      border: 1.8px solid #ccc;
      border-radius: 12px;
      font-size: 16px;
      background-color: #fefefe;
    }

    input[type="text"]:focus {
      border-color: #7e57c2;
      box-shadow: 0 0 0 3px rgba(126, 87, 194, 0.2);
      background-color: #fff;
      outline: none;
    }

    .back-btn {
      margin-top: 20px;
      padding: 10px 16px;
      background-color: #8a76c4;
      color: white;
      border-radius: 8px;
      font-size: 14px;
      text-decoration: none;
      transition: background 0.3s ease;
    }

    .back-btn:hover {
      background-color: #765bbd;
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

    .item-card {
      background-color: white;
      padding: 16px;
      margin: 12px 0;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      transition: box-shadow 0.2s ease;
      display: flex;
      gap: 16px;
    }

    .item-card img {
      width: 80px;
      height: 80px;
      border-radius: 10px;
      object-fit: cover;
    }

    .item-details {
      flex: 1;
    }

    .item-details h4 {
      margin: 0 0 6px;
      font-size: 17px;
      color: #444;
    }

    .item-details p {
      margin: 2px 0;
      font-size: 14px;
      color: #555;
    }

    .add-btn-container {
      position: absolute;
      bottom: 20px;
      right: 30px;
    }

    .add-btn {
      background-color: #8a76c4;
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      transition: background 0.3s ease;
    }

    .add-btn:hover {
      background-color: #8a76c4;
    } 
  </style>
</head>
<body>

<div class="toast-container" id="toastContainer">
  <?php if ($message): ?>
    <div class="toast <?= $toastType === 'error' ? 'error' : '' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>
</div>

<div class="wrapper">
  <div class="left">
    <h2>📦 Scan to Stock Out</h2>
    <form method="POST" id="scanForm">
      <input type="text" name="item_code" placeholder="Scan item code..." autofocus autocomplete="off">
    </form>
    <a href="stock_manage.php" class="back-btn">Go Back</a>
  </div>

  <div class="right">
    <h2>📝 Current Check List</h2>
    <?php if (!empty($_SESSION['check_list_out'])): ?>
      <?php foreach ($_SESSION['check_list_out'] as $item): ?>
        <div class="item-card">
          <img src="<?= htmlspecialchars($item['image_path'] ?? 'https://via.placeholder.com/80') ?>" alt="Image">
          <div class="item-details">
            <h4><?= htmlspecialchars($item['item_name']) ?></h4>
            <p><strong>Code:</strong> <?= htmlspecialchars($item['item_code']) ?></p>
            <p><strong>Quantity:</strong> <?= $item['quantity'] ?></p>
            <p><strong>Unit Price:</strong> RM <?= number_format($item['unit_price'], 2) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="padding: 20px;">📭 No items scanned yet.</p>
    <?php endif; ?>

    <div class="add-btn-container">
      <form method="POST">
        <button type="submit" name="go_to_checklist" class="add-btn">Add Item</button>
      </form>
    </div>
  </div>
</div>

<audio id="successSound" src="success-beep.mp3" preload="auto"></audio>
<audio id="errorSound" src="error-buzz.mp3" preload="auto"></audio>

<script>
  <?php if ($toastType): ?>
    document.getElementById("<?= $toastType === 'error' ? 'errorSound' : 'successSound' ?>").play();
  <?php endif; ?>
</script>

</body>
</html>
