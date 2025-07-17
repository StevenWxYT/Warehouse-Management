<?php
include_once('db.php');

// 获取选中的月份（默认当前月）
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// 查询 Top 10 出库数据
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Top 10 Stock Out Report</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: white;
      padding: 40px;
      margin: 0;
    }

    h2 {
      text-align: center;
      font-size: 28px;
      color: #2c3e50;
      margin-bottom: 30px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      padding: 12px 16px;
      border: 1px solid #ccc;
      text-align: center;
    }

    th {
      background-color: #ecf6ff;
      font-weight: 600;
      color: #34495e;
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
      }

      h2 {
        font-size: 22px;
      }

      table {
        font-size: 14px;
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
  <h2>Top 10 Stock Out Report (<?= date('F Y', strtotime($selected_month)) ?>)</h2>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Item Name</th>
        <th>Total Quantity</th>
        <th>Unit Price (RM)</th>
        <th>Total Sales (RM)</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $no = 1;
      while ($row = $result->fetch_assoc()):
        $total_sales = $row['unit_price'] * $row['total_sold'];
      ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['item_name']) ?></td>
          <td class="quantity"><?= $row['total_sold'] ?></td>
          <td><?= number_format($row['unit_price'], 2) ?></td>
          <td class="money"><?= number_format($total_sales, 2) ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</body>
</html>
