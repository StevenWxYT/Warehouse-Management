<?php
session_start();
include_once('db.php');

$check_list = $_SESSION['check_list'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm'])) {
        foreach ($check_list as $item) {
            $item_name = $item['item_name'];
            $item_code = $item['item_code'];
            $quantity = $item['quantity'];
            $note = '';
            $image_path = '';
            $date = date("Y-m-d");
            $time = date("H:i:s");

            // ✅ 获取分类 ID
            $category_name = $item['category_id'] ?? '';
            $category_id = 1; // fallback

            if (!empty($category_name)) {
                $cat_stmt = $conn->prepare("SELECT category_id FROM wmscategory WHERE category = ?");
                $cat_stmt->bind_param("s", $category);
                $cat_stmt->execute();
                $cat_result = $cat_stmt->get_result();
                if ($cat_row = $cat_result->fetch_assoc()) {
                    $category_id = $cat_row['category_id'];
                }
                $cat_stmt->close();
            }

            // 避免重复插入
            $check_stmt = $conn->prepare("SELECT item_id FROM wmsitem WHERE item_code = ?");
            $check_stmt->bind_param("s", $item_code);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO wmsitem (item_name, quantity, item_code, note, image_path, date, time, category_id)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sisssssi", $item_name, $quantity, $item_code, $note, $image_path, $date, $time, $category_id);
                $stmt->execute();
                $stmt->close();
            }

            $check_stmt->close();
        }

        // 清空 session
        unset($_SESSION['check_list']);
        header("Location: stock_manage.php");
        exit();

    } elseif (isset($_POST['return'])) {
        header("Location: stock_order.php");
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
      max-width: 900px;
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
    }

    th {
      background-color: #8a76c4;
      color: white;
      font-weight: 600;
    }

    tr:last-child td {
      border-bottom: none;
    }

    .no-data {
      text-align: center;
      font-size: 18px;
      color: #888;
      padding: 40px 0;
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

    .btn-container {
      text-align: center;
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
    }

    @media screen and (max-width: 600px) {
      th, td {
        font-size: 14px;
        padding: 10px;
      }

      .btn {
        width: 100%;
      }

      .btn-container {
        flex-direction: column;
        gap: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>🧾 Check List — Confirm New Items</h2>

    <?php if (!empty($check_list)): ?>
      <table>
        <thead>
          <tr>
            <th>Item Name</th>
            <th>Item Code</th>
            <th>Quantity</th>
            <th>Category</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($check_list as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['item_name']) ?></td>
              <td><?= htmlspecialchars($item['item_code']) ?></td>
              <td><?= htmlspecialchars($item['quantity']) ?></td>
              <td><?= htmlspecialchars($item['category'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <form method="post" class="btn-container">
        <button type="submit" name="return" class="btn">🔙 Return to Edit</button>
        <button type="submit" name="confirm" class="btn">✅ Confirm & Save</button>
      </form>
    <?php else: ?>
      <div class="no-data">
        No items to check.<br><br>
        <a href="stock_order.php" class="btn">⬅ Back to Stock Order</a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
