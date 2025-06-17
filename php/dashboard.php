<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard</title>
  <style>
    /* 🌈 背景渐变动画 */
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      height: 100vh;
      background: linear-gradient(-45deg, #a1c4fd, #c2e9fb, #d4fc79, #96e6a1);
      background-size: 400% 400%;
      animation: gradientFlow 18s ease infinite;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    @keyframes gradientFlow {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* 📦 中央容器 */
    .dashboard {
      display: grid;
      gap: 20px;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 15px 30px rgba(0,0,0,0.2);
      max-width: 900px;
      width: 90%;
      text-align: center;
      animation: floaty 8s ease-in-out infinite;
    }

    @keyframes floaty {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }

    /* 🟦 按钮卡片风格 */
    .dashboard button {
      background: #007bff;
      color: white;
      border: none;
      padding: 30px 20px;
      font-size: 18px;
      font-weight: bold;
      border-radius: 16px;
      box-shadow: 0 5px 15px rgba(0, 123, 255, 0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
      height: 150px;
    }

    .dashboard button:hover {
      transform: translateY(-5px) scale(1.05);
      box-shadow: 0 10px 25px rgba(0, 123, 255, 0.4);
    }

    /* 🎨 按钮文字换行 */
    .dashboard button br {
      display: none;
    }

    /* 📱 小屏幕优化 */
    @media (max-width: 500px) {
      .dashboard {
        grid-template-columns: 1fr;
      }
    }

    /* Toast 样式 */
    #toast {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background-color: #4CAF50;
      color: white;
      padding: 16px 24px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      opacity: 0;
      transition: opacity 0.5s ease, transform 0.5s ease;
      transform: translateY(20px);
      z-index: 1000;
    }
  </style>
</head>
<body>
  <main class="dashboard">
    <button onclick="window.location.href='stock_quantity.php'">📦 View Stock Quantity</button>
    <button onclick="window.location.href='order_history.php'">📜 View Order History</button>
    <button onclick="window.location.href='stock_order.php'">🛒 Order Stocks</button>
    <button onclick="window.location.href='stock_manage.php'">🛠️ Manage Stocks</button>
  </main>

  <!-- toast 提示 -->
  <div id="toast"></div>

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    const logoutSuccess = urlParams.get('logout');
    const loginSuccess = urlParams.get('login');
    const registerSuccess = urlParams.get('register');
    const toast = document.getElementById('toast');

    // 设置 toast 内容
    if (logoutSuccess === 'success') {
      toast.textContent = 'You have been logged out successfully.';
    } else if (loginSuccess === 'success') {
      toast.textContent = 'Welcome back user!';
    } else if (registerSuccess === 'success') {
      toast.textContent = 'Welcome new user!';
    }

    // 如果有任何内容，则显示 toast
    if (toast.textContent !== '') {
      toast.style.opacity = '1';
      toast.style.transform = 'translateY(0)';
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
      }, 3000);
    }
  </script>
</body>
</html>
