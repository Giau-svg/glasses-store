<?php
require_once '../check_sales_login.php';
require_once '../../admin/root.php';

// Kiểm tra nếu không có ID đơn hàng hoặc hành động
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header('Location: orders.php?error=Thiếu thông tin để xử lý đơn hàng');
    exit;
}

$order_id = $_GET['id'];
$action = $_GET['action'];
$staff_id = $_SESSION['staff_user_id'];
$staff_name = $_SESSION['staff_name'] ?? 'Nhân viên bán hàng';
$current_time = date('Y-m-d H:i:s');

// Lấy thông tin đơn hàng hiện tại
$sql = "SELECT * FROM orders WHERE order_id = '$order_id'";
$result = mysqli_query($connect, $sql);

if (mysqli_num_rows($result) == 0) {
    header('Location: orders.php?error=Không tìm thấy đơn hàng');
    exit;
}

$order = mysqli_fetch_assoc($result);
$current_status = $order['order_status'];
$new_status = '';
$status_message = '';

// Xử lý theo hành động
switch ($action) {
    case 'approve': // Xác nhận đơn hàng
        if ($current_status == 'pending') {
            $new_status = 'confirmed';
            $status_message = "Đơn hàng đã được xác nhận bởi $staff_name";
        } else {
            header('Location: order_detail.php?id=' . $order_id . '&error=Không thể xác nhận đơn hàng ở trạng thái hiện tại');
            exit;
        }
        break;
        
    case 'cancel': // Hủy đơn hàng
        if ($current_status != 'delivered' && $current_status != 'cancelled') {
            $new_status = 'cancelled';
            $status_message = "Đơn hàng đã bị hủy bởi $staff_name";
            
            // Nếu đơn hàng đã xuất kho, cần trả lại hàng tồn kho
            if ($current_status == 'shipping' || $current_status == 'confirmed') {
                // Lấy chi tiết đơn hàng
                $sql = "SELECT * FROM order_details WHERE order_id = $order_id";
                $result_details = mysqli_query($connect, $sql);
                
                while ($detail = mysqli_fetch_assoc($result_details)) {
                    $product_id = $detail['product_id'];
                    $quantity = $detail['quantity'];
                    
                    // Cập nhật lại số lượng trong kho
                    $sql_update = "UPDATE products SET stock_quantity = stock_quantity + $quantity 
                                WHERE product_id = $product_id";
                    mysqli_query($connect, $sql_update);
                }
            }
        } else {
            header('Location: order_detail.php?id=' . $order_id . '&error=Không thể hủy đơn hàng ở trạng thái hiện tại');
            exit;
        }
        break;
        
    case 'next': // Chuyển sang trạng thái tiếp theo
        switch ($current_status) {
            case 'confirmed':
                $new_status = 'shipping';
                $status_message = "Đơn hàng đã được đóng gói và giao cho đơn vị vận chuyển";
                break;
                
            case 'shipping':
                $new_status = 'delivered';
                $status_message = "Đơn hàng đã được giao thành công";
                break;
                
            default:
                header('Location: order_detail.php?id=' . $order_id . '&error=Không thể chuyển trạng thái đơn hàng');
                exit;
        }
        break;
        
    default:
        header('Location: orders.php?error=Hành động không hợp lệ');
        exit;
}

// Cập nhật trạng thái đơn hàng
$sql_update = "UPDATE orders SET order_status = '$new_status', 
              processed_date = '$current_time', 
              sales_employee_id = $staff_id 
              WHERE order_id = $order_id";

if (mysqli_query($connect, $sql_update)) {
    // Thêm vào lịch sử đơn hàng
    $sql_history = "INSERT INTO order_history (order_id, status, message, created_at, staff_id) 
                  VALUES ($order_id, '$new_status', '$status_message', '$current_time', $staff_id)";
    mysqli_query($connect, $sql_history);
    
    header('Location: order_detail.php?id=' . $order_id . '&success=Đơn hàng đã được cập nhật thành công');
} else {
    header('Location: order_detail.php?id=' . $order_id . '&error=Có lỗi xảy ra khi cập nhật đơn hàng: ' . mysqli_error($connect));
}
exit; 