<?php
require_once '../check_sales_login.php';
require_once '../../admin/root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Thiết lập trang hiện tại
$active_page = 'dashboard';
$page_title = 'Tổng quan bán hàng';

// Lấy tháng hiện tại để thống kê
$month = date('Y-m');

// Lấy năm hiện tại để thống kê
$year = date('Y');

// Doanh thu tháng - lấy tất cả đơn hàng đã hoàn thành trong tháng hiện tại
$sql = "SELECT SUM(total_amount) as total FROM `orders` 
        WHERE DATE_FORMAT(order_date, '%Y-%m') = '$month' 
        AND order_status = 'delivered'";
$result = mysqli_query($connect, $sql);
$eachMonth = mysqli_fetch_array($result);
$monthly_revenue = $eachMonth['total'] ?? 0;

// Số đơn hàng trong tháng - bao gồm cả đơn chưa duyệt, loại trừ đơn đã hủy
$sql = "SELECT COUNT(*) as total_orders FROM `orders` 
        WHERE DATE_FORMAT(order_date, '%Y-%m') = '$month' 
        AND order_status != 'cancelled'";
$result = mysqli_query($connect, $sql);
$month_orders = mysqli_fetch_array($result);
$orders_count = $month_orders['total_orders'] ?? 0;

// Số đơn hàng chờ xử lý
$sql = "SELECT COUNT(*) as total_pending FROM `orders` 
        WHERE order_status = 'pending'";
$result = mysqli_query($connect, $sql);
$pending_orders = mysqli_fetch_array($result);
$pending_count = $pending_orders['total_pending'] ?? 0;

// Lấy 5 đơn hàng mới nhất
$sql = "SELECT o.order_id, o.shipping_name, o.total_amount, o.order_status, o.order_date
        FROM orders o
        ORDER BY o.order_date DESC
        LIMIT 5";
$result_orders = mysqli_query($connect, $sql);

