<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            header("Location: dashboard.php"); // Redirect to dashboard or home
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            background: #f1f1f1;
            font-family: Arial;
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
        }
        form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px #ccc;
            width: 300px;
        }
        input {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<form method="POST" action="">
    <h2>Login</h2>
    <?php if ($error != ""): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <input type="text" name="username" required placeholder="Enter Username">
    <input type="password" name="password" required placeholder="Enter Password">
    <button type="submit">Login</button>
</form>

</body>
</html>
