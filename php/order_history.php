<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order History</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #ff9a9e, #fad0c4, #fbc2eb, #a18cd1);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
      padding: 40px 20px;
      min-height: 100vh;
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
      max-width: 900px;
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
      transition: transform 0.3s ease;
    }

    .order-card:hover {
      transform: translateY(-4px);
    }

    .order-field {
      flex: 1 1 45%;
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
      display: inline-block;
      width: fit-content;
    }

    .completed {
      background-color: #d4edda;
      color: #155724;
    }

    .pending {
      background-color: #fff3cd;
      color: #856404;
    }

    .cancelled {
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
    <div class="controls">
      <input type="text" id="searchInput" placeholder="Search Item Name">
      <select id="statusFilter">
        <option value="">All Status</option>
        <option value="Completed">Completed</option>
        <option value="Pending">Pending</option>
        <option value="Cancelled">Cancelled</option>
      </select>
      <button onclick="history.back()" class="go-back-btn">Go Back</button>
    </div>
  </div>

  <div class="order-list" id="orderList">
    <!-- Order cards will be inserted here by JS -->
  </div>

  <script>
    const orders = [
      { id: '1001', name: 'Wireless Mouse', quantity: 2, date: '2025-07-01', status: 'Completed' },
      { id: '1002', name: 'Bluetooth Speaker', quantity: 1, date: '2025-07-01', status: 'Pending' },
      { id: '1003', name: 'USB-C Cable', quantity: 5, date: '2025-06-30', status: 'Cancelled' },
      { id: '1004', name: 'Laptop Stand', quantity: 3, date: '2025-06-29', status: 'Completed' },
      { id: '1005', name: 'Keyboard', quantity: 2, date: '2025-06-28', status: 'Pending' }
    ];

    const orderList = document.getElementById('orderList');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');

    function renderOrders(data) {
      orderList.innerHTML = '';
      data.forEach(order => {
        const card = document.createElement('div');
        card.className = 'order-card';
        card.innerHTML = `
          <div class="order-field">
            <label>Order ID</label>
            <input type="text" value="${order.id}" readonly>
          </div>
          <div class="order-field">
            <label>Item Name</label>
            <input type="text" value="${order.name}" readonly>
          </div>
          <div class="order-field">
            <label>Quantity</label>
            <input type="text" value="${order.quantity}" readonly>
          </div>
          <div class="order-field">
            <label>Order Date</label>
            <input type="text" value="${order.date}" readonly>
          </div>
          <div class="order-field">
            <label>Status</label>
            <span class="status-badge ${order.status.toLowerCase()}">${order.status}</span>
          </div>
        `;
        orderList.appendChild(card);
      });
    }

    function filterOrders() {
      const keyword = searchInput.value.toLowerCase();
      const status = statusFilter.value;
      const filtered = orders.filter(order => {
        const matchName = order.name.toLowerCase().includes(keyword);
        const matchStatus = status === '' || order.status === status;
        return matchName && matchStatus;
      });
      renderOrders(filtered);
    }

    renderOrders(orders);
    searchInput.addEventListener('input', filterOrders);
    statusFilter.addEventListener('change', filterOrders);
  </script>

</body>
</html>
