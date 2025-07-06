  <?php
include_once('db.php');

// 处理更新与删除请求
$response = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_code = $_POST['item_code'];
    if ($_POST['action'] === 'update') {
        $quantity = intval($_POST['quantity']);
        $stmt = $conn->prepare("UPDATE wmsitem SET quantity = ? WHERE item_code = ?");
        $stmt->bind_param("is", $quantity, $item_code);
        $response = $stmt->execute()
            ? ['success' => true, 'message' => '✅ Add stock successful!']
            : ['success' => false, 'message' => '❌ Failed to update stock.'];
    }

    if ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM wmsitem WHERE item_code = ?");
        $stmt->bind_param("s", $item_code);
        $response = $stmt->execute()
            ? ['success' => true, 'message' => '🗑️ Item deleted successfully.']
            : ['success' => false, 'message' => '❌ Failed to delete item.'];
    }

    // 如果是 AJAX 请求
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// 获取所有商品
$items_sql = "SELECT * FROM wmsitem ORDER BY item_code ASC";
$query = mysqli_query($conn, $items_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Stock Quantity</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
      * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding: 40px;
      background: linear-gradient(-45deg, #fdfbfb, #ebedee, #e0d9f5, #e6f0ff);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
    }

    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .container {
      max-width: 1200px;
      width: 100%;
      background-color: #ffffffcc;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      backdrop-filter: blur(8px);
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 20px;
    }

    h1 {
      font-size: 32px;
      color: #333;
    }

    .category-input {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .category-input label {
      font-size: 16px;
      font-weight: 600;
      color: #333;
    }

    .category-select {
      padding: 8px 12px;
      border-radius: 8px;
      border: 2px solid #ccc;
      background-color: #fafafa;
      font-size: 14px;
      color: #333;
      width: 180px;
    }

    .search-input {
      padding: 8px 12px;
      border-radius: 8px;
      border: 2px solid #ccc;
      background-color: #fafafa;
      font-size: 14px;
      color: #333;
      width: 200px;
    }

    .go-back-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      background-color: #8a76c4;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .go-back-btn:hover {
      transform: translateY(-2px);
      background-color: #7a68b6;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    }

    .item-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
    }

    .item-card {
      display: flex;
      flex-direction: column;
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .item-left {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      margin-bottom: 15px;
    }

    .item-left input[type="file"] {
      font-size: 14px;
    }

    .item-left img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 10px;
    }

    .item-info label {
      font-weight: 600;
      font-size: 16px;
      display: block;
      margin-bottom: 6px;
    }

    .item-info input.qty-input {
      width: 100px;
      padding: 8px 10px;
      border: 2px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
      text-align: center;
      background-color: #fafafa;
    }

    .actions {
      display: flex;
      justify-content: flex-start;
      gap: 12px;
    }

    .btn {
      padding: 8px 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      color: white;
      transition: background-color 0.2s ease;
    }

    .update-btn {
      background-color: #4CAF50;
    }

    .update-btn:hover {
      background-color: #45a049;
    }

    .delete-btn {
      background-color: #dc3545;
    }

    .delete-btn:hover {
      background-color: #c82333;
    } 
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Stock Quantity</h1>
      <div class="category-input">
        <input type="text" placeholder="Search Item" class="search-input" id="searchInput">
        <label for="global-category">Category:</label>
        <select id="global-category" class="category-select">
          <option value="">All</option>
          <option value="Stationery">Stationery</option>
          <option value="Electronics">Electronics</option>
          <option value="Office Supply">Office Supply</option>
          <option value="Others">Others</option>
        </select>
        <button onclick="history.back()" class="go-back-btn">Go Back</button>
      </div>
    </div>

    <!-- Item Grid -->
    <div class="item-grid" id="itemGrid">
      <?php while ($row = mysqli_fetch_assoc($query)): ?>
      <div class="item-card" data-category="<?= $row['category_id'] ?>">
        <div class="item-left">
          <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['item_name']) ?>">
          <div class="item-info">
            <label><?= htmlspecialchars($row['item_code']) ?></label>
            <input type="number" value="<?= $row['quantity'] ?>" class="qty-input">
          </div>
        </div>
        <div class="actions">
          <button class="btn update-btn">Update</button>
          <button class="btn delete-btn">Delete</button>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>

  <div id="toast" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

  <script>
    function showToast(message, success = true) {
      const toast = document.createElement('div');
      toast.textContent = message;
      toast.style.background = success ? '#4CAF50' : '#dc3545';
      toast.style.color = 'white';
      toast.style.padding = '12px 20px';
      toast.style.marginBottom = '10px';
      toast.style.borderRadius = '8px';
      toast.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
      toast.style.animation = 'fadeOut 4s forwards';
      document.getElementById('toast').appendChild(toast);
      setTimeout(() => toast.remove(), 4000);
    }

    document.addEventListener("click", function(e) {
      const target = e.target;

      if (target.classList.contains("update-btn") || target.classList.contains("delete-btn")) {
        const item = target.closest(".item-card");
        const itemCode = item.querySelector("label").textContent;
        const qty = item.querySelector(".qty-input")?.value || 0;
        const action = target.classList.contains("update-btn") ? "update" : "delete";

        const formData = new FormData();
        formData.append("action", action);
        formData.append("item_code", itemCode);
        if (action === "update") {
          formData.append("quantity", qty);
        }

        fetch("", {
          method: "POST",
          body: formData,
          headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(res => res.json())
        .then(data => {
          showToast(data.message, data.success);
          if (data.success) setTimeout(() => location.reload(), 1200);
        });
      }
    });

    document.getElementById("searchInput").addEventListener("input", function() {
      const keyword = this.value.toLowerCase();
      document.querySelectorAll(".item-card").forEach(card => {
        const code = card.querySelector("label").textContent.toLowerCase();
        card.style.display = code.includes(keyword) ? "flex" : "none";
      });
    });

    document.getElementById("global-category").addEventListener("change", function() {
      const category = this.value;
      document.querySelectorAll(".item-card").forEach(card => {
        const cardCategory = card.dataset.category;
        card.style.display = !category || category === cardCategory ? "flex" : "none";
      });
    });
  </script>
</body>
</html>
