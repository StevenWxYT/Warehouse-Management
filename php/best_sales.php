<?php
include_once('db.php');

// 获取选中的月份（默认当前月）
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// 获取月份选项（存在出库数据的月份）
$month_sql = "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') AS month FROM wmsstock_out ORDER BY month DESC";
$month_result = mysqli_query($conn, $month_sql);

// 查询该月 Top 10 出库数据（按数量排序）
$sql = "
  SELECT 
    i.item_name, 
    i.unit_price,
    SUM(s.quantity) as total_sold
  FROM wmsstock_out s
  INNER JOIN wmsitem i ON s.item_id = i.item_id
  WHERE DATE_FORMAT(s.date, '%Y-%m') = ?
  GROUP BY s.item_id
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
      background: linear-gradient(-45deg, #fdfbfb, #ebedee, #e0d9f5, #e6f0ff);
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

    select {
      padding: 10px 16px;
      border-radius: 8px;
      font-size: 16px;
      border: 1px solid #ccc;
      transition: box-shadow 0.3s ease;
    }

    select:hover {
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
      grid-template-columns: 150px 1fr;
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

    .buttons {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 15px;
      margin-top: 40px;
    }

    .buttons button, .buttons a {
      background: linear-gradient(135deg, #8a76c4, #a18cd1);
      border: none;
      padding: 12px 24px;
      border-radius: 10px;
      color: white;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      font-size: 15px;
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .buttons button:hover, .buttons a:hover {
      background: linear-gradient(135deg, #715abf, #9479d1);
      transform: scale(1.05);
      box-shadow: 0 8px 22px rgba(0, 0, 0, 0.15);
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

      .buttons {
        flex-direction: column;
        align-items: center;
      }
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>Top 10 Best Stock Out</h1>

    <div class="month-select">
      <form method="GET">
        <label for="month">Choose month:</label>
        <select name="month" id="month" onchange="this.form.submit()">
          <?php while ($row = mysqli_fetch_assoc($month_result)): ?>
            <option value="<?= $row['month'] ?>" <?= $row['month'] === $selected_month ? 'selected' : '' ?>>
              <?= date("F Y", strtotime($row['month'])) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </form>
    </div>

    <div class="top10-sales">
      <?php
      $rank = 1;
      while ($row = $result->fetch_assoc()):
      ?>
        <div class="sale-item">
          <label>Item Name:</label>
          <input type="text" value="<?= htmlspecialchars($row['item_name']) ?>" readonly>

          <label>Total Quantity:</label>
          <input type="text" value="<?= $row['total_sold'] ?>" readonly>

          <label>Unit Price (RM):</label>
          <input type="text" value="<?= number_format($row['unit_price'], 2) ?>" readonly>
        </div>
      <?php $rank++; endwhile; ?>

      <?php if ($rank === 1): ?>
        <p>No stock out data found for this month.</p>
      <?php endif; ?>
    </div>

    <div class="buttons">
      <a href="top10_excel.php?month=<?= $selected_month ?>">Export to Excel</a>
      <a href="top10_pdf.php?month=<?= $selected_month ?>" target="_blank">Export to PDF</a>
      <a href="stock_manage.php">Back to Stock Manage</a>
    </div>
  </div>

  <script>
    lucide.createIcons();
  </script>
</body>
</html>
