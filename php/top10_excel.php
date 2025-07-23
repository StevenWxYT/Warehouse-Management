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

// 初始化总销额
$total_sales_all = 0;

// 输出 HTML 表格作为 Excel 内容
echo "<table border='1' style='border-collapse: collapse; font-size: 16px;'>";

// 表格标题
echo "<tr><th colspan='4' style='padding: 10px; background-color: #f2f2f2;'>Top 10 Best Stock Out - " . date('F Y', strtotime($selected_month . '-01')) . "</th></tr>";

// 表头
echo "<tr style='background-color: #d9e1f2;'>
        <th style='padding: 8px;'>Item Name</th>
        <th style='padding: 8px;'>Total Quantity</th>
        <th style='padding: 8px;'>Unit Price (RM)</th>
        <th style='padding: 8px;'>Total Sales (RM)</th>
      </tr>";

// 数据行
while ($row = $result->fetch_assoc()) {
    $item_name = htmlspecialchars($row['item_name']);
    $total_sold = $row['total_sold'];
    $unit_price = $row['unit_price'];
    $total_sales = $total_sold * $unit_price;
    $total_sales_all += $total_sales;

    echo "<tr>
            <td style='padding: 8px;'>$item_name</td>
            <td style='padding: 8px; text-align: center;'>$total_sold</td>
            <td style='padding: 8px; text-align: right;'>" . number_format($unit_price, 2) . "</td>
            <td style='padding: 8px; text-align: right;'>" . number_format($total_sales, 2) . "</td>
          </tr>";
}

// 汇总总销额
echo "<tr style='font-weight: bold; background-color: #f9f5d7;'>
        <td colspan='3' style='text-align: right; padding: 10px;'>Total Sales (RM):</td>
        <td style='text-align: right; padding: 10px;'>" . number_format($total_sales_all, 2) . "</td>
      </tr>";

echo "</table>";
?>
