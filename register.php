<?php
include 'db.php'; // ✅ Make sure db.php is in the same folder

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape and sanitize input
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into users table
    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";

    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>Registration successful!</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body {
            background: #f2f2f2;
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
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<form method="POST" action="">
    <h2>Register</h2>
    <input type="text" name="username" required placeholder="Enter Username">
    <input type="password" name="password" required placeholder="Enter Password">
    <button type="submit">Register</button>
</form>

</body>
</html>
