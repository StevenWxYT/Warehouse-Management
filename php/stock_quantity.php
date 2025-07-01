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
      min-height: 100vh;
      display: flex;
      align-items: flex-start;
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
      max-width: 1200px;
      width: 100%;
      background-color: #ffffffcc;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      backdrop-filter: blur(8px);
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 20px;
    }

    h1 {
      font-size: 32px;
      color: #333;
    }

    .category-input {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .category-input label {
      font-size: 16px;
      font-weight: 600;
      color: #333;
    }

    .category-select {
      padding: 8px 12px;
      border-radius: 8px;
      border: 2px solid #ccc;
      background-color: #fafafa;
      font-size: 14px;
      color: #333;
      width: 180px;
    }

    .go-back-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(to right, #6a11cb, #2575fc);
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .go-back-btn:hover {
      transform: translateY(-2px);
      background: linear-gradient(to right, #7b2ff7, #1c92d2);
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    }

    .item-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
    }

    .item-card {
      display: flex;
      flex-direction: column;
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .item-left {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      margin-bottom: 15px;
    }

    .item-left input[type="file"] {
      font-size: 14px;
    }

    .item-left img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 10px;
    }

    .item-info label {
      font-weight: 600;
      font-size: 16px;
      display: block;
      margin-bottom: 6px;
    }

    .item-info input.qty-input {
      width: 100px;
      padding: 8px 10px;
      border: 2px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
      text-align: center;
      background-color: #fafafa;
    }

    .actions {
      display: flex;
      justify-content: flex-start;
      gap: 12px;
    }

    .btn {
      padding: 8px 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      color: white;
      transition: background-color 0.2s ease;
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
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Stock Quantity</h1>
      <div class="category-input">
        <button onclick="history.back()" class="go-back-btn">← Go Back</button>
        <label for="global-category">Category:</label>
        <select id="global-category" class="category-select">
          <option value="Stationery">Stationery</option>
          <option value="Electronics">Electronics</option>
          <option value="Office Supply">Office Supply</option>
          <option value="Others">Others</option>
        </select>
      </div>
    </div>

    <!-- Item Grid -->
    <div class="item-grid">
      <!-- Item 1 -->
      <div class="item-card">
        <div class="item-left">
          <input type="file" accept="image/*" onchange="previewImage(event, this)">
          <img src="https://via.placeholder.com/120?text=NB" alt="Notebook">
          <div class="item-info">
            <label>ITM001</label>
            <input type="number" value="120" class="qty-input">
          </div>
        </div>
        <div class="actions">
          <button class="btn update-btn">Update</button>
          <button class="btn delete-btn">Delete</button>
        </div>
      </div>

      <!-- Item 2 -->
      <div class="item-card">
        <div class="item-left">
          <input type="file" accept="image/*" onchange="previewImage(event, this)">
          <img src="https://via.placeholder.com/120?text=Pen" alt="Pen">
          <div class="item-info">
            <label>ITM002</label>
            <input type="number" value="250" class="qty-input">
          </div>
        </div>
        <div class="actions">
          <button class="btn update-btn">Update</button>
          <button class="btn delete-btn">Delete</button>
        </div>
      </div>

      <!-- Item 3 -->
      <div class="item-card">
        <div class="item-left">
          <input type="file" accept="image/*" onchange="previewImage(event, this)">
          <img src="https://via.placeholder.com/120?text=Marker" alt="Marker">
          <div class="item-info">
            <label>ITM003</label>
            <input type="number" value="75" class="qty-input">
          </div>
        </div>
        <div class="actions">
          <button class="btn update-btn">Update</button>
          <button class="btn delete-btn">Delete</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function previewImage(event, input) {
      const file = input.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = input.nextElementSibling;
          img.src = e.target.result;
          input.style.display = "none";
        };
        reader.readAsDataURL(file);
      }
    }

    document.addEventListener("click", function(e) {
      const target = e.target;

      if (target.classList.contains("update-btn")) {
        const item = target.closest(".item-card");
        const label = item.querySelector("label").textContent;
        const qty = item.querySelector(".qty-input").value;
        const globalCategory = document.getElementById("global-category").value || "N/A";
        alert(`Quantity for item code "${label}" updated to ${qty}\nCategory: ${globalCategory}`);
      }

      if (target.classList.contains("delete-btn")) {
        const item = target.closest(".item-card");
        const label = item.querySelector("label").textContent;
        if (confirm(`Are you sure you want to delete item "${label}"?`)) {
          item.remove();
        }
      }
    });
  </script>
</body>
</html>
