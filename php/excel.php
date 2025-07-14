<?php
include_once('db.php');

// 获取用户选择的年份（默认为当前年）
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// 设置 HTTP header 以导出为 Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Stock_Out_Report_$selected_year.xls");
header("Pragma: no-cache");
header("Expires: 0");

// 查询销售数据（按月份与 item_name 分组）
$sales_sql = "
  SELECT 
    DATE_FORMAT(s.date, '%M %Y') AS month,
    i.item_name,
    SUM(s.quantity) AS total_quantity,
    SUM(s.quantity * IFNULL(s.unit_price, 0)) AS total_sales
  FROM wmsstock_out s
  INNER JOIN wmsitem i ON s.item_id = i.item_id
  WHERE YEAR(s.date) = ?
  GROUP BY MONTH(s.date), s.item_id
  ORDER BY MONTH(s.date), s.item_id
";

$stmt = mysqli_prepare($conn, $sales_sql);
if (!$stmt) {
    die("SQL prepare failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "i", $selected_year);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 输出表格头
echo "<table border='1'>";
echo "<tr>
        <th>Month</th>
        <th>Item Name</th>
        <th>Total Quantity</th>
        <th>Total Sales (RM)</th>
      </tr>";

// 输出表格内容
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['month']) . "</td>";
    echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
    echo "<td>" . $row['total_quantity'] . "</td>";
    echo "<td>RM " . number_format($row['total_sales'], 2) . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
