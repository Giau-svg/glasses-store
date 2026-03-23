<?php
require_once '../../admin/config/connect.php';
session_start();

// Proper login check
if (empty($_SESSION['staff_user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Include the settings and invoice column setups silently (using output buffering)
ob_start();
require_once '../sales/add_settings_table.php';
require_once '../sales/add_invoice_column.php';
ob_end_clean();

// Kiểm tra ID đơn hàng
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../sales/print_invoice.php");
    exit;
}

$order_id = intval($_GET['id']);

// Lấy thông tin đơn hàng
$sql = "SELECT o.* FROM orders o WHERE o.order_id = ?";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo "<div class='alert alert-danger'>Không tìm thấy đơn hàng</div>";
    header("Location: ../sales/print_invoice.php");
    exit;
}

$order = mysqli_fetch_assoc($result);

// Kiểm tra trạng thái đơn hàng và tạo hóa đơn nếu hợp lệ
if ($order['order_status'] != 'processing' && $order['order_status'] != 'shipped' && $order['order_status'] != 'delivered') {
    echo "<div class='alert alert-danger'>Đơn hàng chưa được xác nhận hoặc đã bị hủy</div>";
    header("Location: ../sales/print_invoice.php");
    exit;
}

// Tạo mã hóa đơn nếu chưa có
if (empty($order['invoice_number'])) {
    $invoice_number = 'INV' . date('Ymd') . $order_id;
    $update_sql = "UPDATE orders SET invoice_number = ? WHERE order_id = ?";
    $update_stmt = mysqli_prepare($connect, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "si", $invoice_number, $order_id);
    mysqli_stmt_execute($update_stmt);
    $order['invoice_number'] = $invoice_number;
}

// Lấy thông tin chi tiết đơn hàng
$sql_details = "SELECT od.*, p.product_name, p.image 
                FROM order_details od 
                LEFT JOIN products p ON od.product_id = p.product_id 
                WHERE od.order_id = ?";
$stmt_details = mysqli_prepare($connect, $sql_details);
mysqli_stmt_bind_param($stmt_details, "i", $order_id);
mysqli_stmt_execute($stmt_details);
$result_details = mysqli_stmt_get_result($stmt_details);

// Get company information
$sql_company = "SELECT * FROM settings WHERE setting_key IN ('company_name', 'company_address', 'company_phone', 'company_email', 'company_logo')";
$result_company = mysqli_query($connect, $sql_company);
$company = [];

if ($result_company && mysqli_num_rows($result_company) > 0) {
    while ($row = mysqli_fetch_assoc($result_company)) {
        $company[$row['setting_key']] = $row['setting_value'];
    }
}

// Function để định dạng tiền tệ VND
function vnd_format($amount) {
    return number_format($amount, 0, ',', '.') . 'đ';
}

// Định dạng ngày tháng
function format_date($date) {
    return date('d/m/Y', strtotime($date));
}

// Get store name from settings or use default
$store_name = isset($company['company_name']) ? $company['company_name'] : 'EYEGLASSES';
$store_address = isset($company['company_address']) ? $company['company_address'] : 'Hệ thống kính mắt chất lượng cao';
$store_phone = isset($company['company_phone']) ? $company['company_phone'] : '';
$store_email = isset($company['company_email']) ? $company['company_email'] : '';

// Xác định nếu cần hiển thị trang in
$print_mode = isset($_GET['print']) && $_GET['print'] == 'true';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #<?php echo $order['invoice_number'] ?? 'INV'.date('Ymd').$order_id; ?></title>
    
    <!-- Bootstrap và CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        :root {
            --wood-beige: #d2b48c;
            --cream-white: #f5f5dc;
            --black: #333;
            --light-gold: #d4af37;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            font-size: 14px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 30px auto;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        
        .logo-area {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        
        .logo-area h1 {
            margin: 0;
            color: var(--wood-beige);
            font-size: 28px;
            font-weight: 700;
        }
        
        .logo-area p {
            color: #666;
            margin-bottom: 0;
        }
        
        .invoice-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .invoice-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--wood-beige);
            margin-bottom: 15px;
        }
        
        .invoice-details {
            margin-bottom: 30px;
        }
        
        .customer-details {
            margin-bottom: 20px;
        }
        
        .table-items th {
            background-color: var(--cream-white);
            color: var(--wood-beige);
            font-weight: 600;
        }
        
        .table-items {
            border: 1px solid #e3e6f0;
        }
        
        .table-items td, .table-items th {
            vertical-align: middle;
            padding: 10px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .print-button {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn-print {
            background-color: var(--wood-beige);
            color: white;
            font-weight: 600;
            padding: 10px 25px;
            border-radius: 5px;
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .btn-print:hover {
            background-color: var(--light-gold);
            color: var(--black);
        }
        
        .btn-back {
            background-color: #36b9cc;
            color: white;
            font-weight: 600;
            padding: 10px 25px;
            border-radius: 5px;
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-right: 10px;
        }
        
        .btn-back:hover {
            background-color: #2c9faf;
            color: white;
        }
        
        .invoice-badges {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .invoice-num {
            background-color: var(--light-gold);
            color: var(--black);
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .order-id {
            background-color: #1cc88a;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 600;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 600;
        }
        
        .badge-primary {
            background-color: var(--wood-beige);
            color: white;
        }
        
        .badge-info {
            background-color: #36b9cc;
            color: white;
        }
        
        .badge-success {
            background-color: #1cc88a;
            color: white;
        }
        
        /* Print styles */
        @media print {
            body {
                background-color: #fff;
            }
            
            .print-button, 
            .btn-back,
            .header-section,
            .no-print {
                display: none !important;
            }
            
            .invoice-container {
                box-shadow: none;
                margin: 0;
                padding: 15px;
                max-width: 100%;
            }
            
            .container {
                max-width: 100%;
                width: 100%;
            }
            
            /* Ensure tables don't break across pages */
            table { page-break-inside: avoid; }
            tr    { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <?php if (!$print_mode): ?>
        <div class="header-section mb-4 no-print">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="text-primary"><i class="fas fa-file-invoice"></i> Chi tiết hóa đơn</h2>
                <div>
                    <a href="../sales/print_invoice.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Danh sách hóa đơn
                    </a>
                    <button class="btn btn-primary ml-2" onclick="window.print()">
                        <i class="fas fa-print"></i> In hóa đơn
                    </button>
                </div>
            </div>
            <hr>
        </div>
        <?php endif; ?>
        
        <div class="invoice-container">
            <div class="logo-area">
                <h1><?php echo htmlspecialchars($store_name); ?></h1>
                <p><?php echo htmlspecialchars($store_address); ?></p>
                <?php if (!empty($store_phone)): ?>
                    <p>Điện thoại: <?php echo htmlspecialchars($store_phone); ?></p>
                <?php endif; ?>
                <?php if (!empty($store_email)): ?>
                    <p>Email: <?php echo htmlspecialchars($store_email); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="invoice-header row">
                <div class="col-md-6">
                    <div class="invoice-title">HÓA ĐƠN</div>
                    <div class="invoice-badges">
                        <span class="order-id">Đơn hàng #<?php echo $order_id; ?></span>
                        <span class="invoice-num"><?php echo $order['invoice_number'] ?? 'INV'.date('Ymd').$order_id; ?></span>
                    </div>
                    <div>Ngày tạo: <strong><?php echo format_date(date('Y-m-d')); ?></strong></div>
                    <div>Ngày đặt hàng: <strong><?php echo format_date($order['order_date']); ?></strong></div>
                </div>
                <div class="col-md-6 text-right">
                    <div>Trạng thái đơn hàng: 
                        <span class="badge badge-<?php
                            switch($order['order_status']) {
                                case 'processing': echo 'primary'; break;
                                case 'shipped': echo 'info'; break;
                                case 'delivered': echo 'success'; break;
                                default: echo 'secondary';
                            }
                        ?>">
                            <?php
                                switch($order['order_status']) {
                                    case 'processing': echo 'Đang xử lý'; break;
                                    case 'shipped': echo 'Đang giao hàng'; break;
                                    case 'delivered': echo 'Đã giao hàng'; break;
                                    default: echo 'Không xác định';
                                }
                            ?>
                        </span>
                    </div>
                    <div>Phương thức thanh toán: 
                        <strong>
                            <?php 
                            if (!empty($order['payment_method'])) {
                                echo $order['payment_method'] == 'COD' ? 'Thanh toán khi nhận hàng (COD)' : $order['payment_method'];
                            } else {
                                echo 'Thanh toán khi nhận hàng';
                            }
                            ?>
                        </strong>
                    </div>
                    <?php if (!empty($order['processed_date'])): ?>
                    <div>Ngày xác nhận: <strong><?php echo format_date($order['processed_date']); ?></strong></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="invoice-details row">
                <div class="col-md-6 customer-details">
                    <h5>Thông tin khách hàng</h5>
                    <div>Họ tên: <strong><?php echo htmlspecialchars($order['shipping_name']); ?></strong></div>
                    <div>Điện thoại: <?php echo htmlspecialchars($order['shipping_phone']); ?></div>
                    <?php if (!empty($order['shipping_email'])): ?>
                    <div>Email: <?php echo htmlspecialchars($order['shipping_email']); ?></div>
                    <?php endif; ?>
                    <div>Địa chỉ: <?php echo htmlspecialchars($order['shipping_address']); ?></div>
                </div>
                <div class="col-md-6 customer-details">
                    <h5>Ghi chú đơn hàng</h5>
                    <?php if (!empty($order['shipping_notes'])): ?>
                    <div><?php echo nl2br(htmlspecialchars($order['shipping_notes'])); ?></div>
                    <?php else: ?>
                    <div><em>Không có ghi chú</em></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <table class="table table-bordered table-items">
                <thead>
                    <tr>
                        <th width="5%">STT</th>
                        <th width="45%">Sản phẩm</th>
                        <th width="15%">Đơn giá</th>
                        <th width="10%">SL</th>
                        <th width="25%">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stt = 1;
                    $subtotal = 0;
                    if (mysqli_num_rows($result_details) > 0) {
                        while($item = mysqli_fetch_assoc($result_details)): 
                            $item_total = $item['unit_price'] * $item['quantity'];
                            $subtotal += $item_total;
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $stt++; ?></td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td class="text-right"><?php echo vnd_format($item['unit_price']); ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-right"><?php echo vnd_format($item_total); ?></td>
                    </tr>
                    <?php 
                        endwhile;
                    } else {
                    ?>
                    <tr>
                        <td colspan="5" class="text-center">Không có sản phẩm nào trong đơn hàng</td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right"><strong>Tổng tiền hàng:</strong></td>
                        <td class="text-right"><?php echo vnd_format($subtotal); ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-right"><strong>Phí vận chuyển:</strong></td>
                        <td class="text-right"><?php echo vnd_format($order['shipping_fee'] ?? 0); ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-right"><strong>Tổng thanh toán:</strong></td>
                        <td class="text-right"><strong><?php echo vnd_format($order['total_amount']); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
            
            <div class="footer">
                <p>Cảm ơn quý khách đã mua hàng tại <?php echo htmlspecialchars($store_name); ?>!</p>
                <p>Mọi thắc mắc xin vui lòng liên hệ với chúng tôi qua số điện thoại: <?php echo htmlspecialchars($store_phone ?: '1900 1234'); ?></p>
            </div>
            
            <?php if (!$print_mode): ?>
            <div class="print-button">
                <a href="../sales/print_invoice.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại danh sách
                </a>
                <button class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> In hóa đơn
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Auto print if query parameter print=true
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('print') === 'true') {
                setTimeout(function() {
                    window.print();
                }, 1000);
            }
        });
    </script>
</body>
</html> 