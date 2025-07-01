<?php
require_once 'db.php';

// Fetch order history: joins wmsitem_log with wmsitem
$sql = "SELECT l.*, i.item_name 
        FROM wmsitem_log l 
        JOIN wmsitem i ON l.item_id = i.item_id 
        ORDER BY l.date DESC, l.time DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Stock In/Out History</h2>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Status</th>
                <th>Date</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php $i = 1; while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($row['item_name']) ?></td>
                        <td>
                            <?php if ($row['status'] === 'in'): ?>
                                <span class="badge bg-success">IN</span>
                            <?php elseif ($row['status'] === 'out'): ?>
                                <span class="badge bg-danger">OUT</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Unknown</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['date'] ?></td>
                        <td><?= $row['time'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
