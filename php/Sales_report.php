<?php
include_once('db.php');

// 获取用户选择的年份（默认为当前年）
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// 获取年份列表
$years_sql = "SELECT DISTINCT YEAR(sale_date) AS year FROM wmssales ORDER BY year DESC";
$years_result = mysqli_query($conn, $years_sql);

// 获取销售报告（INNER JOIN）
$sales_sql = "
  SELECT 
    DATE_FORMAT(s.sale_date, '%Y-%m') AS month,
    SUM(s.quantity) AS total_quantity,
    SUM(s.quantity * i.unit_price) AS total_sales
  FROM wmssales s
  INNER JOIN wmsitem i ON s.item_id = i.item_id
  WHERE YEAR(s.sale_date) = ?
  GROUP BY month
  ORDER BY month ASC
";

$stmt = mysqli_prepare($conn, $sales_sql);
mysqli_stmt_bind_param($stmt, "i", $selected_year);
mysqli_stmt_execute($stmt);
$sales_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sales Report</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #e0eafc, #cfdef3);
      min-height: 100vh;
      margin: 0;
      padding: 60px 20px;
      display: flex;
      justify-content: center;
      align-items: flex-start;
    }

    .report-container {
      max-width: 900px;
      background: #fff;
      border-radius: 16px;
      padding: 50px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
      width: 100%;
    }

    h2 {
      text-align: center;
      font-size: 32px;
      color: #2c3e50;
      margin-bottom: 30px;
    }

    .year-select {
      text-align: center;
      margin-bottom: 20px;
    }

    select {
      padding: 12px 20px;
      font-size: 16px;
      border-radius: 10px;
      border: 1px solid #ccc;
      background: #f9f9f9;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
    }

    select:hover {
      border-color: #3498db;
      box-shadow: 0 3px 10px rgba(52, 152, 219, 0.2);
    }

    .back-button {
      margin-top: 20px;
      text-align: center;
    }

    .back-button button {
      padding: 12px 24px;
      font-size: 16px;
      color: white;
      background: linear-gradient(to right, #4facfe, #00f2fe);
      border-radius: 30px;
      border: none;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(0, 191, 255, 0.3);
      transition: transform 0.2s ease, box-shadow 0.3s ease;
    }

    .back-button button:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 191, 255, 0.4);
    }

    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 12px;
    }

    th, td {
      padding: 16px 20px;
      text-align: left;
    }

    th {
      background: #f0f4f8;
      color: #34495e;
      font-weight: 600;
      border-bottom: 2px solid #dcdfe6;
    }

    tbody tr {
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      transition: all 0.2s ease;
    }

    tbody tr:hover {
      background: #f5f9ff;
      transform: scale(1.01);
    }

    td {
      color: #333;
    }

    .money {
      color: #2e86de;
      font-weight: 600;
    }

    .quantity {
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="report-container">
    <h2>Monthly Sales Report</h2>

    <div class="year-select">
      <form method="get">
        <label for="year">Select Year:</label>
        <select name="year" id="year" onchange="this.form.submit()">
          <?php while ($year_row = mysqli_fetch_assoc($years_result)): ?>
            <option value="<?= $year_row['year'] ?>" <?= $year_row['year'] == $selected_year ? 'selected' : '' ?>>
              <?= $year_row['year'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </form>
    </div>

    <table>
      <thead>
        <tr>
          <th>Month</th>
          <th>Total Quantity</th>
          <th>Total Sales (RM)</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($sales_result)): ?>
          <tr>
            <td><?= $row['month'] ?></td>
            <td class="quantity"><?= $row['total_quantity'] ?></td>
            <td class="money">RM <?= number_format($row['total_sales'], 2) ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <div class="back-button">
      <button onclick="location.href='stock_manage.php'">Go back</button>
    </div>
  </div>
</body>
</html>
