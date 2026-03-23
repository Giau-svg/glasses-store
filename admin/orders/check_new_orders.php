<?php
require '../check_admin_login.php';
require '../root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Thiết lập header để trả về JSON
header('Content-Type: application/json');

// Đếm số đơn hàng mới (tạo trong 1 giờ gần đây và trạng thái chờ xử lý)
$sqlNewOrders = "SELECT COUNT(*) as new_orders FROM orders 
                WHERE order_status = 'pending' 
                AND order_date >= NOW() - INTERVAL 1 HOUR";
$resultNewOrders = mysqli_query($connect, $sqlNewOrders);

if (!$resultNewOrders) {
    // Có lỗi khi truy vấn
    echo json_encode([
        'success' => false,
        'error' => mysqli_error($connect),
        'new_orders' => 0
    ]);
    exit;
}

$newOrdersCount = mysqli_fetch_assoc($resultNewOrders)['new_orders'];

// Lấy thời gian đơn hàng mới nhất
$sqlLatestOrder = "SELECT MAX(order_date) as latest_order FROM orders";
$resultLatestOrder = mysqli_query($connect, $sqlLatestOrder);
$latestOrderTime = mysqli_fetch_assoc($resultLatestOrder)['latest_order'];

// Trả về kết quả JSON
echo json_encode([
    'success' => true,
    'new_orders' => (int)$newOrdersCount,
    'latest_order_time' => $latestOrderTime,
    'check_time' => date('Y-m-d H:i:s')
]); 