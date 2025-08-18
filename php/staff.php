<?php
include_once('db.php');
session_start();

if (!isset($_SESSION['role'])) {
      echo "<script>
        alert('You do not have permission to access this page!');
        window.location.href = 'index.php';
    </script>";
    exit;
}

date_default_timezone_set("Asia/Kuala_Lumpur");

// 查询所有员工信息
$sql = "SELECT username, email, role FROM wmsregister ORDER BY username ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Staff Information</title>
<style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
    h1 { text-align: center; margin-bottom: 20px; }
    .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
    .card {
        background: white; padding: 20px; border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .card h3 { margin-bottom: 10px; color: #333; }
    .card p { margin: 5px 0; color: #555; }
    .btn-container { margin-top: 20px; text-align: center; }
    .btn-container a button {
        padding: 10px 20px; border: none; border-radius: 8px;
        background: #8a76c4; color: white; cursor: pointer;
    }
    .btn-container a button:hover { background: #715abf; }
</style>
</head>
<body>

<h1>Staff Information</h1>

<div class="grid">
<?php while($row = $result->fetch_assoc()): ?>
    <div class="card">
        <h3><?= htmlspecialchars($row['username']) ?></h3>
        <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($row['role']) ?></p>
    </div>
<?php endwhile; ?>
</div>

<div class="btn-container">
    <a href="index.php"><button>Go Back</button></a>
    <?php if (strtolower($_SESSION['role']) === 'admin'): ?>
        <a href="register.php"><button>Register New User</button></a>
    <?php endif; ?>
</div>

</body>
</html>
