<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Check if user is logged in and has sales role
require_once '../check_sales_login.php';

// Include database connection
require_once '../../admin/config/connect.php';

// Kiểm tra lỗi kết nối
if (!$connect) {
    die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
}

// Kiểm tra nếu không có ID đơn hàng
if (!isset($_GET['id'])) {
    die("Không tìm thấy đơn hàng.");
}

$order_id = $_GET['id'];

// Lấy thông tin đơn hàng
$stmt = $connect->prepare("SELECT o.*, u.full_name, u.email, u.phone, u.address 
                         FROM orders o 
                         LEFT JOIN users u ON o.user_id = u.user_id
                         WHERE o.order_id = ?");
$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Không tìm thấy đơn hàng.");
}

$order = $result->fetch_assoc();

// Lấy danh sách sản phẩm trong đơn hàng
$stmt_details = $connect->prepare("SELECT od.*, p.product_name 
                                 FROM order_details od
                                 JOIN products p ON od.product_id = p.product_id
                                 WHERE od.order_id = ?");
$stmt_details->bind_param("s", $order_id);
$stmt_details->execute();
$result_details = $stmt_details->get_result();

// Tính tổng tiền
$total_amount = 0;
$order_items = [];
while ($item = $result_details->fetch_assoc()) {
    $order_items[] = $item;
    $total_amount += $item['unit_price'] * $item['quantity'];
}

// Hiển thị giao diện HTML xem trước hóa đơn
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #<?php echo $order_id; ?></title>
    <style>
        /* Thêm font Roboto vào CSS */
        @font-face {
            font-family: 'Roboto';
            src: url('../../libs/fpdf/font/Roboto-Regular.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'Roboto';
            src: url('../../libs/fpdf/font/Roboto-Bold.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
        }

        body { font-family: 'Roboto', Arial, sans-serif; margin: 20px; }
        .invoice-container { max-width: 800px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; }
        .invoice-header { text-align: center; margin-bottom: 20px; }
        .invoice-info { margin-bottom: 20px; }
        .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .invoice-table th, .invoice-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .invoice-table th { background-color: #f2f2f2; }
        .invoice-total { text-align: right; font-weight: bold; }
        .invoice-actions { margin-top: 20px; text-align: center; }
        .btn { display: inline-block; padding: 8px 16px; margin: 0 5px; 
               background-color: #4CAF50; color: white; text-decoration: none; 
               border-radius: 4px; border: none; cursor: pointer; }
        .btn:hover { background-color: #45a049; }
        .btn-back { background-color: #f1c40f; color: #fff; }
        .btn-back:hover { background-color: #d4ac0d; }
        @media print {
            .invoice-actions { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h2>EYEGLASSES</h2>
            <h2>HÓA ĐƠN BÁN HÀNG</h2>
            <p style="font-style: italic; font-size: 15px; margin-top: 5px;;">
                Hệ thống kính mắt chất lượng cao - Hotline: 1900 1234
            </p>
        </div>
        
        <div class="invoice-info">
            <p><strong>Mã đơn:</strong> #<?php echo $order['order_id']; ?></p>
            <p><strong>Khách hàng:</strong> <?php echo $order['shipping_name']; ?></p>
            <p><strong>Email:</strong> <?php echo $order['email']; ?></p>
            <p><strong>SĐT:</strong> <?php echo $order['shipping_phone']; ?></p>
            <p><strong>Địa chỉ:</strong> <?php echo $order['shipping_address']; ?></p>
            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
        </div>
        
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($order_items)): ?>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo number_format($item['unit_price'], 0, ',', '.'); ?> đ</td>
                        <td><?php echo number_format($item['unit_price'] * $item['quantity'], 0, ',', '.'); ?> đ</td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Không có sản phẩm nào trong đơn hàng.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="invoice-total">
            <p>TỔNG CỘNG: <?php echo number_format($total_amount, 0, ',', '.'); ?> đ</p>
        </div>
        
        <div class="invoice-actions">
            <button onclick="window.location.href='order_detail.php?id=<?php echo urlencode($order_id); ?>'" class="btn btn-back">Quay lại</button>
            <button onclick="window.print()" class="btn">In hóa đơn</button>
        </div>
    </div>
</body>
</html>