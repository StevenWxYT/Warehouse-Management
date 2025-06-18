<?php
session_start();
session_unset();       // Clear session
session_destroy();     // Ensure user is logged out

require_once 'php/function.php';

$db = new DBConn();
$user = new DBFunc($db->conn);
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if ($password !== $confirm_password) {
        $message = "❌ Passwords do not match!";
    } elseif ($user->userExists($username)) {
        $message = "❌ Username already exists!";
    } else {
        $success = $user->registerUser($username, $password, $role);
        if ($success) {
            header("Location: login.php");  // ✅ Redirect to login
            exit();
        } else {
            $message = "❌ Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
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
            /* background: linear-gradient(270deg, red, orange, yellow, green, blue, indigo, violet);
            background-size: 1400% 1400%;
            animation: rainbowBG 10s ease infinite; */
            /* background: url('your-background.gif') no-repeat center center fixed;
            background-size: cover; */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        @keyframes rainbowBG {
            0% {background-position: 0% 50%}
            50% {background-position: 100% 50%}
            100% {background-position: 0% 50%}
        }

        form {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            width: 320px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            text-align: center;
        }

        input, select {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            font-size: 16px;
        }

        button {
            padding: 12px;
            width: 95%;
            border: none;
            background-color: #333;
            color: white;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #555;
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
    <source src="bg/Best Travel Destinations in The World 4K.mp4" type="video/mp4">
    Your browser does not support HTML5 video.
</video>
<form method="post" action="">
    <h2>Register</h2>
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <input type="password" name="confirm_password" placeholder="Confirm Password" required />
    <select name="role" required>
        <option value="" disabled selected>Select Role</option>
        <option value="admin">Admin</option>
        <option value="salesman">Salesman</option>
    </select>
    <button type="submit">Register</button>
    <p>Already have an account? <a href="login.php">Login here</a></p>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
</form>
</body>
</html>
