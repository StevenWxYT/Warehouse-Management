<?php
include_once('db.php');
session_start();
date_default_timezone_set("Asia/Kuala_Lumpur");

$today = date('Y-m-d');
$time_now = date('H:i:s');
$user = null;

if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $sql = "SELECT username, email, role FROM wmsregister WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Information</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
      margin: 0;
      padding: 0;
    }

    body {
      background: linear-gradient(135deg, #fdfbfb, #ebedee);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      padding: 20px;
    }

    .card {
      background: #ffffff;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 15px 30px rgba(0,0,0,0.1);
      max-width: 500px;
      width: 100%;
      text-align: center;
      animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    h2 {
      font-size: 28px;
      color: #333;
      margin-bottom: 30px;
    }

    .info {
      font-size: 16px;
      color: #555;
      margin: 15px 0;
    }

    .label {
      font-weight: 600;
      color: #222;
    }

    .not-logged-in {
      color: #e74c3c;
      font-size: 18px;
      font-weight: 500;
      margin-top: 20px;
    }

    .back-btn {
      margin-top: 35px;
    }

    .back-btn button {
      background-color: #8a76c4;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .back-btn button:hover {
      background-color: #715abf;
      transform: translateY(-2px);
    }
  </style>
</head>
<body>

  <div class="card">
    <h2>Staff Information</h2>

    <?php if ($user): ?>
      <div class="info"><span class="label">Name:</span> <?= htmlspecialchars($user['username']) ?></div>
      <div class="info"><span class="label">Email:</span> <?= htmlspecialchars($user['email']) ?></div>
      <div class="info"><span class="label">Role:</span> <?= htmlspecialchars($user['role']) ?></div>
      <div class="info"><span class="label">Current Date:</span> <?= $today ?></div>
      <div class="info"><span class="label">Current Time:</span> <?= $time_now ?></div>
    <?php else: ?>
      <div class="not-logged-in">⚠️ No user is currently logged in.</div>
    <?php endif; ?>

    <div class="back-btn">
      <a href="stock_manage.php">
        <button>Go Back</button>
      </a>
    </div>
  </div>

</body>
</html>
