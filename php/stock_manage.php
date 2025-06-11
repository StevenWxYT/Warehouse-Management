<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manage Stock</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(-45deg, #f6d365, #fda085, #ff9a9e, #fad0c4);
      background-size: 400% 400%;
      animation: gradientBG 12s ease infinite;
      padding: 40px 20px;
      display: flex;
      justify-content: center;
    }

    @keyframes gradientBG {
      0% {
        background-position: 0% 50%;
      }
      50% {
        background-position: 100% 50%;
      }
      100% {
        background-position: 0% 50%;
      }
    }

    .stock-container {
      width: 100%;
      max-width: 1300px;
      background: #fff;
      padding: 40px;
      border-radius: 24px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 30px;
    }

    .controls {
      display: flex;
      justify-content: flex-start;
      gap: 15px;
      margin-bottom: 30px;
      flex-wrap: wrap;
      align-items: center;
    }

    .controls input,
    .controls select {
      padding: 12px;
      border-radius: 10px;
      border: 1px solid #ccc;
      font-size: 15px;
      width: 100%;
      max-width: 300px;
      transition: box-shadow 0.3s ease;
    }

    .controls input:hover {
      box-shadow: 0 0 8px #fda085;
    }

    .add-button {
      padding: 12px 20px;
      background-color: #ff7e5f;
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    .add-button:hover {
      background-color: #eb5e3b;
      transform: scale(1.05);
    }

    .stock-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
    }

    .stock-card {
      background: #f9f9f9;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .stock-card:hover {
      transform: translateY(-6px);
    }

    .stock-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    .stock-info {
      padding: 15px 20px;
    }

    .stock-info h3 {
      margin: 0 0 10px;
      color: #333;
      font-size: 20px;
    }

    .stock-info p {
      margin: 5px 0;
      font-size: 14px;
      color: #555;
    }

    .hidden {
      display: none !important;
    }
  </style>
</head>
<body>
  <div class="stock-container">
    <h2>Manage Stock</h2>

    <div class="controls">
      <button class="add-button" onclick="location.href='stock_order.php'">Add Item</button>
      <select id="categoryFilter">
        <option value="all">All Categories</option>
        <option value="Phone">Phone</option>
        <option value="Laptop">Laptop</option>
        <option value="Camera">Camera</option>
        <option value="Accessory">Accessory</option>
      </select>
      <input type="text" id="searchInput" placeholder="Search by name or code...">
    </div>

    <div class="stock-grid" id="stockGrid">
      <!-- Stock Cards (same as before) -->
      <div class="stock-card" data-category="Phone">
        <img src="cat1.jpeg" alt="iPhone 14">
        <div class="stock-info">
          <h3>Apple iPhone 14</h3>
          <p><strong>Quantity:</strong> 25</p>
          <p><strong>Item Code:</strong> IPH14-001</p>
          <p><strong>Time:</strong> 10:30 AM</p>
          <p><strong>Date:</strong> 2025-06-11</p>
        </div>
      </div>

      <div class="stock-card" data-category="Phone">
        <img src="https://via.placeholder.com/400x180?text=GalaxyS23" alt="Galaxy S23">
        <div class="stock-info">
          <h3>Samsung Galaxy S23</h3>
          <p><strong>Quantity:</strong> 18</p>
          <p><strong>Item Code:</strong> SAMS23-002</p>
          <p><strong>Time:</strong> 09:45 AM</p>
          <p><strong>Date:</strong> 2025-06-11</p>
        </div>
      </div>

      <div class="stock-card" data-category="Laptop">
        <img src="https://via.placeholder.com/400x180?text=DellXPS13" alt="Dell XPS 13">
        <div class="stock-info">
          <h3>Dell XPS 13 Laptop</h3>
          <p><strong>Quantity:</strong> 10</p>
          <p><strong>Item Code:</strong> DELLXPS13-003</p>
          <p><strong>Time:</strong> 11:00 AM</p>
          <p><strong>Date:</strong> 2025-06-11</p>
        </div>
      </div>

      <div class="stock-card" data-category="Accessory">
        <img src="https://via.placeholder.com/400x180?text=Mouse" alt="Mouse">
        <div class="stock-info">
          <h3>Logitech Mouse</h3>
          <p><strong>Quantity:</strong> 55</p>
          <p><strong>Item Code:</strong> LOGI-MSE-004</p>
          <p><strong>Time:</strong> 12:15 PM</p>
          <p><strong>Date:</strong> 2025-06-11</p>
        </div>
      </div>

      <div class="stock-card" data-category="Camera">
        <img src="https://via.placeholder.com/400x180?text=Canon+Camera" alt="Canon Camera">
        <div class="stock-info">
          <h3>Canon DSLR Camera</h3>
          <p><strong>Quantity:</strong> 6</p>
          <p><strong>Item Code:</strong> CANON-DSLR-005</p>
          <p><strong>Time:</strong> 01:30 PM</p>
          <p><strong>Date:</strong> 2025-06-11</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const cards = document.querySelectorAll('.stock-card');

    function filterStock() {
      const keyword = searchInput.value.toLowerCase();
      const category = categoryFilter.value;

      cards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const code = card.querySelector('p').textContent.toLowerCase();
        const cardCategory = card.dataset.category;

        const matchesSearch = title.includes(keyword) || code.includes(keyword);
        const matchesCategory = category === "all" || cardCategory === category;

        card.classList.toggle('hidden', !(matchesSearch && matchesCategory));
      });
    }

    searchInput.addEventListener('input', filterStock);
    categoryFilter.addEventListener('change', filterStock);
  </script>
</body>
</html>
