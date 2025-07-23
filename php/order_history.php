<?php
include_once('db.php');

// 获取分页参数
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 获取搜索和筛选条件
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// 统计总记录数
$count_sql = "
  SELECT COUNT(*) AS total
  FROM wmsitem_log l
  JOIN wmsitem i ON l.item_id = i.item_id
  WHERE (
    i.item_code LIKE ?
    OR i.item_name LIKE ?
    OR l.date LIKE ?
    OR l.time LIKE ?
  )
";
$count_params = ["%$search%", "%$search%", "%$search%", "%$search%"];
$count_types = "ssss";

if ($status !== '') {
  $count_sql .= " AND l.status = ?";
  $count_params[] = $status;
  $count_types .= "s";
}

$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($count_types, ...$count_params);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_records = $count_result['total'];
$total_pages = ceil($total_records / $limit);

// 查询记录
$sql = "
  SELECT 
    l.log_id,
    i.item_code,
    i.item_name,
    i.quantity,
    i.unit_price,
    i.image_path,
    l.status,
    l.date,
    l.time
  FROM wmsitem_log l
  JOIN wmsitem i ON l.item_id = i.item_id
  WHERE (
    i.item_code LIKE ?
    OR i.item_name LIKE ?
    OR l.date LIKE ?
    OR l.time LIKE ?
  )
";
$params = ["%$search%", "%$search%", "%$search%", "%$search%"];
$types = "ssss";

if ($status !== '') {
  $sql .= " AND l.status = ?";
  $params[] = $status;
  $types .= "s";
}

