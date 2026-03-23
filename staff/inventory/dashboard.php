<?php
require_once '../check_inventory_login.php';
require_once '../../admin/root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Thiết lập trang hiện tại
$active_page = 'dashboard';
$page_title = 'Tổng quan quản lý kho';

// Lấy tháng hiện tại để thống kê
$month = date('Y-m');

// Lấy năm hiện tại để thống kê
$year = date('Y');

// Tổng số sản phẩm
$sql = "SELECT COUNT(*) as total FROM products";
$result = mysqli_query($connect, $sql);
$total_products = mysqli_fetch_assoc($result)['total'] ?? 0;

// Tổng số nhà cung cấp
$sql = "SELECT COUNT(*) as total FROM suppliers";
$result = mysqli_query($connect, $sql);
$total_suppliers = mysqli_fetch_assoc($result)['total'] ?? 0;

// Tổng giá trị hàng tồn kho
$sql = "SELECT SUM(stock_quantity * cost_price) as total_value FROM products";
$result = mysqli_query($connect, $sql);
$total_stock_value = mysqli_fetch_assoc($result)['total_value'] ?? 0;

// Số lượng sản phẩm sắp hết hàng (định nghĩa sắp hết hàng là dưới 10 sản phẩm)
$sql = "SELECT COUNT(*) as total FROM products WHERE stock_quantity < 10";
$result = mysqli_query($connect, $sql);
$low_stock_count = mysqli_fetch_assoc($result)['total'] ?? 0;

// Sản phẩm sắp hết hàng
$sql = "SELECT product_id, product_name, stock_quantity, cost_price 
        FROM products 
        WHERE stock_quantity < 10 
        ORDER BY stock_quantity ASC 
        LIMIT 5";
$result_low_stock = mysqli_query($connect, $sql);

// Nhật ký nhập kho gần đây
$sql = "SELECT sr.receipt_id, sr.receipt_date, sr.total_amount, s.supplier_name 
        FROM stock_receipts sr
        JOIN suppliers s ON sr.supplier_id = s.supplier_id
        ORDER BY sr.receipt_date DESC
        LIMIT 5";
$result_receipts = mysqli_query($connect, $sql);

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
</head>
<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include 'inventory_sidebar.php'; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include 'inventory_topbar.php'; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div id="page-content">
                    <div class="container-fluid">

                        <!-- Page Heading -->
                        <div class="d-sm-flex align-items-center justify-content-between mb-4">
                            
                        </div>

                        <!-- Content Row -->
                        <div class="row">
                            <!-- Tổng số sản phẩm -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Tổng số sản phẩm</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $total_products; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-glasses fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tổng số nhà cung cấp -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-success shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Nhà cung cấp</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $total_suppliers; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-industry fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Giá trị tồn kho -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-info shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                    Giá trị tồn kho</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo vnd_format($total_stock_value); ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sản phẩm sắp hết hàng -->
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="card border-left-warning shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                    Sản phẩm sắp hết hàng</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo $low_stock_count; ?>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content Row -->
                        <div class="row">
                            <!-- Sản phẩm sắp hết hàng -->
                            <div class="col-lg-6">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                        <h6 class="m-0 font-weight-bold text-primary">Sản phẩm sắp hết hàng</h6>
                                        <a href="low_stock.php" class="btn btn-warning btn-sm">Xem tất cả</a>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Sản phẩm</th>
                                                        <th>Tồn kho</th>
                                                        <th>Giá trị</th>
                                                        <th>Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(mysqli_num_rows($result_low_stock) > 0): ?>
                                                        <?php while($product = mysqli_fetch_assoc($result_low_stock)): ?>
                                                            <tr>
                                                                <td><?php echo $product['product_name']; ?></td>
                                                                <td class="<?php echo $product['stock_quantity'] < 5 ? 'text-danger' : 'text-warning'; ?>">
                                                                    <strong><?php echo $product['stock_quantity']; ?></strong>
                                                                </td>
                                                                <td><?php echo vnd_format($product['cost_price'] * $product['stock_quantity']); ?></td>
                                                                <td>
                                                                    <a href="stock_in.php?product_id=<?php echo $product['product_id']; ?>" class="btn btn-primary btn-sm">
                                                                        <i class="fas fa-plus-circle"></i> Nhập hàng
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center">Không có sản phẩm nào sắp hết hàng</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nhật ký nhập kho gần đây -->
 

                            <!-- Phân loại sản phẩm -->
                            <div class="col-lg-6">
                                <div class="card shadow mb-4">
                                    <!-- Card Header - Dropdown -->
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Phân loại sản phẩm</h6>
                                    </div>
                                    <!-- Card Body -->
                                    <div class="card-body">
                                        <div class="chart-pie pt-4">
                                            <canvas id="productCategoryChart"></canvas>
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
        // Phân loại sản phẩm
        <?php
        $sql_categories = "SELECT c.category_name, COUNT(p.product_id) as count 
                           FROM categories c
                           LEFT JOIN products p ON c.category_id = p.category_id
                           GROUP BY c.category_id";
        $result_categories = mysqli_query($connect, $sql_categories);
        $category_labels = [];
        $category_data = [];
        
        while($row = mysqli_fetch_assoc($result_categories)) {
            $category_labels[] = $row['category_name'];
            $category_data[] = $row['count'];
        }
        ?>

        // Biểu đồ phân loại sản phẩm
        var ctx = document.getElementById("productCategoryChart");
        var productCategoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($category_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($category_data); ?>,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#5a5c69'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#484a56'],
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