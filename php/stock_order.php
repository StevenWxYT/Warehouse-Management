
<?php

include_once('db.php');

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    $item_name =$_POST['item_name'];
    $quantity = $_POST['quantity'];
    $item_code = $_POST['item_code'];

    $date = date("d - m - Y");
    $time = date("h:i:s A");
    
}





?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Order Stock</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(-45deg, #d9afd9, #97d9e1, #fbc2eb, #a1c4fd);
      background-size: 400% 400%;
      animation: gradientFlow 15s ease infinite;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    @keyframes gradientFlow {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .container {
      display: flex;
      background: rgba(255, 255, 255, 0.95);
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
      width: 90%;
      max-width: 1000px;
      gap: 30px;
    }

    .order-container, .product-list {
      flex: 1;
    }

    .order-container {
      animation: floaty 6s ease-in-out infinite;
    }

    @keyframes floaty {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }

    h2 {
      margin-bottom: 25px;
      color: #333;
    }

    input, textarea {
      width: 100%;
      padding: 12px;
      margin-bottom: 18px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      transition: 0.3s ease;
    }

    input:focus, textarea:focus {
      border-color: #007bff;
      box-shadow: 0 0 12px rgba(0, 123, 255, 0.4);
      outline: none;
    }

    input[type="file"] {
      padding: 0;
    }

    button {
      width: 100%;
      padding: 12px;
      background: #007bff;
      border: none;
      color: white;
      font-weight: bold;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s ease;
    }

    button:hover {
      background: #0056b3;
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(0, 123, 255, 0.5);
    }

    .product-list {
      background: #f8f8f8;
      padding: 20px;
      border-radius: 12px;
      overflow-y: auto;
      max-height: 500px;
    }

    .product-item {
      display: flex;
      align-items: center;
      background: white;
      padding: 10px 15px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 12px;
    }

    .product-item img {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 10px;
      margin-right: 15px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .product-item-details h4 {
      margin: 0 0 5px;
      color: #333;
    }

    .product-item-details p {
      margin: 0;
      font-size: 14px;
      color: #666;
    }
    .button-group {
  display: flex;
  gap: 15px;
  margin-top: 15px;
  flex-wrap: wrap;
}
  </style>
</head>
<body>
  <div class="container">
    <!-- 左边表单 -->
  <div class="order-container">
  <h2>Order Stock</h2>
  <form action="stock_order.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="item_name" placeholder="Item Name" required>
    <input type="number" name="quantity" placeholder="Quantity" min="1" required>
    <input type="text" name="item_code" placeholder="Item code" required>
    <textarea name="note" placeholder="Additional Notes (optional)" rows="3"></textarea>
    <input type="file" name="image" accept="image/jpeg, image/png" id="imageUpload">
   
    
    <!-- 按钮包裹容器 -->
    <div class="button-group">
      <button type="submit" class="add-button">Add Item</button>
      <button type="button" class="add-button" onclick="location.href='stock_manage.php'">Go back</button>
    </div>
  </form>
</div>


    <!-- 右边商品列表 -->
    <div class="product-list">
      <h2>Available Stocks</h2>

      
    </div>
  </div>
</body>
</html>