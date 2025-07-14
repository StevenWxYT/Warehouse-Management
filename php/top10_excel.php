<?php
include_once('db.php');

// 获取选中的月份
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// 设置 HTTP 头部为 Excel 格式
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=top10_stock_out_$selected_month.xls");
header("Pragma: no-cache");
header("Expires: 0");

// 查询该月 Top 10 出库数据
$sql = "
  SELECT 
    i.item_name, 
    i.unit_price,
    SUM(s.quantity) as total_sold
  FROM wmsstock_out s
  INNER JOIN wmsitem i ON s.item_id = i.item_id
  WHERE DATE_FORMAT(s.date, '%Y-%m') = ?
  GROUP BY s.item_id
  ORDER BY total_sold DESC
  LIMIT 10
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_month);
$stmt->execute();
$result = $stmt->get_result();

// 输出 HTML 表格作为 Excel 内容
echo "<table border='1'>";
echo "<tr><th colspan='3'>Top 10 Best Stock Out - " . date('F Y', strtotime($selected_month . '-01')) . "</th></tr>";
echo "<tr>
        <th>Item Name</th>
        <th>Total Quantity</th>
        <th>Unit Price (RM)</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . htmlspecialchars($row['item_name']) . "</td>
            <td>" . $row['total_sold'] . "</td>
            <td>" . number_format($row['unit_price'], 2) . "</td>
          </tr>";
}

echo "</table>";
?>
