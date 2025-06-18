<?php
include 'php/function.php';

$db = new DBConn();
$warehouse = new DBFunc($db->conn);

$data = $warehouse->getAllWarehouse();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Stock Quantity</title>
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
            /* background: linear-gradient(270deg, red, orange, yellow, green, blue, indigo, violet);
            background-size: 1400% 1400%;
            animation: rainbowBG 20s ease infinite; */
            background: url('bg/job-application-form-isolated-on-white-background-DT4P55.jpg') no-repeat center center fixed;
            background-size: cover;
        }

        /* @keyframes rainbowBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        } */

        table {
            margin: auto;
            border-collapse: collapse;
            width: 90%;
            background: white;
        }

        th, td {
            border: 1px solid #999;
            padding: 10px;
        }

        th {
            background: #333;
            color: white;
        }
    </style>
</head>
<body>
    <h1>View Stock Quantity</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>SKU</th>
            <th>Rack</th>
            <th>Zone</th>
            <th>Quantity</th>
        </tr>
        <?php foreach ($data as $item): ?>
            <tr>
                <td><?= $item['id'] ?></td>
                <td><img src="<?= $item['image'] ?>" width="50"></td>
                <td><?= $item['sku'] ?></td>
                <td><?= $item['rack'] ?></td>
                <td><?= $item['zone'] ?></td>
                <td><?= $item['quantity'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
