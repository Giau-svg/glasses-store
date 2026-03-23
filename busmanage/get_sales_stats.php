<?php
header('Content-Type: application/json');
require 'root.php';

// Lấy ngày đầu và cuối của tháng hiện tại
$first_day = date('Y-m-01');
$last_day = date('Y-m-t');

// Lấy dữ liệu doanh thu trong tháng hiện tại
$stmt = $connect->prepare("
    SELECT DATE(order_date) as date, SUM(total_amount) as total 
    FROM orders 
    WHERE order_date BETWEEN ? AND ? AND order_status = 'delivered' 
    GROUP BY DATE(order_date)
");
$stmt->bind_param("ss", $first_day, $last_day);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['date']] = (int)$row['total'];
}

// Điền dữ liệu cho tất cả các ngày trong tháng (nếu không có dữ liệu thì trả về 0)
$start = new DateTime($first_day);
$end = new DateTime($last_day);
$interval = new DateInterval('P1D');
$period = new DatePeriod($start, $interval, $end->modify('+1 day'));

$filled_data = [];
foreach ($period as $date) {
    $date_str = $date->format('Y-m-d');
    $filled_data[$date_str] = isset($data[$date_str]) ? $data[$date_str] : 0;
}

echo json_encode($filled_data);
$stmt->close();
?>