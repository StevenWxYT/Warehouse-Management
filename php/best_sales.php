<?php
include_once('db.php');

// 获取选中的月份（默认当前月）
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// 查询该月 Top 10 销售数据
$sql = "
  SELECT item_id, SUM(quantity) as total_sold
  FROM wmssales
  WHERE DATE_FORMAT(sale_date, '%Y-%m') = ?
  GROUP BY item_id
  ORDER BY total_sold DESC
  LIMIT 10
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_month);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Top 10 Best Sales</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      min-height: 100vh;
      background: linear-gradient(-45deg, #a18cd1, #fbc2eb, #fad0c4, #ff9a9e);
      background-size: 400% 400%;
      animation: gradientMove 15s ease infinite;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }

    @keyframes gradientMove {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .card {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      max-width: 900px;
      width: 100%;
    }

    h1 {
      text-align: center;
      font-size: 32px;
      color: #333;
      margin-bottom: 30px;
    }

    .month-select {
      text-align: center;
      margin-bottom: 25px;
    }

    input[type="month"] {
      padding: 10px 16px;
      border-radius: 8px;
      font-size: 16px;
      border: 1px solid #ccc;
      transition: box-shadow 0.3s ease;
    }

    input[type="month"]:hover {
      box-shadow: 0 0 8px rgba(161, 140, 209, 0.5);
    }

    .top10-sales {
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin-top: 10px;
    }

    .sale-item {
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      display: grid;
      grid-template-columns: 120px 1fr;
      row-gap: 10px;
      column-gap: 20px;
      align-items: center;
    }

    .sale-item label {
      font-weight: 600;
      color: #333;
    }

    .sale-item input {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      background-color: #f9f9f9;
      color: #555;
    }

    .back-button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin: 30px auto 0;
      text-align: center;
      background: linear-gradient(135deg, #6c63ff, #a18cd1);
      color: white;
      padding: 14px 24px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 600;
      font-size: 16px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .back-button:hover {
      background: linear-gradient(135deg, #574fd6, #8e7be5);
      transform: scale(1.05);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    @media (max-width: 600px) {
      .card {
        padding: 20px;
      }

      h1 {
        font-size: 24px;
      }

      .sale-item {
        grid-template-columns: 1fr;
      }

      .sale-item label {
        margin-top: 8px;
      }
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>Top 10 Best Sales Stock</h1>

    <div class="month-select">
      <form method="GET">
        <label for="month">Choose month:</label>
        <input type="month" id="month" name="month" value="<?= $selected_month ?>" onchange="this.form.submit()" />
      </form>
    </div>

    <div class="top10-sales">
      <?php
      $rank = 1;
      while ($row = $result->fetch_assoc()):
      ?>
        <div class="sale-item">
          <label>Rank:</label>
          <input type="text" value="<?= $rank ?>" readonly>

          <label>Item Name:</label>
          <input type="text" value="<?= htmlspecialchars($row['item_name']) ?>" readonly>

          <label>Quantity Sold:</label>
          <input type="text" value="<?= $row['total_sold'] ?>" readonly>
        </div>
      <?php $rank++; endwhile; ?>

      <?php if ($rank === 1): ?>
        <p>No best sales found for this month.</p>
      <?php endif; ?>
    </div>

    <a href="stock_manage.php" class="back-button">
      <i data-lucide="arrow-left"></i> Back to Stock Manage
    </a>
  </div>

  <script>
    lucide.createIcons();
  </script>
</body>
</html>
