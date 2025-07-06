<?php
include_once('db.php');

// 获取搜索和筛选条件
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

// SQL 查询
$sql = "
  SELECT 
    l.log_id,
    i.item_code,
    i.quantity,
    l.status,
    l.date,
    l.time
  FROM wmsitem_log l
  JOIN wmsitem i ON l.item_id = i.item_id
  WHERE i.item_code LIKE ?
";

$params = ["%$search%"];
$types = "s";

if ($status !== '') {
  $sql .= " AND l.status = ?";
  $params[] = $status;
  $types .= "s";
}

$sql .= " ORDER BY l.date DESC, l.time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Order History</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
   
    body {
      background: linear-gradient(135deg, #fdfbfb, #ebedee, #e0d9f5, #e6f0ff);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
      padding: 40px 20px;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
    }

    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .header {
      max-width: 1000px;
      margin: 0 auto 30px auto;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      gap: 15px;
    }

    .header h1 {
      font-size: 36px;
      color: #333;
      letter-spacing: 1px;
    }

    .controls {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .controls input,
    .controls select,
    .controls .go-back-btn {
      padding: 10px 14px;
      border-radius: 10px;
      font-size: 14px;
      box-shadow: 2px 2px 8px rgba(0,0,0,0.05);
      transition: 0.3s;
    }

    .controls input,
    .controls select {
      border: 1px solid #ddd;
      background-color: #fff;
    }

    .controls input:focus,
    .controls select:focus {
      outline: none;
      border-color: #a18cd1;
      box-shadow: 0 0 0 3px rgba(161, 140, 209, 0.2);
    }

    .go-back-btn {
      border: none;
      background-color: #a18cd1;
      color: white;
      cursor: pointer;
    }

    .go-back-btn:hover {
      background-color: #8a76c4;
    }

    .order-list {
      max-width: 1000px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .order-card {
      background: #fff;
      border-radius: 20px;
      padding: 24px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 20px;
    }

    .order-field {
      flex: 1 1 30%;
      display: flex;
      flex-direction: column;
    }

    .order-field label {
      font-weight: 600;
      margin-bottom: 6px;
      color: #555;
      font-size: 14px;
    }

    .order-field input {
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      background-color: #f7f7f7;
      color: #444;
      pointer-events: none;
    }

    .status-badge {
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: bold;
      text-align: center;
      width: fit-content;
    }

    .in {
      background-color: #d4edda;
      color: #155724;
    }

    .out {
      background-color: #f8d7da;
      color: #721c24;
    }

    @media (max-width: 600px) {
      .order-field {
        flex: 1 1 100%;
      }
      .header {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>

  <div class="header">
    <h1>Order History</h1>
    <form class="controls" method="get">
      <input type="text" name="search" placeholder="Search Item Code" value="<?= htmlspecialchars($search) ?>">
      <select name="status">
        <option value="">All Status</option>
        <option value="in" <?= $status === 'in' ? 'selected' : '' ?>>In</option>
        <option value="out" <?= $status === 'out' ? 'selected' : '' ?>>Out</option>
      </select>
      <button type="submit" class="go-back-btn">Search</button>
      <button type="button" onclick="history.back()" class="go-back-btn">Go Back</button>
    </form>
  </div>

  <div class="order-list">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="order-card">
        <div class="order-field">
          <label>Order ID</label>
          <input type="text" value="<?= $row['log_id'] ?>" readonly>
        </div>
        <div class="order-field">
          <label>Item Code</label>
          <input type="text" value="<?= $row['item_code'] ?>" readonly>
        </div>
        <div class="order-field">
          <label>Quantity</label>
          <input type="text" value="<?= $row['quantity'] ?>" readonly>
        </div>
        <div class="order-field">
          <label>Date</label>
          <input type="text" value="<?= $row['date'] ?>" readonly>
        </div>
        <div class="order-field">
          <label>Time</label>
          <input type="text" value="<?= $row['time'] ?>" readonly>
        </div>
        <div class="order-field">
          <label>Status</label>
          <span class="status-badge <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

</body>
</html>
