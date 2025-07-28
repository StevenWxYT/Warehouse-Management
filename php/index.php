<?php
include_once('db.php');
session_start();

$items_sql = "SELECT * FROM `wmsitem`";
$query = mysqli_query($conn, $items_sql);

// 获取分类并创建映射 category_id => category 名称
$category_sql = "SELECT * FROM `wmscategory`";
$category_result = mysqli_query($conn, $category_sql);
$category_map = [];
while ($row = mysqli_fetch_assoc($category_result)) {
    $category_map[$row['category_id']] = $row['category'];
}

$isLoggedIn = isset($_SESSION['username']);
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
      background: linear-gradient(-45deg, #fdfbfb, #ebedee, #e0d9f5, #e6f0ff);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
      padding-top: 60px;
    }

    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .top-nav {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background-color: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 30px;
      z-index: 9999;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .top-nav .nav-title {
      font-size: 20px;
      font-weight: 600;
      color: #333;
    }

    .top-nav .nav-links {
      display: flex;
      align-items: center;
    }

    .top-nav .nav-links a {
      margin-left: 20px;
      text-decoration: none;
      color: #555;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    .top-nav .nav-links a:hover {
      color: #8a76c4;
    }

    .user-flat-box {
      margin-left: 20px;
      padding: 6px 14px;
      background-color: #8a76c4;
      color: white;
      font-weight: 600;
      border-radius: 6px;
      cursor: pointer;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
      transition: background 0.3s ease;
    }

    .user-flat-box:hover {
      background-color: #6a5dbd;
    }

    .user-card {
      position: fixed;
      top: 70px;
      right: 20px;
      background: white;
      border-radius: 12px;
      padding: 20px;
      width: 260px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.2);
      z-index: 1000;
      display: none;
      animation: fadeSlideIn 0.3s ease forwards;
    }

    @keyframes fadeSlideIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .user-card h4 {
      margin: 0 0 10px;
      font-size: 18px;
      color: #333;
    }

    .user-card p {
      margin: 4px 0;
      font-size: 14px;
      color: #666;
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
      background-color: #ecf0f1;
      transform: translateX(4px);
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
      background: linear-gradient(135deg, #e0d9f5, #e6f0ff);
      border: none;
      cursor: pointer;
      font-size: 15px;
      padding: 12px 16px;
      border-radius: 12px;
      color: black;
      font-weight: 600;
      box-shadow: 0 6px 16px rgba(0,0,0,0.08);
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .auth-buttons button:hover {
      background: linear-gradient(135deg, #e0d9f5, #e6f0ff);
      transform: translateY(-2px);
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
      max-width: 300px;
      transition: box-shadow 0.3s ease;
    }

    .controls input:hover,
    .controls select:hover {
      box-shadow: 0 0 8px #a18cd1;
    }

    .add-button {
      padding: 12px 20px;
      background-color: #8a76c4;
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
      grid-template-columns: repeat(3, 1fr);
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
  height: 160px;
  object-fit: contain;
  border-radius: 12px;
  background-color: #fff;
  border: 1px solid #ddd;
  padding: 6px;
  box-shadow: inset 0 0 6px rgba(0,0,0,0.05);
  transition: transform 0.3s ease;
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

    .dropdown-section {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .dropdown-toggle {
      font-size: 22px;
      padding: 8px;
      background: #f8f8f8;
      border: none;
      border-radius: 8px;
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    .dropdown-content {
      display: flex;
      flex-direction: column;
      gap: 8px;
      overflow: hidden;
      max-height: 0;
      opacity: 0;
      transition: all 0.3s ease;
    }

    .dropdown-content.show {
      max-height: 200px;
      opacity: 1;
    }

    .toast-container {
      position: fixed;
      top: 80px;
      right: 20px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .toast {
      background-color: #ff4d4f;
      color: white;
      padding: 14px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      font-size: 14px;
      animation: fadeInOut 5s forwards;
      max-width: 320px;
      line-height: 1.4;
      white-space: pre-line;
    }

    @keyframes fadeInOut {
      0% { opacity: 0; transform: translateY(-10px); }
      10%, 90% { opacity: 1; transform: translateY(0); }
      100% { opacity: 0; transform: translateY(-10px); }
    }
    .image-modal {
  display: none;
  position: fixed;
  z-index: 99999;
  padding-top: 80px;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.85);
}

.image-modal .modal-content {
  margin: auto;
  display: block;
  max-width: 90%;
  max-height: 80vh;
  border-radius: 12px;
  box-shadow: 0 0 12px #000;
}

.image-modal .close {
  position: absolute;
  top: 30px;
  right: 40px;
  color: white;
  font-size: 40px;
  font-weight: bold;
  cursor: pointer;
  z-index: 100000;
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
      background: linear-gradient(135deg, #e0d9f5, #e6f0ff);
      border: none;
      cursor: pointer;
      font-size: 15px;
      padding: 12px 16px;
      border-radius: 12px;
      color: black;
      font-weight: 600;
      box-shadow: 0 6px 16px rgba(0,0,0,0.08);
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .auth-buttons button:hover {
      background: linear-gradient(135deg, #d6c6f7, #e0ebff);
      transform: translateY(-2px);
    }
  </style>
</head>
<body>
<nav class="top-nav">
  <div class="nav-title">📦 Warehouse System</div>
  <div class="nav-links">
    <a href="Sales_report.php">Dashboard</a>
    <a href="best_sales.php">Best seller</a>
    <?php if ($isLoggedIn): ?>
      <div class="user-flat-box" onclick="toggleUserCard()">
        <?= htmlspecialchars($_SESSION['username']) ?>
      </div>
    <?php endif; ?>
  </div>
</nav>

<?php if ($isLoggedIn): ?>
  <div class="user-card" id="userCard">
    <h4><?= htmlspecialchars($_SESSION['username']) ?></h4>
    <p>Email: <?= htmlspecialchars($_SESSION['email']) ?></p>
  </div>
<?php endif; ?>

<div class="toast-container" id="toastContainer"></div>

<div class="sidebar" id="sidebar">
  <div class="dropdown-section">
    <button class="dropdown-toggle" onclick="toggleDropdown()" title="Tools">☰</button>
    <div class="dropdown-content" id="dropdownContent">
      <button onclick="window.location.href='add_category.php'"><i data-lucide="loader"></i><span>Add category</span></button>
      <button onclick="window.location.href='staff.php'"><i data-lucide="shield-alert"></i><span>Staff</span></button>
    </div>
  </div>
  <button onclick="window.location.href='stock_quantity.php'"><i data-lucide="package"></i><span>View Stock Quantity</span></button>
  <button onclick="window.location.href='order_history.php'"><i data-lucide="history"></i><span>View Order History</span></button>
  <button onclick="window.location.href='stock_order.php'"><i data-lucide="shopping-cart"></i><span>Order New Stocks</span></button>
  <button onclick="window.location.href='stock_out.php'"><i data-lucide="shopping-cart"></i><span>Stock out</span></button>

  <!-- 登录/注册 或 登出按钮 -->
  <div class="auth-buttons">
    <?php if ($isLoggedIn): ?>
      <button onclick="window.location.href='logout.php'"><i data-lucide="log-out"></i><span>Logout</span></button>
    <?php else: ?>
      <button onclick="window.location.href='login.php'"><i data-lucide="log-in"></i><span>Login</span></button>
      <button onclick="window.location.href='register.php'"><i data-lucide="user-plus"></i><span>Register</span></button>
    <?php endif; ?>
  </div>
</div>

<div class="container">
  <div class="stock-container">
    <h2>Manage Stock</h2>
    <div class="controls">
      <button class="add-button" onclick="location.href='stock_order.php'">Order</button>
      <select id="categoryFilter">
        <option value="all">All Categories</option>
        <?php foreach ($category_map as $catName): ?>
          <option value="<?= htmlspecialchars($catName) ?>"><?= htmlspecialchars($catName) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="text" id="searchInput" placeholder="Search by name or code...">
    </div>

    <div class="stock-grid" id="stockGrid">
      <?php while ($item = mysqli_fetch_assoc($query)): ?>
        <?php $categoryName = $category_map[$item['category_id']] ?? 'Unknown'; ?>
        <div class="stock-card" data-category="<?= htmlspecialchars($categoryName) ?>">
          <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['item_name']) ?>">
          <div class="stock-info">
            <h3><?= htmlspecialchars($item['item_name']) ?></h3>
            <p>Code: <?= htmlspecialchars($item['item_code']) ?></p>
            <p>Qty: <?= htmlspecialchars($item['quantity']) ?></p>
            <p>Category: <?= htmlspecialchars($categoryName) ?></p>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</div>

<!-- 图片预览 Modal -->
<div id="imageModal" class="image-modal">
  <span class="close" onclick="closeModal()">&times;</span>
  <img class="modal-content" id="modalImage">
</div>

<script>
  const searchInput = document.getElementById('searchInput');
  const categoryFilter = document.getElementById('categoryFilter');

  function filterStock() {
    const keyword = searchInput.value.toLowerCase();
    const category = categoryFilter.value;
    const cards = document.querySelectorAll('.stock-card');

    cards.forEach(card => {
      const title = card.querySelector('h3').textContent.toLowerCase();
      const code = card.querySelector('p').textContent.toLowerCase();
      const cardCategory = card.dataset.category.toLowerCase();
      const matchesSearch = title.includes(keyword) || code.includes(keyword);
      const matchesCategory = category === "all" || cardCategory === category.toLowerCase();
      card.style.display = (matchesSearch && matchesCategory) ? 'block' : 'none';
    });
  }

  searchInput.addEventListener('input', filterStock);
  categoryFilter.addEventListener('change', filterStock);

  function toggleUserCard() {
    const card = document.getElementById('userCard');
    card.style.display = card.style.display === 'block' ? 'none' : 'block';
  }

  function toggleDropdown() {
    document.getElementById('dropdownContent').classList.toggle('show');
  }

  function showToast(message) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerText = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
  }

  function checkLowStock(threshold = 10) {
    const cards = document.querySelectorAll('.stock-card');
    let lowStockItems = [];
    cards.forEach(card => {
      const name = card.querySelector('h3').textContent;
      const qtyText = Array.from(card.querySelectorAll('p')).find(p => p.textContent.includes('Qty'));
      const qty = parseInt(qtyText.textContent.replace(/[^0-9]/g, ''));
      if (qty < threshold) {
        lowStockItems.push(`${name}（剩余 ${qty}）`);
      }
    });
    if (lowStockItems.length > 0) {
      showToast("⚠️ 以下库存不足：\n" + lowStockItems.join('\n'));
    }
  }

  // 图片点击预览
  document.querySelectorAll('.stock-card img').forEach(img => {
    img.addEventListener('click', function () {
      const modal = document.getElementById("imageModal");
      const modalImg = document.getElementById("modalImage");
      modal.style.display = "block";
      modalImg.src = this.src;
    });
  });

  // 关闭预览
  function closeModal() {
    document.getElementById("imageModal").style.display = "none";
  }

  window.addEventListener('DOMContentLoaded', () => {
    checkLowStock(10);
    lucide.createIcons();
  });
</script>
</body>
</html>
