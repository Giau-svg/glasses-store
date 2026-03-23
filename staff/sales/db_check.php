<?php
// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối database
require_once '../../admin/root.php';

echo "<h1>Khắc phục lỗi trong bảng Orders</h1>";

// Kiểm tra cấu trúc hiện tại của cột order_status
$sql_check_column = "SHOW COLUMNS FROM orders LIKE 'order_status'";
$result_column = mysqli_query($connect, $sql_check_column);
$column_info = mysqli_fetch_assoc($result_column);

echo "<h2>Thông tin cột order_status hiện tại:</h2>";
echo "<pre>";
print_r($column_info);
echo "</pre>";

// Sửa lại cấu trúc bảng nếu cần thiết
$sql_modify_column = "ALTER TABLE orders MODIFY COLUMN order_status ENUM('pending','confirmed','shipping','delivered','cancelled','processing','shipped') DEFAULT 'pending'";
if (mysqli_query($connect, $sql_modify_column)) {
    echo "<div style='color: green; font-weight: bold;'>Đã cập nhật cấu trúc bảng orders thành công!</div>";
} else {
    echo "<div style='color: red; font-weight: bold;'>Lỗi khi cập nhật cấu trúc bảng: " . mysqli_error($connect) . "</div>";
}

// Xoá toàn bộ dữ liệu cũ nếu có lỗi
echo "<h2>Xoá dữ liệu cũ và tạo dữ liệu mẫu mới</h2>";

// Lưu ý: Chỉ sử dụng TRUNCATE TABLE nếu muốn xoá toàn bộ dữ liệu
mysqli_query($connect, "SET FOREIGN_KEY_CHECKS=0");
mysqli_query($connect, "TRUNCATE TABLE order_details");
mysqli_query($connect, "TRUNCATE TABLE orders");
mysqli_query($connect, "SET FOREIGN_KEY_CHECKS=1");

echo "<div style='color: green;'>Đã xoá dữ liệu cũ!</div>";

// Thêm dữ liệu mẫu mới
$sql_insert_order = "INSERT INTO orders (user_id, total_amount, order_status, shipping_notes, shipping_name, shipping_phone, shipping_email, shipping_address, payment_method, order_date) VALUES 
(1, 2500000, 'pending', 'Giao hàng trong giờ hành chính', 'Nguyễn Văn A', '0901234567', 'nguyenvana@gmail.com', '123 Đường Lê Lợi, Quận 1, TP.HCM', 'COD', NOW()),
(2, 1800000, 'confirmed', 'Gọi trước khi giao', 'Trần Thị B', '0912345678', 'tranthib@gmail.com', '456 Đường Nguyễn Huệ, Quận 1, TP.HCM', 'Banking', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 3200000, 'shipping', 'Để hàng tại quầy lễ tân', 'Lê Văn C', '0823456789', 'levanc@gmail.com', '789 Đường 3/2, Quận 10, TP.HCM', 'COD', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 1500000, 'delivered', NULL, 'Nguyễn Văn A', '0901234567', 'nguyenvana@gmail.com', '123 Đường Lê Lợi, Quận 1, TP.HCM', 'Banking', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 950000, 'cancelled', 'Khách hàng đổi ý', 'Phạm Thị D', '0978123456', 'phamthid@gmail.com', '321 Đường Cách Mạng Tháng 8, Quận 3, TP.HCM', 'COD', DATE_SUB(NOW(), INTERVAL 3 DAY))";

if (mysqli_query($connect, $sql_insert_order)) {
    echo "<div style='color: green;'>Đã thêm dữ liệu mẫu thành công!</div>";
} else {
    echo "<div style='color: red;'>Lỗi khi thêm dữ liệu mẫu: " . mysqli_error($connect) . "</div>";
}

// Lấy danh sách các trạng thái hiện có trong bảng
$sql_statuses = "SELECT DISTINCT order_status FROM orders";
$result_statuses = mysqli_query($connect, $sql_statuses);
$statuses = [];
while ($row = mysqli_fetch_assoc($result_statuses)) {
    $statuses[] = $row['order_status'];
}

echo "<h2>Trạng thái đơn hàng hiện có:</h2>";
echo "<ul>";
foreach ($statuses as $status) {
    echo "<li>$status</li>";
}
echo "</ul>";

// Hiển thị đơn hàng đã thêm
$sql_orders = "SELECT * FROM orders";
$result_orders = mysqli_query($connect, $sql_orders);

echo "<h2>Danh sách đơn hàng:</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr>
        <th>ID</th>
        <th>Khách hàng</th>
        <th>SĐT</th>
        <th>Tổng tiền</th>
        <th>Trạng thái</th>
        <th>Ngày đặt</th>
      </tr>";

while ($row = mysqli_fetch_assoc($result_orders)) {
    echo "<tr>";
    echo "<td>" . $row['order_id'] . "</td>";
    echo "<td>" . $row['shipping_name'] . "</td>";
    echo "<td>" . $row['shipping_phone'] . "</td>";
    echo "<td>" . number_format($row['total_amount'], 0, ',', '.') . " đ</td>";
    echo "<td>" . $row['order_status'] . "</td>";
    echo "<td>" . $row['order_date'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><a href='orders.php' style='display: inline-block; padding: 10px 15px; background-color: #4e73df; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;'>Quay lại trang Quản lý đơn hàng</a></p>";
?> 