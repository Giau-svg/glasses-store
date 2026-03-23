<?php
require_once '../check_sales_login.php';
require_once '../../admin/root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra nếu không có ID đơn hàng
if (!isset($_GET['id'])) {
    header('Location: orders.php?error=Không tìm thấy đơn hàng');
    exit;
}

$order_id = $_GET['id'];
$active_page = 'orders';
$page_title = 'Chi tiết đơn hàng #' . $order_id;

// Lấy thông tin đơn hàng
$sql = "SELECT o.*, u.email, u.phone 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = $order_id ";
$result = mysqli_query($connect, $sql);

if (mysqli_num_rows($result) == 0) {
    header('Location: orders.php?error=Không tìm thấy đơn hàng');
    exit;
}

$order = mysqli_fetch_assoc($result);

// Lấy các sản phẩm trong đơn hàng
$sql_details = "SELECT od.*, p.product_name 
               FROM order_details od
               JOIN products p ON od.product_id = p.product_id
               WHERE od.order_detail_id = $order_id";
$result_details = mysqli_query($connect, $sql_details);


// Lấy lịch sử đơn hàng
$sql_history = "SELECT oh.*, u.full_name 
               FROM order_history oh
               LEFT JOIN users u ON oh.staff_id = u.user_id
               WHERE oh.order_id = $order_id
               ORDER BY oh.created_at DESC";
$result_history = mysqli_query($connect, $sql_history);

// Hàm định dạng số tiền VND
function vnd_format($amount) {
    return number_format($amount ?? 0, 0, ',', '.') . ' đ';
}

// Hàm trả về trạng thái đơn hàng tiếng Việt
function get_status_text($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge badge-warning">Chờ xử lý</span>';
        case 'confirmed':
            return '<span class="badge badge-primary">Đã xác nhận</span>';
        case 'shipping':
            return '<span class="badge badge-info">Đang giao hàng</span>';
        case 'delivered':
            return '<span class="badge badge-success">Đã giao hàng</span>';
        case 'cancelled':
            return '<span class="badge badge-danger">Đã hủy</span>';
        default:
            return '<span class="badge badge-secondary">Không xác định</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include '../partials/head.php'; ?>
    <style>
        .order-summary {
            margin-bottom: 30px;
        }
        .status-badge {
            font-size: 1rem;
            padding: 8px 15px;
        }
        .order-products {
            margin-bottom: 30px;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline:before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: var(--light-gold);
        }
        .timeline-item {
            padding-bottom: 20px;
            position: relative;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -30px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--wood-beige);
        }
        .timeline-date {
            font-size: 0.8rem;
            color: #777;
        }
        .action-buttons {
            margin-top: 20px;
        }
        /* Page Layout */
        .container-fluid {
            padding: 4rem 1.5rem 1.5rem 1.5rem;
        }
    </style>
