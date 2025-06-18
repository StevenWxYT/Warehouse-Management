<?php
include_once('db.php');

// 获取选中的月份
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// 获取该月 top 10 销售数据
$sql = "
  SELECT item_name, SUM(quantity_sold) as total_sold
  FROM sales
  WHERE DATE_FORMAT(sale_date, '%Y-%m') = ?
  GROUP BY item_name
  ORDER BY total_sold DESC
  LIMIT 10
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_month);
$stmt->execute();
$result = $stmt->get_result();

// 获取可用月份列表（下拉选单用）
$month_sql = "
  SELECT DISTINCT DATE_FORMAT(sale_date, '%Y-%m') as month
  FROM sales
  ORDER BY month DESC
";
$months_result = $conn->query($month_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Top 10 Best Sales</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f3f4f6;
      padding: 30px;
    }

    h1 {
      text-align: center;
      color: #333;
    }

    .month-select {
      text-align: center;
      margin-bottom: 20px;
    }

    select {
      padding: 8px 12px;
      font-size: 16px;
    }

    table {
      width: 60%;
      margin: 0 auto;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 12px 16px;
      border-bottom: 1px solid #eee;
      text-align: center;
    }

    th {
      background: #6c63ff;
      color: white;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    .back-button {
      display: block;
      margin: 20px auto;
      text-align: center;
      text-decoration: none;
      background: #6c63ff;
      color: white;
      padding: 10px 16px;
      border-radius: 6px;
      width: fit-content;
    }

    .back-button:hover {
      background: #574fd6;
    }
  </style>
</head>
<body>

<h1>Top 10 Best Sales Stock</h1>

<div class="month-select">
  <form method="GET">
    <label for="month">Choose month:</label>
    <select name="month" id="month" onchange="this.form.submit()">
      <?php while ($row = $months_result->fetch_assoc()): ?>
        <option value="<?= $row['month'] ?>" <?= $selected_month == $row['month'] ? 'selected' : '' ?>>
          <?= date('F Y', strtotime($row['month'])) ?>
        </option>
      <?php endwhile; ?>
    </select>
  </form>
</div>

<table>
  <thead>
    <tr>
      <th>Rank</th>
      <th>Item Name</th>
      <th>Quantity Sold</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $rank = 1;
    while ($row = $result->fetch_assoc()):
    ?>
      <tr>
        <td><?= $rank++ ?></td>
        <td><?= htmlspecialchars($row['item_name']) ?></td>
        <td><?= $row['total_sold'] ?></td>
      </tr>
    <?php endwhile; ?>
    <?php if ($rank === 1): ?>
      <tr><td colspan="3">No sales found for this month.</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<a href="dashboard.php" class="back-button">← Back to Dashboard</a>

</body>
</html>
