<?php
include("db.php");
include("function.php");

$db = new DBConn();
$user = new DBFunc($db->conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (!empty($username) && !empty($password) && !empty($role)) {
        $user->registerUser($username, $password, $role);
    } else {
        echo "Error: Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(120deg, #a1c4fd, #c2e9fb, #d4fc79, #96e6a1);
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

        form {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 300px;
            text-align: center;
            animation: floaty 6s ease-in-out infinite;
        }

        @keyframes floaty {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 12px rgba(76, 175, 80, 0.5);
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #0056b3;
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0, 123, 255, 0.6);
        }

        a {
            display: block;
            margin-top: 15px;
            font-size: 14px;
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <main class="form">
        <form action="register.php" method="POST">
            <label for="username">Username</label>
            <input type="text" name="username" required><br>

            <label for="password">Password</label>
            <input type="password" name="password" required><br>

            <label for="role">Select a role:</label>
            <select name="role" required>
                <option value="">--Choose Role--</option>
                <option value="admin">Admin</option>
                <option value="salesman">Salesman</option>
            </select><br>

            <button type="submit">Register</button>
        </form>
    </main>
</body>
</html>
