<?php
include_once('db.php');
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Saleman'])) {
   echo "<script>
        window.location.href = 'index.php';
    </script>";
    exit;
}

$category_sql = "SELECT * FROM wmscategory ORDER BY category ASC";
$category_result = mysqli_query($conn, $category_sql);

$response = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_code = $_POST['item_code'];
    if ($_POST['action'] === 'update') {
        $quantity = intval($_POST['quantity']);

        $stmt_check = $conn->prepare("SELECT quantity, unit_price FROM wmsitem WHERE item_code = ?");
        $stmt_check->bind_param("s", $item_code);
        $stmt_check->execute();
        $stmt_check->bind_result($current_quantity, $unit_price);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($quantity > $current_quantity) {
            $restock_qty = $quantity - $current_quantity;
            $restock_cost = $restock_qty * $unit_price;

            $stmt = $conn->prepare("UPDATE wmsitem SET quantity = ? WHERE item_code = ?");
            $stmt->bind_param("is", $quantity, $item_code);
            $response = $stmt->execute()
            ? ['success' => true, 'message' => '✅ Stock updated successfully!']
            : ['success' => false, 'message' => '❌ Failed to update stock.'];
        } else {
            $response = ['success' => false, 'message' => '⚠️ Quantity must be greater than current stock.'];
        }
    }

    if ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM wmsitem WHERE item_code = ?");
        $stmt->bind_param("s", $item_code);
        $response = $stmt->execute()
          ? ['success' => true, 'message' => '🗑️ Item deleted successfully.']
          : ['success' => false, 'message' => '❌ Failed to delete item. ' . $stmt->error];
    }

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

