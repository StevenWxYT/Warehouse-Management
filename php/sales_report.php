<?php
include_once('db.php');
session_start();

$top10_sql = "SELECT item_name, SUM(quantity) AS total_sold 
              FROM wmsorder 
              GROUP BY item_name 
              ORDER BY total_sold DESC 
              LIMIT 10";
$top10_result = mysqli_query($conn, $top10_sql);

$monthly_sql = "SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(quantity) AS total_quantity 
                FROM wmsorder 
                GROUP BY month 
                ORDER BY month ASC";
$monthly_result = mysqli_query($conn, $monthly_sql);

$months = [];
$quantities = [];

while ($row = mysqli_fetch_assoc($monthly_result)) {
    $months[] = $row['month'];
    $quantities[] = $row['total_quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sales Report</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f2f2f2;
      padding: 40px;
    }

    h2 {
      text-align: center;
      font-size: 28px;
      color: #333;
      margin-bottom: 40px;
    }

    .section {
      background: white;
      border-radius: 12px;
      padding: 30px;
      margin-bottom: 40px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      max-width: 900px;
      margin: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    table th, table td {
      text-align: left;
      padding: 12px;
      border-bottom: 1px solid #ddd;
    }

    table th {
      background: #f9f9f9;
    }

    canvas {
      max-width: 100%;
    }
  </style>
</head>
<body>

  <div class="section">
    <h2>Top 10 Best Sales Stock</h2>
    <table>
      <thead>
        <tr>
          <th>Item Name</th>
          <th>Total Sold</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($top10_result)): ?>
          <tr>
            <td><?= htmlspecialchars($row['item_name']) ?></td>
            <td><?= $row['total_sold'] ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <div class="section">
    <h2>Monthly Sales Report</h2>
    <canvas id="monthlyChart"></canvas>
  </div>

  <script>
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
          label: 'Total Sales',
          data: <?= json_encode($quantities) ?>,
          backgroundColor: '#6c63ff'
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    });
  </script>

</body>
</html>
