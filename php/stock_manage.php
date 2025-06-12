<?php
include_once('db.php');

$items_sql = "SELECT * FROM `wmsitem`";
$query = mysqli_query($conn, $items_sql);

$category_sql = "SELECT * FROM `wmscategory`";
$category_sql = mysqli_query($conn, $category_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manage Stock</title>
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
      height: 100vh;
      display: flex;
      background: linear-gradient(-45deg, #ff9a9e, #fad0c4, #fbc2eb, #a18cd1);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
    }

    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .sidebar {
      background-color: #ffffffcc;
      backdrop-filter: blur(10px);
      width: 240px;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 20px;
      border-right: 1px solid #ddd;
      transition: all 0.3s ease;
      position: relative;
    }

    .sidebar.collapsed {
      width: 60px;
      padding: 20px 10px;
    }

    .sidebar button {
      display: flex;
      align-items: center;
      gap: 12px;
      background: #f8f8f8;
      border: none;
      cursor: pointer;
      font-size: 15px;
      padding: 12px 16px;
      border-radius: 12px;
      color: #333;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .sidebar button:hover {
      background-color: #e4e4ff;
      transform: translateX(4px);
    }

    .sidebar.collapsed button span {
      display: none;
    }

    .sidebar-toggle {
      position: absolute;
      top: 10px;
      right: -16px;
      background-color: #6c63ff;
      border: none;
      color: white;
      border-radius: 50%;
      width: 32px;
      height: 32px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 0 8px rgba(0,0,0,0.1);
      z-index: 10;
    }

    .auth-buttons {
      position: absolute;
      bottom: 20px;
      left: 20px;
      right: 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .auth-buttons button {
      display: flex;
      align-items: center;
      gap: 12px;
      background: linear-gradient(135deg, #a18cd1, #fbc2eb);
      border: none;
      cursor: pointer;
      font-size: 15px;
      padding: 12px 16px;
      border-radius: 12px;
      color: white;
      font-weight: 600;
      box-shadow: 0 6px 16px rgba(0,0,0,0.08);
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .auth-buttons button:hover {
      background: linear-gradient(135deg, #8e7be5, #f4aee3);
      transform: translateY(-2px);
    }

    .sidebar.collapsed .auth-buttons span {
      display: none;
    }

    .sidebar.collapsed .auth-buttons button {
      justify-content: center;
    }

    .container {
      flex: 1;
      padding: 40px;
      overflow-y: auto;
    }

    .stock-container {
      max-width: 1200px;
      margin: auto;
      background-color: #ffffffcc;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      backdrop-filter: blur(8px);
    }

    h2 {
      text-align: center;
      font-size: 32px;
      color: #333;
      margin-bottom: 30px;
    }

    .controls {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-start;
      align-items: center;
      gap: 15px;
      margin-bottom: 30px;
    }

    .controls input,
    .controls select {
      padding: 12px 16px;
      border-radius: 10px;
      border: 1px solid #ccc;
      font-size: 15px;
      width: 100%;
      max-width: 300px;
      transition: box-shadow 0.3s ease;
    }

    .controls input:hover,
    .controls select:hover {
      box-shadow: 0 0 8px #a18cd1;
    }

    .add-button {
      padding: 12px 20px;
      background-color: #6c63ff;
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .add-button:hover {
      background-color: #574fd6;
      transform: scale(1.05);
    }

    .stock-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 25px;
    }

    .stock-card {
      background: #f9f9f9;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .stock-card:hover {
      transform: translateY(-6px);
    }

    .stock-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    .stock-info {
      padding: 15px 20px;
    }

    .stock-info h3 {
      margin: 0 0 10px;
      color: #333;
      font-size: 20px;
    }

    .stock-info p {
      margin: 5px 0;
      font-size: 14px;
      color: #555;
    }

    .hidden {
      display: none !important;
    }

    .sidebar i {
      width: 20px;
      height: 20px;
    }
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar">
    <button class="sidebar-toggle" onclick="toggleSidebar()">
      <i data-lucide="chevron-left"></i>
    </button>
    <button onclick="window.location.href='stock_quantity.php'">
      <i data-lucide="package"></i><span>View Stock Quantity</span>
    </button>
    <button onclick="window.location.href='order_history.php'">
      <i data-lucide="history"></i><span>View Order History</span>
    </button>
    <button onclick="window.location.href='stock_order.php'">
      <i data-lucide="shopping-cart"></i><span>Order Stocks</span>
    </button>

    <!-- 认证按钮 -->
    <div class="auth-buttons">
      <button onclick="window.location.href='login.php'">
        <i data-lucide="log-in"></i><span>Login</span>
      </button>
      <button onclick="window.location.href='register.php'">
        <i data-lucide="user-plus"></i><span>Register</span>
      </button>
      <button onclick="window.location.href='logout.php'">
        <i data-lucide="log-out"></i><span>Logout</span>
      </button>
    </div>
  </div>

  <div class="container">
    <div class="stock-container">
      <h2>Manage Stock</h2>
      <div class="controls">
        <button class="add-button" onclick="location.href='stock_order.php'">Add Item</button>
        <select id="categoryFilter">
          <option value="all">All Categories</option>
          <?php while($row = mysqli_fetch_assoc($category_sql)): ?>
            <option value="<?=htmlspecialchars($row['category'])?>"><?=htmlspecialchars($row['category'])?></option>
          <?php endwhile; ?>
        </select>
        <input type="text" id="searchInput" placeholder="Search by name or code...">
      </div>

      <div class="stock-grid" id="stockGrid">
        <?php while ($item = mysqli_fetch_assoc($query)): ?>
          <div class="stock-card" data-category="<?=htmlspecialchars($item['category'])?>">
            <img src="<?=htmlspecialchars($item['category'])?>" alt="<?=htmlspecialchars($item['item_name'])?>">
            <div class="stock-info">
              <h3><?=htmlspecialchars($item['item_name'])?></h3>
              <p><strong>Quantity:</strong> <?=htmlspecialchars($item['quantity'])?></p>
              <p><strong>Item Code:</strong> <?=htmlspecialchars($item['item_code'])?></p>
              <p><strong>Time:</strong> <?=htmlspecialchars($item['time'])?></p>
              <p><strong>Date:</strong> <?=htmlspecialchars($item['date'])?></p>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

  <script>
    // 搜索与分类过滤
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const cards = document.querySelectorAll('.stock-card');

    function filterStock() {
      const keyword = searchInput.value.toLowerCase();
      const category = categoryFilter.value;

      cards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const code = card.querySelector('p').textContent.toLowerCase();
        const cardCategory = card.dataset.category;

        const matchesSearch = title.includes(keyword) || code.includes(keyword);
        const matchesCategory = category === "all" || cardCategory === category;

        card.classList.toggle('hidden', !(matchesSearch && matchesCategory));
      });
    }

    searchInput.addEventListener('input', filterStock);
    categoryFilter.addEventListener('change', filterStock);

    // 侧边栏折叠功能
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const icon = sidebar.querySelector('.sidebar-toggle i');
      sidebar.classList.toggle('collapsed');
      icon.setAttribute("data-lucide", sidebar.classList.contains("collapsed") ? "chevron-right" : "chevron-left");
      lucide.createIcons();
    }

    // 初始化 lucide 图标
    lucide.createIcons();
  </script>
</body>
</html>
