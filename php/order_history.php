<!DOCTYPE html>
<html>
<head>
    <title>Order History</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: linear-gradient(270deg, red, orange, yellow, green, blue, indigo, violet);
    background-size: 1400% 1400%;
    animation: rainbowBG 15s ease infinite;
}

@keyframes rainbowBG {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.container {
    max-width: 900px;
    margin: auto;
    padding: 20px;
    margin-top: 30px;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
    border-radius: 20px;
    color: #000;
}

h2 {
  font-size: 28px;
  text-align: center;
  background: linear-gradient(to right, red, orange, yellow, green, blue, indigo, violet);
  background-size: 300% 300%;
  background-position: 0% 50%;
  -webkit-text-fill-color: transparent;
  animation: rainbowText 5s linear infinite;
}

@keyframes rainbowText {
  0% {
    background-position: 0% 50%;
  }
  100% {
    background-position: 100% 50%;
  }
}

input[type="text"],
input[type="password"] {
    padding: 10px;
    width: 80%;
    margin-bottom: 12px;
    border: 2px solid #ccc;
    border-radius: 8px;
    outline: none;
    transition: border-color 0.3s ease;
    color: #000;
}

input[type="text"]:focus,
input[type="password"]:focus {
    border-color: #888;
}

button,
.btn {
    padding: 10px 20px;
    background-color: #444;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s, background-color 0.3s;
    text-decoration: none;
    display: inline-block;
    font-weight: bold;
    text-align: center;
}

button:hover,
.btn:hover {
    transform: scale(1.05);
    background-color: #000;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    color: #000;
}

th {
    background: black;
    color: white;
    font-weight: bold;
    background-size: 300% 100%;
    animation: rainbowBG 8s ease infinite;
}

td {
    background-color: #fff;
    color: #000;
}

th, td {
    border: 1px solid #ddd;
    padding: 14px;
    text-align: center;
}

img {
    max-width: 100px;
    height: auto;
    border-radius: 10px;
}
    </style>
</head>
<body>
<div class="container">
    <h2>📜 Order History (Check-In / Check-Out)</h2>
    <a href="stock_view.html" class="btn">← Back to Stock</a>
    <a href="login.html" class="btn">Logout</a>
    <table>
        <tr>
            <th>Timestamp</th><th>Type</th><th>SKU</th><th>Name</th><th>Quantity</th>
        </tr>
        <tr>
            <td>2025-06-11 12:00:00</td>
            <td>CHECK-IN</td>
            <td>SKU123</td>
            <td>Item Name</td>
            <td>5</td>
        </tr>
    </table>
</div>
</body>
</html>