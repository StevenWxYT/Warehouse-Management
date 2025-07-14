<?php
include_once('db.php');

$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$sales_sql = "
  SELECT 
    DATE_FORMAT(s.date, '%M %Y') AS month,
    i.item_name,
    SUM(s.quantity) AS total_quantity,
    SUM(s.quantity * IFNULL(s.unit_price, 0)) AS total_sales
  FROM wmsstock_out s
  INNER JOIN wmsitem i ON s.item_id = i.item_id
  WHERE YEAR(s.date) = ?
  GROUP BY MONTH(s.date), s.item_id
  ORDER BY MONTH(s.date), s.item_id
";

$stmt = mysqli_prepare($conn, $sales_sql);
if (!$stmt) {
    die("SQL prepare error: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "i", $selected_year);
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
  <title>Print Stock Out Report</title>
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

    @media print {
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
  <script>
    window.onload = function () {
      window.print();
    }
  </script>
</head>
<body>
  <div class="report-container">
    <h2>Monthly Stock Out Report (<?= $selected_year ?>)</h2>
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
  </div>
</body>
</html>
