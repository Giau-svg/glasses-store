<?php
require '../check_admin_login.php';
require '../root.php';

$max_date = $_GET['days'] ?? 30;

// Sửa lại câu truy vấn để lấy doanh thu chính xác hơn
$sql = "SELECT DATE_FORMAT(order_date, '%e-%m') as days,
        SUM(total_amount) as total
        FROM `orders` 
        WHERE order_date >= CURDATE() - INTERVAL $max_date DAY 
        AND order_status = 'delivered'
        GROUP BY DATE_FORMAT(order_date, '%e-%m')
        ORDER BY STR_TO_DATE(days, '%e-%m') ASC";
$result = mysqli_query($connect, $sql);

// Kiểm tra kết nối SQL
if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($connect));
}

$arr = [];
$today = date('d');
$this_month = date('m');
$this_year = date('Y');

// Tạo mảng dữ liệu cho 30 ngày qua, khởi tạo giá trị 0 cho mỗi ngày
for ($i = $max_date - 1; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $day = date('j', strtotime($date));
    $month = date('m', strtotime($date));
    $key = $day . '-' . $month;
    $arr[$key] = 0;
}

// Điền dữ liệu thực từ cơ sở dữ liệu
foreach ($result as $each) {
    $arr[$each['days']] = (int)$each['total'];
}

// Trả về dữ liệu dạng JSON
echo json_encode($arr);