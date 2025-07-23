<?php
include_once('db.php');

// 获取选中的月份（默认当前月）
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// 获取所有月份
$month_sql = "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') AS month FROM wmsstock_out ORDER BY month DESC";
$month_result = mysqli_query($conn, $month_sql);

// 查询该月 Top 10 出库数据
$sql = "
  SELECT 
    i.item_name, 
    i.unit_price,
    SUM(s.quantity) AS total_sold
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

// 收集数据 + 计算总销额
$chart_data = [];
$total_sales = 0;
while ($row = $result->fetch_assoc()) {
  $row['total_sales'] = $row['unit_price'] * $row['total_sold'];
  $total_sales += $row['total_sales'];
  $chart_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Top 10 Best Stock Out</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      font-family: 'Inter', sans-serif;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(-45deg, #fdfbfb, #ebedee, #e0d9f5, #e6f0ff);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
      padding: 40px 20px;
      display: flex;
      justify-content: center;
    }

    @keyframes gradientBG {
      0% {
        background-position: 0% 50%;
      }
      50% {
        background-position: 100% 50%;
      }
      100% {
        background-position: 0% 50%;
      }
    }

    .container {
      background: white;
      padding: 40px;
      border-radius: 16px;
      max-width: 1000px;
      width: 100%;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    h1 {
      text-align: center;
      font-size: 32px;
      margin-bottom: 20px;
      color: #333;
    }

    .month-select {
      text-align: center;
      margin-bottom: 30px;
    }

    select {
      padding: 10px 16px;
      font-size: 16px;
      border-radius: 10px;
      border: 1px solid #ccc;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    th, td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      text-align: center;
    }

    th {
      background-color: #eae9f5;
      color: #333;
    }

    td {
      background-color: #fdfdfd;
    }

    .buttons {
      text-align: center;
      margin-top: 20px;
    }

    .buttons a {
      margin: 0 10px;
      padding: 12px 20px;
      text-decoration: none;
      background: #8a76c4;
      color: white;
      border-radius: 8px;
      font-weight: 600;
      transition: background 0.3s ease;
    }

    .buttons a:hover {
      background: #715abf;
    }

    @media print {
      .month-select, .buttons, h1 {
        display: none;
      }

      .container {
        box-shadow: none;
        padding: 0;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Top 10 Stock Out</h1>

    <!-- 月份选择器 -->
    <div class="month-select">
      <form method="GET">
        <label for="month">Choose month: </label>
        <select name="month" id="month" onchange="this.form.submit()">
          <?php mysqli_data_seek($month_result, 0); ?>
          <?php while ($row = mysqli_fetch_assoc($month_result)): ?>
            <option value="<?= $row['month'] ?>" <?= $row['month'] === $selected_month ? 'selected' : '' ?>>
              <?= date("F Y", strtotime($row['month'])) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </form>
    </div>

    <!-- 表格 -->
    <table>
      <thead>
        <tr>
          <th>No.</th>
          <th>Item Name</th>
          <th>Total Quantity</th>
          <th>Unit Price (RM)</th>
          <th>Total Sales (RM)</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($chart_data) > 0): ?>
          <?php foreach ($chart_data as $index => $item): ?>
            <tr>
              <td><?= $index + 1 ?></td>
              <td><?= htmlspecialchars($item['item_name']) ?></td>
              <td><?= $item['total_sold'] ?></td>
              <td><?= number_format($item['unit_price'], 2) ?></td>
              <td><?= number_format($item['total_sales'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
          <!-- 总销额行 -->
          <tr style="font-weight: bold; background-color: #f4f1fa;">
            <td colspan="4" style="text-align: right;">Total Sales (RM):</td>
            <td><?= number_format($total_sales, 2) ?></td>
          </tr>
        <?php else: ?>
          <tr>
            <td colspan="5">No data available for this month.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- 导出按钮 -->
    <div class="buttons">
      <a href="top10_excel.php?month=<?= $selected_month ?>">Export to Excel</a>
      <a href="top10_pdf.php?month=<?= $selected_month ?>" target="_blank">Export to PDF</a>
      <a href="stock_manage.php">Back to Stock Manage</a>
    </div>
  </div>
</body>
</html>
