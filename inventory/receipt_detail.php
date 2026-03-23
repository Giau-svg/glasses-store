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

// Lấy thông tin phiếu nhập
$sql = "SELECT sr.*, s.supplier_name, s.contact_name, s.phone, s.email, u.full_name as staff_name 
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

// Thiết lập trang hiện tại
$active_page = 'stock_history';
$page_title = 'Chi tiết phiếu nhập kho #' . $receipt_id;

// Hàm định dạng số tiền VND
function vnd_format($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include '../partials/head.php'; ?>
    <style>
        .receipt-header {
            margin-bottom: 30px;
        }
        .receipt-info {
            margin-bottom: 20px;
        }
        .receipt-info p {
            margin-bottom: 5px;
        }
        .print-section {
            margin-top: 30px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
        }
    </style>
</head>
<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <div class="no-print">
            <?php include 'inventory_sidebar.php'; ?>
        </div>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
    <div id="page-content"> 

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <div class="no-print">
                    <?php include 'inventory_topbar.php'; ?>
                </div>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Chi tiết phiếu nhập kho #<?php echo $receipt_id; ?></h1>

    <div>
        <a href="stock_history.php" class="btn btn-light btn-circle btn-sm mr-1" title="Đóng">
            <i class="fas fa-times"></i>
        </a>

        <a href="print_receipt.php?id=<?php echo $receipt_id; ?>" class="btn btn-sm btn-primary" title="In phiếu">
            <i class="fas fa-print mr-1"></i> In phiếu
        </a>
    </div>
</div>
                           
                    <!-- Chi tiết phiếu nhập -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu nhập kho</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="receipt-info">
                                        <h5>Thông tin phiếu nhập</h5>
                                        <p><strong>Mã phiếu:</strong> #<?php echo $receipt_id; ?></p>
                                        <p><strong>Ngày nhập:</strong> <?php echo date('d/m/Y H:i', strtotime($receipt['receipt_date'])); ?></p>
                                        <p><strong>Người nhập:</strong> <?php echo htmlspecialchars($receipt['staff_name']); ?></p>
                                        <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($receipt['notes']); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="receipt-info">
                                        <h5>Thông tin nhà cung cấp</h5>
                                        <p><strong>Tên nhà cung cấp:</strong> <?php echo htmlspecialchars($receipt['supplier_name']); ?></p>
                                        <p><strong>Người liên hệ:</strong> <?php echo htmlspecialchars($receipt['contact_name']); ?></p>
                                        <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($receipt['phone']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($receipt['email']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <h5>Chi tiết sản phẩm</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
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
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white no-print">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; EYEGLASSES 2023</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded no-print" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade no-print" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Bạn muốn đăng xuất?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Chọn "Đăng xuất" bên dưới nếu bạn muốn kết thúc phiên làm việc hiện tại.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                    <a class="btn btn-primary" href="../logout.php">Đăng xuất</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="https://cdn.jsdelivr.net/npm/jquery.easing@1.4.1/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../admin/public/js/sb-admin-2.min.js"></script>

</body>
</html> 