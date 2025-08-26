<?php
include_once('db.php');
session_start();

if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
     echo "<script>
        window.location.href = 'index.php';
    </script>";
    exit;
}

// --- 获取选择的月份 ---
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : $selected_month . '-01';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t', strtotime($selected_month . '-01'));

// --- 月份下拉框数据 ---
$month_sql = "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') AS month FROM wmsstock_out ORDER BY month DESC";
$month_result = mysqli_query($conn, $month_sql);

// --- Top 10 报表 ---
$top_sql = "
  SELECT 
    i.item_name, 
    i.unit_price,
    SUM(s.quantity) AS total_sold
  FROM wmsstock_out s
  INNER JOIN wmsitem i ON s.item_id = i.item_id
  WHERE s.date BETWEEN ? AND ?
  GROUP BY s.item_id
  ORDER BY total_sold DESC
  LIMIT 10
";
$top_stmt = $conn->prepare($top_sql);
$top_stmt->bind_param("ss", $start_date, $end_date);
$top_stmt->execute();
$top_result = $top_stmt->get_result();

$top_data = [];
$top_total_sales = 0;
while ($row = $top_result->fetch_assoc()) {
  $row['total_sales'] = $row['unit_price'] * $row['total_sold'];
  $top_total_sales += $row['total_sales'];
  $top_data[] = $row;
}

// --- 月度报表 ---
$sales_sql = "
  SELECT 
    DATE_FORMAT(s.date, '%M %Y') AS month,
    i.item_name,
    SUM(s.quantity) AS total_quantity,
    SUM(s.quantity * IFNULL(s.unit_price, 0)) AS total_sales
  FROM wmsstock_out s
  INNER JOIN wmsitem i ON s.item_id = i.item_id
  WHERE s.date BETWEEN ? AND ?
  GROUP BY s.item_id
  ORDER BY s.item_id
";
$sales_stmt = $conn->prepare($sales_sql);
$sales_stmt->bind_param("ss", $start_date, $end_date);
$sales_stmt->execute();
$sales_result = $sales_stmt->get_result();

$sales_data = [];
$sales_total = 0;
while ($row = $sales_result->fetch_assoc()) {
  $sales_data[] = $row;
  $sales_total += $row['total_sales'];
}

// --- 图表数据（所有月份总览） ---
$chart_sql = "
  SELECT 
    DATE_FORMAT(date, '%Y-%m') AS ym,
    SUM(quantity * IFNULL(unit_price, 0)) AS total_sales
  FROM wmsstock_out
  GROUP BY ym
  ORDER BY ym ASC
";
$chart_result = mysqli_query($conn, $chart_sql);

