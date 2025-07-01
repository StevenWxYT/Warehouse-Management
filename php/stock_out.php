<?php
include_once('db.php');
session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $stock_out_qty = intval($_POST['quantity']);

    // 获取当前库存
    $query = "SELECT quantity FROM wmsitem WHERE item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $item_id);
    $stmt->execute();
    $stmt->bind_result($current_qty);
    $stmt->fetch();
    $stmt->close();

    if ($stock_out_qty > 0 && $stock_out_qty <= $current_qty) {
        $new_qty = $current_qty - $stock_out_qty;

        // 更新库存
        $update = "UPDATE wmsitem SET quantity = ? WHERE item_id = ?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param('ii', $new_qty, $item_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $message = "✅ Stock out successful!";
        } else {
            $message = "❌ Failed to update stock.";
        }
        $stmt->close();
    } else {
        $message = "❌ Invalid quantity. Must be less than or equal to current stock.";
    }
}

// 获取商品列表
$items = mysqli_query($conn, "SELECT item_id, item_name, quantity FROM wmsitem");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Stock Out</title>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      padding: 20px;
      background-color: #f4f6f8;
    }
    h2 {
      color: #333;
    }
    .form-box {
      background-color: #fff;
      padding: 20px;
      border-radius: 12px;
      max-width: 400px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    label, select, input {
      display: block;
      margin-bottom: 15px;
      width: 100%;
    }
    button {
      padding: 10px 20px;
      background-color: #1e88e5;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }
    .message {
      margin-top: 20px;
      color: green;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <h2>📦 Stock Out</h2>
  <div class="form-box">
    <form method="POST">
      <label for="item_id">Select Item:</label>
      <select name="item_id" required>
        <?php while($row = mysqli_fetch_assoc($items)) { ?>
          <option value="<?= $row['item_id'] ?>">
            <?= $row['item_name'] ?> (Stock: <?= $row['quantity'] ?>)
          </option>
        <?php } ?>
      </select>

      <label for="quantity">Quantity to Stock Out:</label>
      <input type="number" name="quantity" min="1" required>

      <button type="submit">Stock Out</button>
    </form>

    <?php if ($message): ?>
      <div class="message"><?= $message ?></div>
    <?php endif; ?>
  </div>

</body>
</html>