// Hàm định dạng số tiền VND
function vnd_format($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include '../partials/head.php'; ?>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-dashboard {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: none;
            transition: transform 0.3s ease;
        }
        
        .card-dashboard:hover {
            transform: translateY(-5px);
        }
        
        .card-dashboard .card-body {
            padding: 1.5rem;
        }
        
        .card-dashboard .card-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 1rem;
            color: #777;
        }
        
        .card-dashboard .card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .border-left-primary {
            border-left: 0.25rem solid var(--wood-beige) !important;
        }
        
        .border-left-success {
            border-left: 0.25rem solid #28a745 !important;
        }
        
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        
        /* Action buttons styling */
        .btn-group .btn {
            border-radius: 4px;
            font-weight: 500;
            margin-right: 5px;
        }
        
        .btn-group .btn i {
            margin-right: 3px;
        }
        
        .table td {
            vertical-align: middle !important;
        }
        
        /* Enhanced button visibility */
        .btn-view {
            background-color: #4e73df;
            color: white;
            min-width: 75px;
        }
        
        .btn-approve {
            background-color: #1cc88a;
            color: white;
            min-width: 75px;
        }
        
        .btn-cancel {
            background-color: #e74a3b;
            color: white;
            min-width: 75px;
        }
        
        .btn-ship {
            background-color: #36b9cc;
            color: white;
            min-width: 75px;
        }
        
        .btn-print {
            background-color: #6c757d;
            color: white;
            min-width: 75px;
        }
        
        /* Make buttons more visible in table */
        #dataTable .btn {
            padding: 0.375rem 0.75rem;
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
                <div id="page-content">
                    <div class="container-fluid">

                        <!-- Page Heading -->
                        <div class="d-sm-flex align-items-center justify-content-between mb-4">
                            <h1 class="h3 mb-0 text-gray-800">Tổng quan bán hàng</h1>
                            <div>
                                <a href="reports.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-2">
                                    <i class="fas fa-chart-bar fa-sm text-white-50"></i> Xem báo cáo
                                </a>
                                
                            </div>
                        </div>

                        <!-- Content Row -->
                        <div class="row">
                            <!-- Doanh thu trong tháng -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Doanh thu (Tháng <?php echo date('m/Y'); ?>)</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo vnd_format($monthly_revenue); ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Đơn hàng trong tháng -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-success shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Đơn hàng (Tháng <?php echo date('m/Y'); ?>)</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $orders_count; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Đơn hàng chờ xử lý -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-warning shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                    Đơn hàng chờ xử lý</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $pending_count; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Đơn hàng hoàn thành -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-info shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                    Đơn hàng hoàn thành</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php 
                                                    // Đếm đơn hoàn thành
                                                    $sql = "SELECT COUNT(*) as completed FROM `orders` WHERE order_status = 'delivered'";
                                                    $result = mysqli_query($connect, $sql);
                                                    $completed = mysqli_fetch_array($result);
                                                    echo $completed['completed'] ?? 0; 
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Đơn hàng mới nhất -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Đơn hàng mới nhất</h6>
                                <a href="orders.php" class="btn btn-primary btn-sm">Xem tất cả</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Mã đơn</th>
                                                <th>Khách hàng</th>
                                                <th>Tổng tiền</th>
                                                <th>Trạng thái</th>
                                                <th>Ngày đặt</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(mysqli_num_rows($result_orders) > 0): ?>
                                                <?php while($order = mysqli_fetch_assoc($result_orders)): ?>
                                                    <tr>
                                                        <td>#<?php echo $order['order_id']; ?></td>
                                                        <td><?php echo $order['shipping_name']; ?></td>
                                                        <td><?php echo vnd_format($order['total_amount']); ?></td>
                                                        <td>
                                                            <?php 
                                                            // Hiển thị trạng thái đơn hàng với badge màu phù hợp
                                                            switch($order['order_status']) {
                                                                case 'pending':
                                                                    echo '<span class="badge bg-warning text-dark">Chờ xử lý</span>';
                                                                    break;
                                                                case 'processing':
                                                                    echo '<span class="badge bg-primary text-white">Đang xử lý</span>';
                                                                    break;
                                                                case 'shipping':
                                                                    echo '<span class="badge bg-info text-white">Đang giao</span>';
                                                                    break;
                                                                case 'delivered':
                                                                    echo '<span class="badge bg-success text-white">Hoàn thành</span>';
                                                                    break;
                                                                case 'cancelled':
                                                                    echo '<span class="badge bg-danger text-white">Đã hủy</span>';
                                                                    break;
                                                                default:
                                                                    echo '<span class="badge bg-secondary text-white">Không xác định</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="order_detail.php?id=<?php echo $order['order_id']; ?>" class="btn btn-view btn-sm mr-1" title="Xem chi tiết">
                                                                    <i class="fas fa-eye"></i> Xem
                                                                </a>
                                                                <?php if($order['order_status'] == 'pending'): ?>
                                                                    <a href="process_order.php?id=<?php echo $order['order_id']; ?>&action=approve" class="btn btn-approve btn-sm mr-1" title="Xác nhận đơn hàng">
                                                                        <i class="fas fa-check"></i> Duyệt
                                                                    </a>
                                                                    <a href="process_order.php?id=<?php echo $order['order_id']; ?>&action=cancel" class="btn btn-cancel btn-sm" title="Hủy đơn hàng" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">
                                                                        <i class="fas fa-times"></i> Hủy
                                                                    </a>
                                                                <?php elseif($order['order_status'] != 'cancelled' && $order['order_status'] != 'delivered'): ?>
                                                                    <a href="process_order.php?id=<?php echo $order['order_id']; ?>&action=next" class="btn btn-ship btn-sm mr-1" title="Chuyển trạng thái tiếp theo">
                                                                        <i class="fas fa-arrow-right"></i> Tiếp theo
                                                                    </a>
                                                                <?php endif; ?>
                                                                <?php if($order['order_status'] == 'delivered'): ?>
                                                                    <a href="print_invoice.php?id=<?php echo $order['order_id']; ?>" class="btn btn-print btn-sm" title="In hóa đơn">
                                                                        <i class="fas fa-print"></i> In
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">Không có đơn hàng nào</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Thống kê trạng thái đơn hàng -->
                        <div class="row">
                            <!-- Biểu đồ trạng thái đơn hàng -->
                            <div class="col-lg-6">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Thống kê trạng thái đơn hàng</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-pie pt-4">
                                            <canvas id="orderStatusChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Thao tác nhanh -->
                            <div class="col-lg-6">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Thao tác nhanh</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-lg-6 mb-3">
                                                <a href="pending_orders.php" class="btn btn-warning btn-block">
                                                    <i class="fas fa-hourglass-half mr-2"></i> Đơn hàng chờ xử lý
                                                </a>
                                            </div>
                                            <div class="col-lg-6 mb-3">
                                            <a href="orders.php" class="btn btn-info btn-block">
                                                <i class="fas fa-tasks mr-2"></i> Quản lý đơn hàng
                                            </a>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6 mb-3">
                                                <a href="reports.php" class="btn btn-success btn-block">
                                                    <i class="fas fa-chart-line mr-2"></i> Xem báo cáo
                                                </a>
                                            </div>
                                            <div class="col-lg-6 mb-3">
                                                <a href="order_history.php" class="btn btn-primary btn-block">
                                                    <i class="fas fa-history mr-2"></i> Lịch sử đơn hàng
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End of Page Content -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
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
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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

    <!-- Custom scripts -->
    <script>
    $(document).ready(function() {
        // Activate bootstrap tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Activate bootstrap popovers
        $('[data-toggle="popover"]').popover();
        
        // Activate Modal
        $('.modal').modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });
    });
    </script>

    <!-- Chart JS -->
    <script>
        // Lấy số liệu đơn hàng theo trạng thái
        <?php
        $sql_status = "SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status";
        $result_status = mysqli_query($connect, $sql_status);
        $status_data = [0, 0, 0, 0, 0]; // pending, processing, shipped, delivered, cancelled
        
        while($row = mysqli_fetch_assoc($result_status)) {
            switch($row['order_status']) {
                case 'pending':
                    $status_data[0] = $row['count'];
                    break;
                case 'processing':
                    $status_data[1] = $row['count'];
                    break;
                case 'shipped':
                    $status_data[2] = $row['count'];
                    break;
                case 'delivered':
                    $status_data[3] = $row['count'];
                    break;
                case 'cancelled':
                    $status_data[4] = $row['count'];
                    break;
            }
        }
        ?>

        // Biểu đồ trạng thái đơn hàng
        var ctx = document.getElementById("orderStatusChart");
        var orderStatusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ["Chờ xử lý", "Đang xử lý", "Đang giao", "Hoàn thành", "Đã hủy"],
                datasets: [{
                    data: <?php echo json_encode($status_data); ?>,
                    backgroundColor: ['#f6c23e', '#4e73df', '#36b9cc', '#1cc88a', '#e74a3b'],
                    hoverBackgroundColor: ['#e0b02c', '#2e59d9', '#2c9faf', '#17a673', '#be2617'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: true,
                    position: 'bottom'
                },
                cutoutPercentage: 70,
            },
        });
    </script>
</body>
</html> 