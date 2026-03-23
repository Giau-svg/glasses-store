<?php
require '../check_admin_login.php'; 
require '../root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id = $_GET["id"] ?? 0; 
$status = $_GET["status"] ?? '';

if(empty($id) || empty($status)){
    header('location:index.php?error=Thiếu thông tin cập nhật');
    exit;
}

// Kiểm tra đơn hàng tồn tại
$check_order = mysqli_query($connect, "SELECT order_id, order_status FROM orders WHERE order_id = '$id'");
if (mysqli_num_rows($check_order) == 0) {
    header('location:index.php?error=Đơn hàng không tồn tại');
    exit;
}
$order = mysqli_fetch_assoc($check_order);

// Kiểm tra trạng thái hiện tại
$current_status = $order['order_status'];
if ($current_status == 'delivered' || $current_status == 'cancelled') {
    header('location:index.php?error=Không thể cập nhật đơn hàng đã hoàn thành hoặc đã hủy');
    exit;
}

// Kiểm tra trạng thái hợp lệ
$valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if(!in_array($status, $valid_statuses)) {
    header('location:index.php?error=Trạng thái không hợp lệ');
    exit;
}

// Kiểm tra quy trình cập nhật hợp lệ
$valid_flow = true;
if ($current_status == 'pending' && !in_array($status, ['processing', 'cancelled'])) {
    $valid_flow = false;
} else if ($current_status == 'processing' && !in_array($status, ['shipped', 'cancelled'])) {
    $valid_flow = false;
} else if ($current_status == 'shipped' && $status != 'delivered') {
    $valid_flow = false;
}

if (!$valid_flow) {
    header('location:index.php?error=Không thể cập nhật đơn hàng từ trạng thái ' . $current_status . ' sang ' . $status);
    exit;
}

// Thực hiện cập nhật trạng thái đơn hàng
$sql = "UPDATE orders 
    SET order_status = '$status'
    WHERE order_id = '$id'";

$query = mysqli_query($connect, $sql);
if ($query) {
    // Nếu đơn hàng được cập nhật sang trạng thái đã hủy, cần cập nhật lại số lượng trong kho
    if ($status == 'cancelled') {
        // Lấy thông tin chi tiết đơn hàng
        $sql_details = "SELECT product_id, quantity FROM order_details WHERE order_id = '$id'";
        $result_details = mysqli_query($connect, $sql_details);
        
        while ($detail = mysqli_fetch_assoc($result_details)) {
            $product_id = $detail['product_id'];
            $quantity = $detail['quantity'];
            
            // Cộng lại số lượng vào kho
            $sql_update_stock = "UPDATE products 
                                SET stock_quantity = stock_quantity + $quantity 
                                WHERE product_id = '$product_id'";
            mysqli_query($connect, $sql_update_stock);
        }
    }
    
    // Tạo thông báo thành công với mô tả trạng thái
    $status_text = '';
    switch ($status) {
        case 'processing':
            $status_text = 'đang xử lý';
            break;
        case 'shipped':
            $status_text = 'đang giao hàng';
            break;
        case 'delivered':
            $status_text = 'đã hoàn thành';
            break;
        case 'cancelled':
            $status_text = 'đã hủy';
            break;
        default:
            $status_text = $status;
    }
    
    header('location:index.php?success=Đơn hàng #' . $id . ' đã được cập nhật thành ' . $status_text);
} else {
    header('location:index.php?error=' . urlencode(mysqli_error($connect)));
}

mysqli_close($connect);
