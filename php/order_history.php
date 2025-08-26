<?php
include_once('db.php');
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Saleman'])) {
    echo "<script>
        window.location.href = 'index.php';
    </script>";
    exit;
}

// 分页设置
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 搜索与分类筛选
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

// 获取所有分类
$category_sql = "SELECT category_id, category FROM wmscategory";
$category_result = mysqli_query($conn, $category_sql);

// 构建 WHERE 子句
$where_clause = "1";
$params = [];
$types = "";

if (!empty($search)) {
    $where_clause .= " AND (combined.item_code LIKE ? OR combined.item_name LIKE ? OR combined.date LIKE ? OR combined.time LIKE ? OR combined.status LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
    $types .= "sssss";
}
if (!empty($category_filter)) {
    $where_clause .= " AND combined.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}

/**
 * 获取总记录数
 */
$count_sql = "
    SELECT COUNT(*) AS total FROM (
        -- stock_in 和 restock（不合并，直接显示）
        SELECT i.item_code, i.item_name, i.unit_price, l.item_quantity,
               i.image_path, l.date, l.time, l.status, i.category_id
        FROM wmsitem_log l
        INNER JOIN wmsitem i ON l.item_id = i.item_id

        UNION ALL

        -- stock_out
        SELECT s.item_code, s.item_name, s.unit_price, s.quantity,
               i.image_path, s.date, s.time, 'out' AS status, i.category_id
        FROM wmsstock_out s
        INNER JOIN wmsitem i ON s.item_code = i.item_code
    ) AS combined
    WHERE $where_clause
";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

/**
 * 获取数据（分页）
 */
$data_sql = "
    SELECT * FROM (
        -- stock_in 和 restock（不合并）
        SELECT i.item_code, i.item_name, i.unit_price, l.item_quantity,
               i.image_path, l.date, l.time, l.status, i.category_id
        FROM wmsitem_log l
        INNER JOIN wmsitem i ON l.item_id = i.item_id

        UNION ALL

        -- stock_out
        SELECT s.item_code, s.item_name, s.unit_price, s.quantity,
               i.image_path, s.date, s.time, 'out' AS status, i.category_id
        FROM wmsstock_out s
        INNER JOIN wmsitem i ON s.item_code = i.item_code
    ) AS combined
    WHERE $where_clause
    ORDER BY date DESC, time DESC
    LIMIT ? OFFSET ?
";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($data_sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>








  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        background: #f7f7f7;
        padding: 30px;
      }
      .header {
        max-width: 1000px;
        margin: 0 auto 20px;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        align-items: center;
      }
      h1 {
        font-size: 28px;
        margin: 0;
      }
      .controls input, .controls select, .controls button {
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        margin-right: 8px;
      }
      table {
        width: 100%;
        max-width: 1000px;
        margin: 0 auto;
        background: white;
        border-collapse: collapse;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      }
      th, td {
        padding: 12px;
        border-bottom: 1px solid #eee;
      }
      th {
        background: #f0f0f0;
      }
      .eye-button {
        cursor: pointer;
        background: #e0e7ff;
        border: none;
        padding: 6px;
        border-radius: 50%;
      }
      .badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: bold;
        color: #fff;
      }
      .in { background-color: #28a745; }
      .out { background-color: #dc3545; }
      .restock {background-color: #3333ff;}

      .pagination {
        text-align: center;
        margin-top: 20px;
      }
      .pagination a {
        background: #6a5acd;
        color: white;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 6px;
        margin: 0 5px;
      }

      #itemModal {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
        visibility: hidden;
        opacity: 0;
        pointer-events: none;
        display: flex;
        transition: opacity 0.2s ease;
        z-index: 999;
      }
      #itemModal.show {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
      }
      .modal-content {
        background: #fff;
        padding: 20px;
        width: 400px;
        border-radius: 10px;
        position: relative;
        text-align: center;
      }
      .modal-content img {
        max-width: 100%;
        height: auto;
        margin-bottom: 15px;
        border-radius: 10px;
        box-shadow: 0 0 5px rgba(0,0,0,0.1);
      }
      .close-btn {
        position: absolute;
        right: 15px;
        top: 10px;
        font-size: 20px;
        cursor: pointer;
      }

    .controls {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.controls input[type="text"],
.controls select {
  padding: 10px 14px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease-in-out;
  background: #fff;
  min-width: 180px;
}

.controls input[type="text"]:focus,
.controls select:focus {
  border-color: #6a5acd;
  box-shadow: 0 0 0 3px rgba(106, 90, 205, 0.2);
  outline: none;
}

/* 搜索按钮 */
.controls button[type="submit"] {
  background: #8a76c4;
  color: #fff;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
  transition: all 0.2s ease-in-out;
}

.controls button[type="submit"]:hover {
  background: #5848c2;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(106, 90, 205, 0.3);
}

/* Go Back 按钮 */
.controls button[type="button"] {
  background: #8a76c4;
  color: #fff;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s ease-in-out;
}

.controls button[type="button"]:hover {
  background: #5848c2;
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

    </style>
  </head>
  <body>

  <div class="header">
    <h1>Order History</h1>
    <form method="get" class="controls">
      <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
      <select name="category">
        <option value="">All Categories</option>
        <?php while ($cat = mysqli_fetch_assoc($category_result)): ?>
          <option value="<?= $cat['category_id'] ?>" <?= ($cat['category_id'] == $category_filter) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['category']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <button type="submit">Search</button>
      <button type="button" onclick="window.location.href='index.php'">Go Back</button>
    </form>
  </div>

  <table>
    <thead>
      <tr>
        <th>Date</th>
        <th>Time</th>
        <th>Item Name</th>
        <th>Item Code</th>
        <th>Quantity</th>
        <th>Total Price</th>
        <th>Status</th>
        <th>View</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <?php
          $qty = intval($row['item_quantity']);
          $unit_price = floatval($row['unit_price']);
          $total_price = $qty * $unit_price;
          $image_path = htmlspecialchars($row['image_path'] ?? 'wms.jpg');
        ?>
        <tr>
          <td><?= htmlspecialchars($row['date']) ?></td>
          <td><?= htmlspecialchars($row['time']) ?></td>
          <td><?= htmlspecialchars($row['item_name']) ?></td>
          <td><?= htmlspecialchars($row['item_code']) ?></td>
          <td><?= $qty ?></td>
          <td>RM <?= number_format($total_price, 2) ?></td>
          <td><span class="badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
          <td>
            <button class="eye-button" onclick="showModal('<?= htmlspecialchars($row['item_name']) ?>', '<?= $row['item_code'] ?>', <?= $qty ?>, <?= $unit_price ?>, '<?= $image_path ?>')">👁️</button>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(['search' => $search, 'category' => $category_filter, 'page' => $page - 1]) ?>">← Prev</a>
      <?php endif; ?>
      <span>Page <?= $page ?> of <?= $total_pages ?></span>
      <?php if ($page < $total_pages): ?>
        <a href="?<?= http_build_query(['search' => $search, 'category' => $category_filter, 'page' => $page + 1]) ?>">Next →</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Modal -->
  <div id="itemModal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal()">&times;</span>
      <img id="modalImage" src="" alt="Item Image" onerror="this.src='wms.jpg'">
      <h2 id="modalName"></h2>
      <p><strong>Item Code:</strong> <span id="modalCode"></span></p>
      <p><strong>Quantity:</strong> <span id="modalQty"></span></p>
      <p><strong>Unit Price:</strong> RM <span id="modalPrice"></span></p>
    </div>
  </div>

  <script>
  function showModal(name, code, qty, price, imagePath) {
    document.getElementById('modalName').innerText = name;
    document.getElementById('modalCode').innerText = code;
    document.getElementById('modalQty').innerText = qty;
    document.getElementById('modalPrice').innerText = parseFloat(price).toFixed(2);
    document.getElementById('modalImage').src = imagePath;
    document.getElementById('itemModal').classList.add('show');
  }
  function closeModal() {
    document.getElementById('itemModal').classList.remove('show');
  }
  </script>

  </body>
  </html>