</head>
<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include 'sales_sidebar.php'; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include 'sales_topbar.php'; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
                        <a href="orders.php" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($_GET['success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Order Summary -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Thông tin đơn hàng</h6>
                                    <div class="status">
                                        <?php echo get_status_text($order['order_status']); ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Thông tin khách hàng</h5>
                                            <p><strong>Tên:</strong> <?php echo htmlspecialchars($order['shipping_name']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email'] ?? 'Không có'); ?></p>
                                            <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
                                            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Thông tin đơn hàng</h5>
                                            <p><strong>Mã đơn:</strong> #<?php echo $order['order_id']; ?></p>
                                            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                                            <p><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? ''); ?></p>
                                            <p><strong>Tổng tiền:</strong> <span class="text-danger font-weight-bold"><?php echo vnd_format($order['total_amount']); ?></span></p>
                                        </div>
                                    </div>

                                    <div class="action-buttons text-center">
                                        <?php if ($order['order_status'] == 'pending'): ?>
                                            <div class="btn-group btn-group-lg mb-3">
                                                <a href="process_order.php?id=<?php echo $order_id; ?>&action=approve" class="btn btn-success btn-lg px-4 mr-2">
                                                    <i class="fas fa-check mr-2"></i> Xác nhận đơn hàng
                                                </a>
                                                <a href="process_order.php?id=<?php echo $order_id; ?>&action=cancel" class="btn btn-danger btn-lg px-4" 
                                                   onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">
                                                    <i class="fas fa-times mr-2"></i> Hủy đơn hàng
                                                </a>
                                            </div>
                                        <?php elseif ($order['order_status'] == 'confirmed'): ?>
                                            <div class="btn-group btn-group-lg mb-3">
                                                <a href="process_order.php?id=<?php echo $order_id; ?>&action=next" class="btn btn-primary btn-lg px-4 mr-2">
                                                    <i class="fas fa-shipping-fast mr-2"></i> Chuyển sang Đang giao hàng
                                                </a>
                                                <a href="process_order.php?id=<?php echo $order_id; ?>&action=cancel" class="btn btn-danger btn-lg px-4" 
                                                   onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">
                                                    <i class="fas fa-times mr-2"></i> Hủy đơn hàng
                                                </a>
                                            </div>
                                        <?php elseif ($order['order_status'] == 'shipping'): ?>
                                            <div class="btn-group btn-group-lg mb-3">
                                                <a href="process_order.php?id=<?php echo $order_id; ?>&action=next" class="btn btn-success btn-lg px-4 mr-2">
                                                    <i class="fas fa-check-circle mr-2"></i> Xác nhận đã giao hàng
                                                </a>
                                                <a href="process_order.php?id=<?php echo $order_id; ?>&action=cancel" class="btn btn-danger btn-lg px-4" 
                                                   onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">
                                                    <i class="fas fa-times mr-2"></i> Hủy đơn hàng
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($order['order_status'] == 'delivered' || $order['order_status'] == 'confirmed'): ?>
                                            <a href="print_invoice.php?id=<?php echo $order_id; ?>" class="btn btn-info btn-lg px-4" target="_blank">
                                                <i class="fas fa-print mr-2"></i> In hóa đơn
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Lịch sử đơn hàng</h6>
                                </div>
                                <div class="card-body">
                                    <div class="timeline">
                                        <?php if (mysqli_num_rows($result_history) > 0): ?>
                                            <?php while ($history = mysqli_fetch_assoc($result_history)): ?>
                                                <div class="timeline-item">
                                                    <div class="timeline-date"><?php echo date('d/m/Y H:i', strtotime($history['created_at'])); ?></div>
                                                    <div class="timeline-content">
                                                        <p><?php echo htmlspecialchars($history['message']); ?></p>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <p>Không có lịch sử đơn hàng</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Products -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Sản phẩm trong đơn hàng</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Tên sản phẩm</th>
                                            <th>Đơn giá</th>
                                            <th>Số lượng</th>
                                            <th>Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $subtotal = 0;
                                        while ($detail = mysqli_fetch_assoc($result_details)):
                                            $price = isset($detail['price']) ? $detail['price'] : 0;
                                            $quantity = isset($detail['quantity']) ? $detail['quantity'] : 0;
                                            $item_total = $price * $quantity;
                                            $subtotal += $item_total;
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($detail['product_name']); ?></td>
                                                <td><?php echo vnd_format($price); ?></td>
                                                <td><?php echo $detail['quantity']; ?></td>
                                                <td><?php echo vnd_format($item_total); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-right">Tổng tiền sản phẩm:</th>
                                            <th class="text-danger"><?php echo vnd_format($order['total_amount']); ?></th>
                                        </tr>
                                        <tr>
                                            <th colspan="4" class="text-right">Phí vận chuyển:</th>
                                            <th><?php echo vnd_format($order['shipping_fee']); ?></th>
                                        </tr>
                                        <tr>
                                            <th colspan="4" class="text-right">Tổng thanh toán:</th>
                                            <th class="text-danger"><?php echo vnd_format($order['total_amount']); ?></th>
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
            <?php include '../partials/footer.php'; ?>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="../public/vendor/jquery/jquery.min.js"></script>
    <script src="../public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../public/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../public/js/sb-admin-2.min.js"></script>
</body>
</html> 