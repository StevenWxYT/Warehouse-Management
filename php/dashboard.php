<?php
include("function.php");

$db = new DBConn();
$user = new DBFunc($db->conn);

// Redirect if not logged in
// if (empty($_SESSION['username'])) {
//     header('Location: index.php');
//     exit();
// }

// Fetch dashboard data
$topSelling = $user->getTop10BestSelling();
// $lowStock = $user->getLowStockItems(5);

// Assuming $topSelling is already fetched from the DB
$topSelling = $user->getTop10BestSelling();

if (!empty($topSelling)) {
    echo '<table border="1" cellspacing="0" cellpadding="8">';
    echo '<tr>';
    echo '<th>Image</th>';
    echo '<th>Name</th>';
    echo '<th>SKU</th>';
    echo '<th>Total Sold</th>';
    echo '</tr>';

    foreach ($topSelling as $item) {
        echo '<tr>';
        echo '<td><img src="' . htmlspecialchars($item['image']) . '" width="50" /></td>';
        echo '<td>' . htmlspecialchars($item['name']) . '</td>';
        echo '<td>' . htmlspecialchars($item['sku']) . '</td>';
        echo '<td>' . (int)$item['total_sold'] . '</td>';
        echo '</tr>';
    }

    echo '</table>';
} else {
    echo '<p>No sales data available.</p>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<style>
    /* 🌈 背景渐变和流动效果 */
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      font-family: Arial, sans-serif;
      background: linear-gradient(120deg, #a1c4fd, #c2e9fb, #d4fc79, #96e6a1);
      background-size: 400% 400%;
      animation: gradientFlow 18s ease infinite;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* 🎞 背景渐变动画 */
    @keyframes gradientFlow {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* 📦 表单容器 */
    form {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      width: 300px;
      text-align: center;
      animation: floaty 6s ease-in-out infinite;
    }

    /* ☁ 浮动动画效果（可选） */
    @keyframes floaty {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }

    /* ✨ 输入框样式 */
    /* input {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      transition: all 0.3s ease;
    } */

    /* input:focus {
      border-color: #4CAF50;
      box-shadow: 0 0 12px rgba(76, 175, 80, 0.5);
      outline: none;
    } */

    /* 🔘 按钮样式 */
    button {
      width: 100%;
      padding: 12px;
      background: #007bff;
      border: none;
      color: white;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      border-radius: 8px;
      transition: all 0.3s ease;
      box-shadow: 0 0 0 transparent;
    }

    /* ✨ 按钮悬停效果 */
    button:hover {
      background: #0056b3;
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(0, 123, 255, 0.6);
    }

    a {
      display: block;
      margin-top: 15px;
      font-size: 14px;
      color: #007bff;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
<body>
    <main class="dashboard">
        <button onclick="window.location.href='stock_quantity.php'" id="bt-1">View<br>Stock<br>Quantity</button><br>
        <button onclick="window.location.href='order_history.php'" id="bt-2">View<br>Order<br>History</button><br>
        <button onclick="window.location.href='stock_order.php'" id="bt-3">Order<br>Stocks</button><br>
        <button onclick="window.location.href='stock_manage.php'" id="bt-4">Manage<br>Stocks</button>
    </main>
</body>
</html>