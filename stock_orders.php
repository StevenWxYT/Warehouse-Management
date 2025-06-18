<?php
include 'php/function.php';

$db = new DBConn();
$user = new DBFunc($db->conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sku = $_POST['sku'];
    $rack = $_POST['rack'];
    $zone = $_POST['zone'];
    $quantity = $_POST['quantity'];

    // Handle image upload
    $imageName = $_FILES['image']['name'];
    $imageTmp = $_FILES['image']['tmp_name'];
    $imagePath = "images/" . basename($imageName);

    if (move_uploaded_file($imageTmp, $imagePath)) {
        // Insert into DB
        $id = null; // auto-increment or not used
        if ($user->insertWarehouse($id, $imagePath, $sku, $rack, $zone, $quantity)) {
            $message = "Item added successfully!";
        } else {
            $message = "Database insert failed.";
        }
    } else {
        $message = "Image upload failed.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order New Stock</title>
    <style>
        body {
            /* background: linear-gradient(270deg, red, orange, yellow, green, blue, indigo, violet);
            background-size: 1400% 1400%;
            animation: rainbowBG 20s ease infinite; */
            font-family: Arial, sans-serif;
            text-align: center;
            color: white;
            background: url('bg/images (1).jpg') no-repeat center center fixed;
            background-size: cover;
        }

        /* @keyframes rainbowBG {
            0% {background-position:0% 50%}
            50% {background-position:100% 50%}
            100% {background-position:0% 50%}
        } */

        .form-container {
            margin-top: 100px;
            background: rgba(0, 0, 0, 0.4);
            padding: 20px;
            border-radius: 20px;
            display: inline-block;
        }

        input, select {
            padding: 10px;
            margin: 10px;
            border-radius: 10px;
            border: none;
            width: 250px;
        }

        input[type="submit"] {
            background-color: white;
            color: black;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: lightgray;
        }

        .message {
            margin-top: 20px;
            font-weight: bold;
        }

    </style>
</head>
<body>

    <div class="form-container">
        <h2>Order New Stock</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="text" name="sku" placeholder="SKU" required><br>
            <input type="text" name="rack" placeholder="Rack" required><br>
            <input type="text" name="zone" placeholder="Zone" required><br>
            <input type="number" name="quantity" placeholder="Quantity" required><br>
            <input type="file" name="image" accept="image/*" required><br>
            <input type="submit" value="Submit">
        </form>

        <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>
    </div>

</body>
</html>
