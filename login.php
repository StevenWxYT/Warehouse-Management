<?php
session_start();

include_once 'php/function.php';

if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

$db = new DBConn();
$func = new DBFunc($db->conn);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($func->loginUser($username, $password)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $message = "❌ Username or password incorrect";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        /* Video full screen */
        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
        }
        body {
            font-family: Arial, sans-serif;
            /* background: linear-gradient(120deg, #a1c4fd, #c2e9fb, #d4fc79, #96e6a1);
            background-size: 400% 400%;
            animation: gradientFlow 18s ease infinite; */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        @keyframes gradientFlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        form {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            width: 320px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            text-align: center;
        }

        input {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            font-size: 16px;
        }

        button {
            width: 95%;
            padding: 12px;
            font-size: 18px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <!-- 🎬 VIDEO BACKGROUND -->
<video autoplay muted loop id="bg-video">
    <source src="bg/FPV Drone Flight through Beautiful Iceland Canyon.mp4" type="video/mp4">
    Your browser does not support HTML5 video.
</video>
<form method="POST" action="">
    <h2>Login</h2>
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
</form>
</body>
</html>