$sql .= " ORDER BY i.item_name ASC, l.date DESC, l.time DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$grouped_items = [];
while ($row = $result->fetch_assoc()) {
  $code = $row['item_code'];
  if (!isset($grouped_items[$code])) {
    $grouped_items[$code] = [
      'item_name' => $row['item_name'],
      'quantity' => $row['quantity'],
      'unit_price' => $row['unit_price'],
      'image' => $row['image_path'],
      'logs' => []
    ];
  }
  $grouped_items[$code]['logs'][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order History</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #fdfbfb, #ebedee);
      padding: 40px 20px;
    }
    .header {
      max-width: 1100px;
      margin: 0 auto 30px;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 15px;
      align-items: center;
    }
    .header h1 {
      font-size: 36px;
      color: #2c2c2c;
      font-weight: 700;
    }
    .controls {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .controls input,
    .controls select,
    .controls button {
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 14px;
      border: 1px solid #ccc;
    }
    .controls button {
      background-color: #6a5acd;
      color: #fff;
      border: none;
      cursor: pointer;
    }
    .controls button:hover {
      background-color: #5745b2;
    }

    .item-section {
      max-width: 1100px;
      margin: 0 auto 30px;
      background: #fff;
      padding: 25px;
      border-radius: 16px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.06);
    }

    .item-title {
      font-weight: 600;
      font-size: 18px;
      color: #444;
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .eye-button {
      background-color: #e0e7ff;
      border: none;
      border-radius: 50%;
      padding: 6px;
      width: 32px;
      height: 32px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: 0.3s ease;
    }

    .eye-button:hover {
      background-color: #c7d2fe;
      transform: scale(1.05);
    }

    .eye-button svg {
      width: 18px;
      height: 18px;
      fill: #4f46e5;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    th, td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }

    th {
      background-color: #f3f4f6;
      font-weight: 600;
    }

    .badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 13px;
      color: #fff;
      display: inline-block;
    }

    .in { background-color: #28a745; }
    .out { background-color: #dc3545; }

    .pagination {
      text-align: center;
      margin-top: 40px;
    }

    .pagination-btn {
      padding: 10px 18px;
      background-color: #6a5acd;
      color: white;
      border-radius: 10px;
      font-weight: 600;
      margin: 0 6px;
      text-decoration: none;
    }

    .page-indicator {
      font-weight: 600;
      color: #555;
      margin: 0 10px;
    }

    /* Modal */
    #itemModal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    #itemModal .modal-content {
      background: #fff;
      border-radius: 12px;
      padding: 25px;
      width: 400px;
      max-width: 90%;
      position: relative;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    #itemModal img {
      width: 100%;
      height: 200px;
      object-fit: contain;
      border-radius: 10px;
      margin-bottom: 20px;
    }

    #itemModal .close-btn {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 20px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<div class="header">
  <h1>Order History</h1>
  <form class="controls" method="get">
    <input type="text" name="search" placeholder="Search" value="<?= htmlspecialchars($search) ?>">
    <select name="status">
      <option value="">All Status</option>
      <option value="in" <?= $status === 'in' ? 'selected' : '' ?>>In</option>
      <option value="out" <?= $status === 'out' ? 'selected' : '' ?>>Out</option>
    </select>
    <button type="submit">Search</button>
    <button type="button" onclick="window.location.href='stock_manage.php'">Go Back</button>
  </form>
</div>

<?php foreach ($grouped_items as $code => $item): ?>
  <div class="item-section">
    <div class="item-title">
      <button class="eye-button" onclick="showModal('<?= $code ?>')" title="View Item Info">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
          <path d="M12 4.5C7 4.5 2.73 8.11 1 12c1.73 3.89 6 7.5 11 7.5s9.27-3.61 11-7.5c-1.73-3.89-6-7.5-11-7.5zm0 13a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11zm0-9a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7z"/>
        </svg>
      </button>
      <?= htmlspecialchars($item['item_name']) ?> - <?= htmlspecialchars($code) ?> (Qty: <?= $item['quantity'] ?>)

      <input type="hidden" id="name-<?= $code ?>" value="<?= htmlspecialchars($item['item_name']) ?>">
      <input type="hidden" id="qty-<?= $code ?>" value="<?= $item['quantity'] ?>">
      <input type="hidden" id="price-<?= $code ?>" value="<?= $item['unit_price'] ?>">
      <input type="hidden" id="img-<?= $code ?>" value="<?= htmlspecialchars($item['image']) ?>">
    </div>
    <table>
      <thead>
        <tr><th>Date</th><th>Time</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($item['logs'] as $log): ?>
          <tr>
            <td><?= htmlspecialchars($log['date']) ?></td>
            <td><?= htmlspecialchars($log['time']) ?></td>
            <td><span class="badge <?= $log['status'] ?>"><?= ucfirst($log['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>

<?php if ($total_pages > 1): ?>
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=<?= $page - 1 ?>" class="pagination-btn">← Previous</a>
    <?php endif; ?>
    <span class="page-indicator">Page <?= $page ?> of <?= $total_pages ?></span>
    <?php if ($page < $total_pages): ?>
      <a href="?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&page=<?= $page + 1 ?>" class="pagination-btn">Next →</a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- Modal -->
<div id="itemModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <img id="modalImage" src="" alt="Item Image">
    <h2 id="modalName"></h2>
    <p><strong>Item Code:</strong> <span id="modalCode"></span></p>
    <p><strong>Quantity:</strong> <span id="modalQty"></span></p>
    <p><strong>Unit Price:</strong> RM <span id="modalPrice"></span></p>
  </div>
</div>

<script>
function showModal(code) {
  document.getElementById('modalName').innerText = document.getElementById('name-' + code).value;
  document.getElementById('modalCode').innerText = code;
  document.getElementById('modalQty').innerText = document.getElementById('qty-' + code).value;
  document.getElementById('modalPrice').innerText = parseFloat(document.getElementById('price-' + code).value).toFixed(2);
  document.getElementById('modalImage').src = document.getElementById('img-' + code).value;
  document.getElementById('itemModal').style.display = 'flex';
}
function closeModal() {
  document.getElementById('itemModal').style.display = 'none';
}
</script>

</body>
</html>
