<?php
require_once '../check_inventory_login.php';
require_once '../../admin/root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra ID phiếu nhập
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: stock_history.php');
    exit;
}

$receipt_id = $_GET['id'];

// Lấy thông tin công ty
$company_name = "EYEGLASSES";
$company_address = "123 Đường ABC, Quận 1, TP. Hồ Chí Minh";
$company_phone = "0123456789";
$company_email = "info@eyeglasses.com";

// Lấy thông tin phiếu nhập
$sql = "SELECT sr.*, s.supplier_name, s.contact_name, s.phone, s.email, s.address, u.full_name as inventory_manager_name 
        FROM stock_receipts sr
        LEFT JOIN suppliers s ON sr.supplier_id = s.supplier_id
        LEFT JOIN users u ON sr.inventory_manager_id = u.user_id
        WHERE sr.receipt_id = ?";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $receipt_id);
mysqli_stmt_execute($stmt);
$receipt_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($receipt_result) == 0) {
    header('Location: stock_history.php');
    exit;
}

$receipt = mysqli_fetch_assoc($receipt_result);

// Lấy chi tiết phiếu nhập
$sql = "SELECT rd.*, p.product_name 
        FROM stock_receipt_details rd
        LEFT JOIN products p ON rd.product_id = p.product_id
        WHERE rd.receipt_id = ?
        ORDER BY rd.receipt_detail_id ASC";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "i", $receipt_id);
mysqli_stmt_execute($stmt);
$details_result = mysqli_stmt_get_result($stmt);

// Hàm định dạng số tiền VND
function vnd_format($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>In phiếu nhập kho #<?php echo $receipt_id; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .company-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .receipt-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .receipt-info {
            margin-bottom: 20px;
        }
        .receipt-info p {
            margin-bottom: 5px;
        }
        .table th, .table td {
            padding: 8px;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }
        .signature-box {
            width: 30%;
        }
        .signature-line {
            margin-top: 70px;
            border-top: 1px dotted #333;
        }
        .print-button {
            margin: 20px 0;
            text-align: center;
        }
        @media print {
            .print-button {
                display: none;
            }
            .print-container {
                border: none;
                box-shadow: none;
                padding: 0;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()" class="btn btn-primary">In phiếu</button>
        <a href="receipt_detail.php?id=<?php echo $receipt_id; ?>" class="btn btn-secondary">Quay lại</a>
    </div>

    <div class="print-container">
        <div class="company-header">
            <h3><?php echo $company_name; ?></h3>
            <p><?php echo $company_address; ?></p>
            <p>ĐT: <?php echo $company_phone; ?> | Email: <?php echo $company_email; ?></p>
        </div>

        <div class="receipt-title">
            PHIẾU NHẬP KHO
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="receipt-info">
                    <p><strong>Mã phiếu:</strong> #<?php echo $receipt_id; ?></p>
                    <p><strong>Ngày nhập:</strong> <?php echo date('d/m/Y H:i', strtotime($receipt['receipt_date'])); ?></p>
                    <p><strong>Người nhập:</strong> <?php echo htmlspecialchars($receipt['inventory_manager_name']); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="receipt-info">
                    <p><strong>Nhà cung cấp:</strong> <?php echo htmlspecialchars($receipt['supplier_name']); ?></p>
                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($receipt['address']); ?></p>
                    <p><strong>Liên hệ:</strong> <?php echo htmlspecialchars($receipt['contact_name']); ?> - <?php echo htmlspecialchars($receipt['phone']); ?></p>
                </div>
            </div>
        </div>

        <div class="receipt-info">
            <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($receipt['notes']); ?></p>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stt = 1;
                mysqli_data_seek($details_result, 0);
                while ($detail = mysqli_fetch_assoc($details_result)): 
                ?>
                    <tr>
                        <td><?php echo $stt++; ?></td>
                        <td><?php echo htmlspecialchars($detail['product_name']); ?></td>
                        <td><?php echo $detail['quantity']; ?></td>
                        <td><?php echo vnd_format($detail['unit_price']); ?></td>
                        <td><?php echo vnd_format($detail['subtotal']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-right">Tổng cộng:</th>
                    <th><?php echo vnd_format($receipt['total_amount']); ?></th>
                </tr>
            </tfoot>
        </table>

        <div class="signature-section">
            <div class="signature-box">
                <p><strong>Người lập phiếu</strong></p>
                <div class="signature-line"></div>
                <p><?php echo htmlspecialchars($receipt['inventory_manager_name']); ?></p>
            </div>
            <div class="signature-box">
                <p><strong>Nhà cung cấp</strong></p>
                <div class="signature-line"></div>
                <p><?php echo htmlspecialchars($receipt['contact_name']); ?></p>
            </div>
            <div class="signature-box">
                <p><strong>Giám đốc</strong></p>
                <div class="signature-line"></div>
                <p></p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function goBackAfterPrint() {
        history.back();
    }

    if (window.matchMedia) {
        window.matchMedia('print').addListener(function(media) {
            if (!media.matches) {
                goBackAfterPrint();
            }
        });
    }
    window.addEventListener('afterprint', goBackAfterPrint);
    window.onload = function() {
        window.print();
    };
</script>
</body>
</html> 