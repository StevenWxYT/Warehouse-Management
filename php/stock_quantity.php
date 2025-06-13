<?php
include_once('db.php');



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
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px;
      background: linear-gradient(-45deg, #ff9a9e, #fad0c4, #fbc2eb, #a18cd1);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
    }

    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .container {
      max-width: 1100px;
      width: 100%;
      min-height: 650px;
      background-color: #ffffffcc;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      backdrop-filter: blur(8px);
    }

    h1 {
      text-align: center;
      margin-bottom: 30px;
      font-size: 32px;
      color: #333;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
      border-radius: 12px;
      overflow: hidden;
    }

    thead {
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    th {
      background:  #d0d3d4 ;
      color: black;
      font-size: 16px;
      font-weight: 600;
      padding: 16px;
      border-bottom: 2px solid #ccc;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
      vertical-align: middle;
    }

    tr:hover {
      background-color: #f9f9f9;
    }

    td img {
      width: 50px;
      height: 50px;
      object-fit: cover;
      border-radius: 8px;
    }

    .btn {
      padding: 6px 10px;
      margin-right: 5px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.2s ease;
      color: white;
      font-size: 14px;
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

    .qty-controls {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .qty {
      min-width: 30px;
      text-align: center;
      font-weight: 600;
    }

    .qty-btn {
      background-color: #6c63ff;
      border: none;
      color: white;
      font-weight: bold;
      border-radius: 6px;
      width: 28px;
      height: 28px;
      cursor: pointer;
      font-size: 18px;
      transition: background-color 0.2s ease;
    }

    .qty-btn:hover {
      background-color: #574fd6;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Stock Quantity</h1>

    <table id="stock-table">
      <thead>
        <tr>
          <th>Image</th>
          <th>Item Name</th>
          <th>Quantity</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><img src="https://via.placeholder.com/50?text=NB" alt="Notebook"></td>
          <td>Notebook</td>
          <td>
            <div class="qty-controls">
              <button class="qty-btn minus">−</button>
              <span class="qty">120</span>
              <button class="qty-btn plus">+</button>
            </div>
          </td>
          <td>
            <button class="btn update-btn">Update</button>
            <button class="btn delete-btn">Delete</button>
          </td>
        </tr>
        <tr>
          <td><img src="https://via.placeholder.com/50?text=Pen" alt="Pen"></td>
          <td>Pen</td>
          <td>
            <div class="qty-controls">
              <button class="qty-btn minus">−</button>
              <span class="qty">250</span>
              <button class="qty-btn plus">+</button>
            </div>
          </td>
          <td>
            <button class="btn update-btn">Update</button>
            <button class="btn delete-btn">Delete</button>
          </td>
        </tr>
        <tr>
          <td><img src="https://via.placeholder.com/50?text=Marker" alt="Marker"></td>
          <td>Marker</td>
          <td>
            <div class="qty-controls">
              <button class="qty-btn minus">−</button>
              <span class="qty">75</span>
              <button class="qty-btn plus">+</button>
            </div>
          </td>
          <td>
            <button class="btn update-btn">Update</button>
            <button class="btn delete-btn">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <script>
    document.addEventListener("click", function(e) {
      const target = e.target;

      if (target.classList.contains("update-btn")) {
        const row = target.closest("tr");
        const qtyElement = row.querySelector(".qty");
        const currentQty = qtyElement.textContent;
        const newQty = prompt("Enter new quantity:", currentQty);
        if (newQty !== null && !isNaN(newQty) && Number(newQty) >= 0) {
          qtyElement.textContent = Number(newQty);
        }
      }

      if (target.classList.contains("delete-btn")) {
        const row = target.closest("tr");
        const itemName = row.querySelector("td:nth-child(2)").textContent;
        if (confirm(`Are you sure you want to delete "${itemName}"?`)) {
          row.remove();
        }
      }

      if (target.classList.contains("plus")) {
        const qtyEl = target.parentElement.querySelector(".qty");
        qtyEl.textContent = Number(qtyEl.textContent) + 1;
      }

      if (target.classList.contains("minus")) {
        const qtyEl = target.parentElement.querySelector(".qty");
        let value = Number(qtyEl.textContent);
        if (value > 0) {
          qtyEl.textContent = value - 1;
        }
      }
    });
  </script>
</body>
</html>
