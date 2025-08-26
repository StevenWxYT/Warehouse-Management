<?php
include_once('db.php');

$item_code = $_GET['item_code'] ?? '';

if (!$item_code) {
    echo json_encode(['status' => 'error', 'message' => 'No item code']);
    exit;
}

$stmt = $conn->prepare("SELECT item_name, category_id, unit_price, image_path FROM wmsitem WHERE item_code = ?");
$stmt->bind_param("s", $item_code);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['status' => 'success', 'data' => $row]);
} else {
    echo json_encode(['status' => 'not_found']);
}
$stmt->close();






