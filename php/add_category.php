<?php
include_once('db.php');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
      echo "<script>
        alert('You do not have permission to access this page!');
        window.location.href = 'index.php';
    </script>";
    exit;
}

$toastMessage = "";

// ✅ 删除分类功能
if (isset($_POST['delete_category_id'])) {
    $delete_id = intval($_POST['delete_category_id']);
    if ($delete_id > 0) {
        $delete_sql = "DELETE FROM wmscategory WHERE category_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $delete_id);
        if ($delete_stmt->execute()) {
            $toastMessage = "delete_success";
        } else {
            $toastMessage = "delete_fail";
        }
        $delete_stmt->close();
    }
}

// ✅ 添加分类功能
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['category']) && !isset($_POST['delete_category_id'])) {
    $category = trim($_POST['category']);

    if (!empty($category)) {
        $check_sql = "SELECT category_id FROM wmscategory WHERE category = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $category);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $toastMessage = "exists";
        } else {
            $insert_sql = "INSERT INTO wmscategory (category) VALUES (?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("s", $category);

            if ($insert_stmt->execute()) {
                $toastMessage = "success";
            } else {
                $toastMessage = "fail";
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
    } else {
        $toastMessage = "empty";
    }
}

// ✅ 获取所有分类
$categories = $conn->query("SELECT category_id, category FROM wmscategory ORDER BY category ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Category</title>
<style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(-45deg, #fdfbfb, #ebedee, #e0d9f5, #e6f0ff);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .form-box {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      width: 420px;
      text-align: center;
    }

    h2 {
      margin-bottom: 25px;
      color: #333;
    }

    input[type="text"], select {
      width: 100%;
      padding: 14px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-size: 16px;
      transition: 0.3s ease;
    }

    input[type="text"]:focus, select:focus {
      border-color: #a18cd1;
      box-shadow: 0 0 12px rgba(161, 140, 209, 0.5);
      outline: none;
    }

    button {
      width: 100%;
      padding: 14px;
      background-color: #a18cd1;
      border: none;
      color: white;
      font-weight: bold;
      font-size: 16px;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    button:hover {
      transform: scale(1.03);
      box-shadow: 0 0 12px rgba(0,0,0,0.15);
    }

    .delete-btn {
      background-color: #f44336;
    }

    .delete-btn:hover {
      background-color: #d32f2f;
    }

    .back-button {
      background-color: #bbb;
      color: #333;
      margin-top: 10px;
    }

    .back-button:hover {
      background-color: #999;
    }

    .toast-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
    }

    .toast {
      background-color: #4CAF50;
      color: white;
      padding: 14px 20px;
      border-radius: 10px;
      font-size: 14px;
      animation: fadeInOut 5s forwards;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .toast.error {
      background-color: #f44336;
    }

    @keyframes fadeInOut {
      0% { opacity: 0; transform: translateY(-10px); }
      10%, 90% { opacity: 1; transform: translateY(0); }
      100% { opacity: 0; transform: translateY(-10px); }
    }
</style>
</head>
<body>
<div class="toast-container" id="toastContainer"></div>

<div class="form-box">
    <h2>Manage Categories</h2>

    <!-- ✅ 删除分类表单 -->
    <form action="add_category.php" method="POST" style="margin-top:20px;">
        <select name="delete_category_id" required>
            <option value="">-- Select Category to Delete --</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category']) ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" class="delete-btn">Delete Category</button>
    </form>

    <!-- ✅ 添加分类表单（移动到删除后面） -->
    <form action="add_category.php" method="POST" style="margin-top:20px;">
        <input type="text" name="category" placeholder="Enter Category Name" required>
        <button type="submit">Add Category</button>
    </form>

    <button type="button" class="back-button" onclick="location.href='index.php'">Go Back</button>
</div>

<script>
function showToast(message, isError = false) {
    const container = document.getElementById("toastContainer");
    const toast = document.createElement("div");
    toast.className = "toast" + (isError ? " error" : "");
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

<?php if ($toastMessage === "success"): ?>
    showToast("✅ Add category successful");
<?php elseif ($toastMessage === "fail"): ?>
    showToast("❌ Failed to add category", true);
<?php elseif ($toastMessage === "exists"): ?>
    showToast("⚠️ Category already exists", true);
<?php elseif ($toastMessage === "empty"): ?>
    showToast("⚠️ Category name is required", true);
<?php elseif ($toastMessage === "delete_success"): ?>
    showToast("🗑️ Category deleted successfully");
<?php elseif ($toastMessage === "delete_fail"): ?>
    showToast("❌ Failed to delete category", true);
<?php endif; ?>
</script>
</body>
</html>