$chart_data = [];
while ($r = mysqli_fetch_assoc($chart_result)) {
  $chart_data[] = $r;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Combined Sales Report</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: Arial, sans-serif; background: #f0f4ff; padding: 40px; }
    .container { max-width: 1200px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #333; }
    form { text-align: center; margin-bottom: 20px; }
    form input, form select, form button { padding: 8px 12px; margin: 0 10px; border-radius: 6px; border: 1px solid #ccc; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
    th { background: #e0e7ff; }
    .section-title { margin-top: 40px; font-size: 20px; color: #555; border-bottom: 2px solid #ccc; padding-bottom: 8px; }
    .chart-container { display: flex; justify-content: space-around; flex-wrap: wrap; margin-top: 30px; }
    .chart-box { width: 45%; min-width: 300px; }
    .chart-box canvas {
      width: 100% !important;
      height: 400px !important;
    }
    .button-area { text-align: center; margin-top: 30px; }
    .button-area form, .button-area a { display: inline-block; margin: 0 10px; }
    .button-area button, .button-area a { padding: 10px 20px; background: #8a76c4; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; }
  </style>
</head>
<body>
<div class="container">
  <h2>Combined Sales Report</h2>

  <!-- 表格月份筛选 -->
  <form method="get">
    <label for="month">Select Month:</label>
    <select name="month" id="month" onchange="this.form.submit()">
      <?php mysqli_data_seek($month_result, 0); while ($m = mysqli_fetch_assoc($month_result)): ?>
        <option value="<?= $m['month'] ?>" <?= $m['month'] === $selected_month ? 'selected' : '' ?>>
          <?= date('F Y', strtotime($m['month'])) ?>
        </option>
      <?php endwhile; ?>
    </select>
    <label for="start_date">From:</label>
    <input type="date" name="start_date" value="<?= $start_date ?>">
    <label for="end_date">To:</label>
    <input type="date" name="end_date" value="<?= $end_date ?>">
    <button type="submit">Filter</button>
  </form>

  <!-- 图表月份筛选 -->
  <div style="text-align:center; margin-bottom:20px;">
    <label for="chart_month">View Chart:</label>
    <select id="chart_month" onchange="filterChart(this.value)">
      <option value="all">All Months</option>
      <?php foreach ($chart_data as $r): ?>
        <option value="<?= $r['ym'] ?>"><?= $r['ym'] ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="chart-container">
    <div class="chart-box">
      <canvas id="monthlyChart"></canvas>
    </div>
    <div class="chart-box">
      <canvas id="pieChart"></canvas>
    </div>
  </div>

  <div class="section-title">Top 10 Best Sales (<?= $start_date ?> ~ <?= $end_date ?>)</div>
  <table>
    <thead>
    <tr><th>No</th><th>Item Name</th><th>Total Quantity</th><th>Unit Price</th><th>Total Sales</th></tr>
    </thead>
    <tbody>
    <?php foreach ($top_data as $i => $row): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><?= $row['item_name'] ?></td>
        <td><?= $row['total_sold'] ?></td>
        <td>RM <?= number_format($row['unit_price'], 2) ?></td>
        <td>RM <?= number_format($row['total_sales'], 2) ?></td>
      </tr>
    <?php endforeach; ?>
    <tr style="font-weight:bold; background:#f4f4f8">
      <td colspan="4">Total</td>
      <td>RM <?= number_format($top_total_sales, 2) ?></td>
    </tr>
    </tbody>
  </table>

  <div class="button-area">
    <form action="top10_excel.php" method="get">
      <input type="hidden" name="start_date" value="<?= $start_date ?>">
      <input type="hidden" name="end_date" value="<?= $end_date ?>">
      <button type="submit">Export Top 10 to Excel</button>
    </form>
    <form action="top10_pdf.php" method="get" target="_blank">
      <input type="hidden" name="start_date" value="<?= $start_date ?>">
      <input type="hidden" name="end_date" value="<?= $end_date ?>">
      <button type="submit" style="background:#f39c12">Export Top 10 to PDF</button>
    </form>
  </div>

  <div class="section-title">Monthly Sales Report (<?= $start_date ?> ~ <?= $end_date ?>)</div>
  <table>
    <thead>
    <tr><th>No</th><th>Item Name</th><th>Total Quantity</th><th>Total Sales</th></tr>
    </thead>
    <tbody>
    <?php foreach ($sales_data as $i => $row): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><?= $row['item_name'] ?></td>
        <td><?= $row['total_quantity'] ?></td>
        <td>RM <?= number_format($row['total_sales'], 2) ?></td>
      </tr>
    <?php endforeach; ?>
    <tr style="font-weight:bold; background:#f4f4f8">
      <td colspan="3">Total</td>
      <td>RM <?= number_format($sales_total, 2) ?></td>
    </tr>
    </tbody>
  </table>

  <div class="button-area">
    <form action="excel.php" method="get">
      <input type="hidden" name="start_date" value="<?= $start_date ?>">
      <input type="hidden" name="end_date" value="<?= $end_date ?>">
      <button type="submit">Export Sales Report to Excel</button>
    </form>
    <form action="pdf.php" method="get" target="_blank">
      <input type="hidden" name="start_date" value="<?= $start_date ?>">
      <input type="hidden" name="end_date" value="<?= $end_date ?>">
      <button type="submit" style="background:#e67e22">Export Sales Report to PDF</button>
    </form>
    <a href="index.php">Go Back</a>
  </div>
</div>

<script>
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const pieCtx = document.getElementById('pieChart').getContext('2d');

const allLabels = [<?php foreach ($chart_data as $r) echo '"' . $r['ym'] . '",'; ?>];
const allData = [<?php foreach ($chart_data as $r) echo $r['total_sales'] . ','; ?>];

const barColors = [
  '#8a76c4', '#f39c12', '#2ecc71', '#e74c3c', '#9b59b6',
  '#1abc9c', '#34495e', '#3498db', '#fd79a8', '#00cec9',
  '#ff7675', '#55efc4', '#ffeaa7', '#fab1a0', '#6c5ce7',
  '#0984e3', '#d63031', '#e17055', '#00b894', '#636e72'
];

let monthlyChart = new Chart(monthlyCtx, {
  type: 'bar',
  data: {
    labels: allLabels,
    datasets: [{
      label: 'Monthly Sales (RM)',
      data: allData,
      backgroundColor: barColors
    }]
  },
  options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
});

new Chart(pieCtx, {
  type: 'pie',
  data: {
    labels: [<?php foreach ($top_data as $row) echo '"' . $row['item_name'] . '",'; ?>],
    datasets: [{
      data: [<?php foreach ($top_data as $row) echo $row['total_sales'] . ','; ?>],
      backgroundColor: ['#8a76c4','#f39c12','#2ecc71','#e74c3c','#9b59b6','#1abc9c','#34495e','#3498db','#fd79a8','#00cec9']
    }]
  },
  options: { responsive: true, maintainAspectRatio: false }
});

// 图表月份筛选
function filterChart(month) {
  if (month === "all") {
    monthlyChart.data.labels = allLabels;
    monthlyChart.data.datasets[0].data = allData;
  } else {
    let index = allLabels.indexOf(month);
    monthlyChart.data.labels = [allLabels[index]];
    monthlyChart.data.datasets[0].data = [allData[index]];
  }
  monthlyChart.update();
}
</script>
</body>
</html>