$items_sql = "SELECT * FROM wmsitem ORDER BY item_code ASC";
$query = mysqli_query($conn, $items_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Stock Quantity</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    /* 样式不变 */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    body { min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 40px;
      background: linear-gradient(-45deg, #fdfbfb, #ebedee, #e0d9f5, #e6f0ff); background-size: 400% 400%; animation: gradientBG 15s ease infinite; }
    @keyframes gradientBG { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
    .container { max-width: 1200px; width: 100%; background-color: #ffffffcc; padding: 40px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); backdrop-filter: blur(8px); }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
    h1 { font-size: 32px; color: #333; }
    .category-input { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .category-select, .search-input { padding: 8px 12px; border-radius: 8px; border: 2px solid #ccc; background-color: #fafafa; font-size: 14px; color: #333; }
    .search-input { width: 200px; }
    .category-select { width: 180px; }
    .go-back-btn { background-color: #8a76c4; color: white; border: none; padding: 10px 18px; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); transition: all 0.3s ease; }
    .go-back-btn:hover { transform: translateY(-2px); background-color: #7a68b6; }
    .item-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .item-card { display: flex; flex-direction: column; background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); }
    .item-left { display: flex; flex-direction: column; align-items: flex-start; gap: 10px; margin-bottom: 15px; }
    .item-left img { width: 120px; height: 120px; object-fit: cover; border-radius: 10px; cursor: zoom-in; }
    .item-info label { font-weight: 600; font-size: 16px; display: block; margin-bottom: 6px; }
    .item-info input.qty-input { width: 100px; padding: 8px 10px; border: 2px solid #ccc; border-radius: 8px; font-size: 16px; text-align: center; background-color: #fafafa; }
    .actions { display: flex; justify-content: flex-start; gap: 12px; }
    .btn { padding: 8px 16px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; color: white; transition: background-color 0.2s ease; }
    .update-btn { background-color: #4caf50; }
    .update-btn:hover { background-color: #45a049; }
    .delete-btn { background-color: #dc3545; }
    .delete-btn:hover { background-color: #c82333; }
    /* confirm 弹窗样式保持 */
    .confirm-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(3px); }
    .confirm-box { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); max-width: 320px; width: 90%; animation: scaleIn 0.25s ease; z-index: 1001; }
    .confirm-box h3 { margin-bottom: 10px; font-size: 20px; color: #333; }
    .confirm-box p { margin-bottom: 20px; font-size: 15px; color: #555; }
    .confirm-buttons { display: flex; justify-content: space-between; }
    .confirm-buttons button { padding: 8px 14px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; flex: 1; margin: 0 5px; }
    #confirmYes { background-color: #4caf50; color: white; }
    #confirmYes:hover { background-color: #43a047; }
    #confirmNo { background-color: #dc3545; color: white; }
    #confirmNo:hover { background-color: #c82333; }
    @keyframes scaleIn { from { transform: translate(-50%, -50%) scale(0.9); opacity: 0; } to { transform: translate(-50%, -50%) scale(1); opacity: 1; } }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Stock Quantity</h1>
      <div class="category-input">
        <input type="text" placeholder="Search Item" class="search-input" id="searchInput" />
        <label for="global-category">Category:</label>
        <select id="global-category" class="category-select">
          <option value="">All</option>
          <?php while ($cat = mysqli_fetch_assoc($category_result)) : ?>
            <option value="<?= htmlspecialchars($cat['category_id']) ?>">
              <?= htmlspecialchars($cat['category']) ?>
            </option>
          <?php endwhile; ?>
        </select>
        <button id="restockBtn" style="background-color: #2196f3; color: white; border: none; padding: 10px 18px; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);"> Restock</button>
        <button id="lowStockBtn" style="background-color: #ff9800; color: white; border: none; padding: 10px 18px; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">Low Stock</button>
        <button id="refreshBtn" style="background-color: #4caf50; color: white; border: none; padding: 10px 18px; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); margin-left: 10px;">Refresh</button>
        <button onclick="window.location.href='index.php'" class="go-back-btn">Go Back</button>
      </div>
    </div>

  <div class="item-grid" id="itemGrid">
  <?php while ($row = mysqli_fetch_assoc($query)) : ?>
    <div class="item-card" data-category="<?= htmlspecialchars($row['category_id']) ?>">
      <div class="item-left">
        <div style="font-weight: 600; font-size: 16px;"><?= htmlspecialchars($row['item_name']) ?></div>
        <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="<?= htmlspecialchars($row['item_name']) ?>" />
        <div class="item-info">
          <div style="color: #666; font-size: 14px;"><?= htmlspecialchars($row['item_code']) ?></div>
          <input type="text " value="<?= $row['quantity'] ?>" readonly class="qty-input" />
        </div>
      </div>
    </div>
  <?php endwhile; ?>
</div>


    <div style="margin-top: 30px; text-align: center;">
      <button id="prevPage" style="padding: 6px 14px; margin: 0 5px;">&laquo; Prev</button>
      <span id="pageInfo" style="font-weight: 600; font-size: 16px;"></span>
      <button id="nextPage" style="padding: 6px 14px; margin: 0 5px;">Next &raquo;</button>
    </div>

    <div id="toast" style="position: fixed; top: 20px; right: 20px; z-index: 9999"></div>

    <script>
      function customConfirm(title, message) {
        return new Promise((resolve) => {
          document.getElementById("confirmTitle").textContent = title;
          document.getElementById("confirmMessage").textContent = message;
          const popup = document.getElementById("customConfirm");
          popup.style.display = "block";
          const yesBtn = document.getElementById("confirmYes");
          const noBtn = document.getElementById("confirmNo");
          const cleanup = () => { popup.style.display = "none"; yesBtn.onclick = null; noBtn.onclick = null; };
          yesBtn.onclick = () => { cleanup(); resolve(true); };
          noBtn.onclick = () => { cleanup(); resolve(false); };
        });
      }


      document.getElementById("searchInput").addEventListener("input", function () {
        const keyword = this.value.toLowerCase();
        document.querySelectorAll(".item-card").forEach((card) => {
          const itemName = card.querySelector("div").textContent.toLowerCase();
          const itemCode = card.querySelector(".item-info div").textContent.toLowerCase();
          card.style.display = itemName.includes(keyword) || itemCode.includes(keyword) ? "flex" : "none";
        });
      });

      document.getElementById("global-category").addEventListener("change", function () {
        const category = this.value;
        document.querySelectorAll(".item-card").forEach((card) => {
          const cardCategory = card.dataset.category;
          card.style.display = !category || category === cardCategory ? "flex" : "none";
        });
      });

      const allCards = Array.from(document.querySelectorAll(".item-card"));

      function showAllItems() {
        allCards.forEach((card) => (card.style.display = "flex"));
        setupPagination(allCards);
        togglePagination(true);
      }

      document.getElementById("lowStockBtn").addEventListener("click", function () {
        const threshold = 10;
        const lowStockCards = [];
        allCards.forEach((card) => {
          const qty = parseInt(card.querySelector(".qty-input").value) || 0;
          if (qty < threshold) {
            card.style.display = "flex";
            lowStockCards.push(card);
          } else {
            card.style.display = "none";
          }
        });
        if (lowStockCards.length > 9) {
          setupPagination(lowStockCards);
          togglePagination(true);
        } else {
          togglePagination(false);
        }
      });

      document.getElementById("refreshBtn").addEventListener("click", function () {
        document.getElementById("searchInput").value = "";
        document.getElementById("global-category").value = "";
        showAllItems();
      });

      function togglePagination(show) {
        document.getElementById("prevPage").style.display = show ? "" : "none";
        document.getElementById("nextPage").style.display = show ? "" : "none";
        document.getElementById("pageInfo").style.display = show ? "" : "none";
      }

      function setupPagination(cards) {
        let currentPage = 1;
        const itemsPerPage = 9;
        const totalPages = Math.ceil(cards.length / itemsPerPage);
        function renderPage() {
          cards.forEach((card, index) => {
            card.style.display = index >= (currentPage - 1) * itemsPerPage && index < currentPage * itemsPerPage ? "flex" : "none";
          });
          document.getElementById("pageInfo").textContent = `Page ${currentPage} of ${totalPages}`;
        }
        document.getElementById("prevPage").onclick = function () {
          if (currentPage > 1) { currentPage--; renderPage(); }
        };
        document.getElementById("nextPage").onclick = function () {
          if (currentPage < totalPages) { currentPage++; renderPage(); }
        };
        renderPage();
      }

      setupPagination(allCards);
      togglePagination(true);

      function showToast(message, success) {
        const toast = document.getElementById("toast");
        const el = document.createElement("div");
        el.textContent = message;
        el.style.padding = "12px 20px";
        el.style.marginTop = "10px";
        el.style.borderRadius = "8px";
        el.style.fontWeight = "600";
        el.style.color = "white";
        el.style.background = success ? "#4caf50" : "#dc3545";
        el.style.boxShadow = "0 2px 6px rgba(0,0,0,0.2)";
        el.style.opacity = "0";
        el.style.transition = "opacity 0.3s ease";
        toast.appendChild(el);

        // 触发淡入效果
        setTimeout(() => { el.style.opacity = "1"; }, 50);

        // 自动消失
        setTimeout(() => {
          el.style.opacity = "0";
          setTimeout(() => el.remove(), 300);
        }, 2500);
      }

      // 点击图片放大功能
      document.querySelectorAll(".item-left img").forEach((img) => {
        img.addEventListener("click", () => {
          const overlay = document.createElement("div");
          overlay.style.position = "fixed";
          overlay.style.top = "0";
          overlay.style.left = "0";
          overlay.style.width = "100%";
          overlay.style.height = "100%";
          overlay.style.background = "rgba(0,0,0,0.8)";
          overlay.style.display = "flex";
          overlay.style.alignItems = "center";
          overlay.style.justifyContent = "center";
          overlay.style.zIndex = "9999";

          const enlarged = document.createElement("img");
          enlarged.src = img.src;
          enlarged.style.maxWidth = "90%";
          enlarged.style.maxHeight = "90%";
          enlarged.style.borderRadius = "12px";
          enlarged.style.boxShadow = "0 4px 20px rgba(0,0,0,0.4)";

          overlay.appendChild(enlarged);
          document.body.appendChild(overlay);

          overlay.addEventListener("click", () => overlay.remove());
        });
      });

      document.getElementById("restockBtn").addEventListener("click", function () {
  window.location.href = "Restock.php";
});

    </script>
  </div>
</body>
</html>
