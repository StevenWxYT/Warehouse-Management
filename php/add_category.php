<?php
include_once('db.php');

$toastMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Category</title>
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

    input[type="text"] {
      width: 100%;
      padding: 14px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 10px;
      font-size: 16px;
      transition: 0.3s ease;
    }

    input[type="text"]:focus {
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
      background-color: #8d75c2;
      transform: scale(1.03);
      box-shadow: 0 0 12px rgba(141, 117, 194, 0.4);
    }

    .back-button {
      background-color: #a18cd1;
      color: #333;
      margin-top: 10px;
    }

    .back-button:hover {
      background-color: #bbb;
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
    <h2>Add Category</h2>
    <form action="add_category.php" method="POST">
      <input type="text" name="category" placeholder="Enter Category Name" required>
      <button type="submit">Add Category</button>
      <button type="button" class="back-button" onclick="location.href='stock_manage.php'">Go Back</button>
    </form>
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
    <?php endif; ?>
  </script>
</body>
</html>
