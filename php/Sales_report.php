<?php
include_once('db.php');

// 获取选中的月份（默认当前月）
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// 获取月份列表
$months_sql = "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') AS month FROM wmsstock_out ORDER BY month DESC";
$months_result = mysqli_query($conn, $months_sql);

// 查询该月出库数据
$sales_sql = "
  SELECT 
    DATE_FORMAT(s.date, '%M %Y') AS month,
    i.item_name,
    SUM(s.quantity) AS total_quantity,
    SUM(s.quantity * IFNULL(s.unit_price, 0)) AS total_sales
  FROM wmsstock_out s
  INNER JOIN wmsitem i ON s.item_id = i.item_id
  WHERE DATE_FORMAT(s.date, '%Y-%m') = ?
  GROUP BY s.item_id
  ORDER BY s.item_id
";

$stmt = mysqli_prepare($conn, $sales_sql);
if (!$stmt) {
    die("SQL prepare error: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "s", $selected_month);
mysqli_stmt_execute($stmt);
$sales_result = mysqli_stmt_get_result($stmt);
if (!$sales_result) {
    die("SQL execution error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Stock Out Report</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(to right, #fdfbfb, #ebedee, #e0d9f5, #e6f0ff);
      margin: 0;
      padding: 50px 20px;
      display: flex;
      justify-content: center;
    }

    .report-container {
      background: #ffffff;
      max-width: 1100px;
      width: 100%;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    h2 {
      text-align: center;
      font-size: 34px;
      color: #2c3e50;
      margin-bottom: 35px;
      font-weight: 700;
    }

    .year-select {
      display: flex;
      justify-content: center;
      margin-bottom: 30px;
    }

    .year-select label {
      font-size: 16px;
      margin-right: 10px;
      align-self: center;
      color: #333;
    }

    select {
      padding: 10px 20px;
      font-size: 16px;
      border-radius: 12px;
      border: 1px solid #ccc;
      background: #f9f9f9;
      transition: all 0.3s ease;
    }

    select:hover {
      border-color: #3498db;
      box-shadow: 0 3px 10px rgba(52, 152, 219, 0.2);
    }

    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 10px;
    }

    th {
      background-color: #ecf6ff;
      color: #34495e;
      font-weight: 600;
      padding: 16px 20px;
      text-align: left;
      border-bottom: 1px solid #ccc;
    }

    td {
      background-color: #ffffff;
      padding: 16px 20px;
      color: #333;
      border-radius: 10px;
      transition: background 0.2s ease;
    }

    tbody tr:hover td {
      background-color: #f0faff;
    }

    tbody tr:nth-child(even) td {
      background-color: #fafafa;
    }

    .money {
      color: #1e90ff;
      font-weight: 600;
    }

    .quantity {
      font-weight: 500;
    }

    .back-button {
      text-align: center;
      margin-top: 30px;
    }

    .back-button button {
      padding: 12px 30px;
      font-size: 16px;
      color: white;
      background: #8a76c4;
      border-radius: 30px;
      border: none;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(138, 118, 196, 0.3);
      transition: transform 0.2s ease, box-shadow 0.3s ease;
    }

    .back-button button:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(138, 118, 196, 0.4);
    }

    @media print {
      .year-select,
      .back-button,
      h2 {
        display: none;
      }

      body {
        background: white !important;
        padding: 0 !important;
      }

      .report-container {
        box-shadow: none !important;
        padding: 0 !important;
        width: 100% !important;
      }

      table {
        border-spacing: 0;
      }

      td, th {
        font-size: 14px;
        border: 1px solid #ccc;
      }
    }
  </style>
</head>
<body>
  <div class="report-container">
    <h2>Monthly Stock Out Report</h2>

    <div class="year-select">
      <form method="get">
        <label for="month">Select Month:</label>
        <select name="month" id="month" onchange="this.form.submit()">
          <?php while ($month_row = mysqli_fetch_assoc($months_result)): ?>
            <option value="<?= $month_row['month'] ?>" <?= $month_row['month'] == $selected_month ? 'selected' : '' ?>>
              <?= date('F Y', strtotime($month_row['month'] . '-01')) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </form>
    </div>

    <table>
      <thead>
        <tr>
          <th>Month</th>
          <th>Item Name</th>
          <th>Total Quantity</th>
          <th>Total Sales (RM)</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($sales_result)): ?>
          <tr>
            <td><?= $row['month'] ?></td>
            <td><?= $row['item_name'] ?></td>
            <td class="quantity"><?= $row['total_quantity'] ?></td>
            <td class="money">RM <?= number_format($row['total_sales'], 2) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <div class="back-button">
      <form method="get" action="excel.php" style="margin-bottom: 10px;">
        <input type="hidden" name="month" value="<?= $selected_month ?>">
        <button>Export to Excel</button>
      </form>
      <button onclick="window.open('pdf.php?month=<?= $selected_month ?>', '_blank')">Export to PDF</button><br><br>
      <button onclick="location.href='stock_manage.php'">Go back</button>
    </div>
  </div>
</body>
</html>
