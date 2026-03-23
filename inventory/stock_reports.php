<?php
require_once '../check_inventory_login.php';
require_once '../../admin/root.php';
require_once '../../include/pagination_class.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set active page
$active_page = 'stock_quantity_reports';
$page_title = 'Báo cáo kho';

// Database connection configuration
$db_host = 'localhost';  // XAMPP default host
$db_user = 'root';       // XAMPP default user
$db_pass = '';          // XAMPP default password
$db_name = 'eyeglasses_shop';      // Your database name

// Database connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8");

// Get filter values
$category = isset($_GET['category']) ? $_GET['category'] : '';
$stock_quantity_status = isset($_GET['stock_quantity_status']) ? $_GET['stock_quantity_status'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build WHERE clause based on filters
$where_clause = "WHERE 1=1";
if (!empty($category)) {
    $where_clause .= " AND p.category_id = '" . mysqli_real_escape_string($conn, $category) . "'";
}
if (!empty($search)) {
    $where_clause .= " AND (p.product_name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' OR p.product_id LIKE '%" . mysqli_real_escape_string($conn, $search) . "%')";
}

// Định nghĩa ngưỡng tồn kho thấp (có thể điều chỉnh theo nhu cầu)
$low_stock_quantity_threshold = 10;

switch ($stock_quantity_status) {
    case 'in_stock_quantity':
        $where_clause .= " AND p.stock_quantity > $low_stock_quantity_threshold";
        break;
    case 'low_stock_quantity':
        $where_clause .= " AND p.stock_quantity <= $low_stock_quantity_threshold AND p.stock_quantity > 0";
        break;
    case 'out_of_stock_quantity':
        $where_clause .= " AND p.stock_quantity = 0";
        break;
}

// Build ORDER BY clause
$order_by = "ORDER BY ";
switch ($sort_by) {
    case 'stock_quantity':
        $order_by .= "p.stock_quantity DESC";
        break;
    case 'value':
        $order_by .= "(p.stock_quantity * p.price) DESC";
        break;
    default:
        $order_by .= "p.product_name ASC";
}

// Get dashboard statistics
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'];
$low_stock_quantity = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products p WHERE p.stock_quantity <= $low_stock_quantity_threshold AND p.stock_quantity > 0"))['total'];
$out_of_stock_quantity = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products p WHERE p.stock_quantity = 0"))['total'];
$total_value = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stock_quantity * price) as total FROM products"))['total'];

// Get categories for dropdown
$categories_query = "SELECT category_id, category_name FROM categories ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_query);

// Get products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 3;
$offset = ($page - 1) * $records_per_page;

$products_query = "
    SELECT 
        p.product_id,
        p.product_name,
        p.stock_quantity,
        p.price as unit_price,
        c.category_name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    $where_clause
    $order_by
    LIMIT $offset, $records_per_page
";

$total_query = "
    SELECT COUNT(DISTINCT p.product_id) as total
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    $where_clause
";

$products_result = mysqli_query($conn, $products_query);
if (!$products_result) {
    die("Query failed: " . mysqli_error($conn));
}
$total_records = mysqli_fetch_assoc(mysqli_query($conn, $total_query))['total'];

$total_pages = ceil($total_records / $records_per_page);

// Format number to currency
function format_currency($number) {
    return number_format($number, 0, ',', '.') . ' đ';
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include '../partials/head.php'; ?>
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
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Total Products Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Tổng sản phẩm</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_products; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Low stock_quantity Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Tồn kho thấp</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $low_stock_quantity; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Out of stock_quantity Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Hết hàng</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $out_of_stock_quantity; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Value Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Giá trị tồn kho</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo format_currency($total_value); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc báo cáo</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="" class="row">

                                
                                <div class="col-md-3 mb-3">
                                    <label for="stock_quantity_status" class="form-label small font-weight-bold">Trạng thái tồn</label>
                                    <select class="form-control" id="stock_quantity_status" name="stock_quantity_status">
                                        <option value="">-- Tất cả --</option>
                                        <option value="in_stock_quantity" <?php echo $stock_quantity_status == 'in_stock_quantity' ? 'selected' : ''; ?>>Còn hàng</option>
                                        <option value="low_stock_quantity" <?php echo $stock_quantity_status == 'low_stock_quantity' ? 'selected' : ''; ?>>Sắp hết hàng</option>
                                        <option value="out_of_stock_quantity" <?php echo $stock_quantity_status == 'out_of_stock_quantity' ? 'selected' : ''; ?>>Hết hàng</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="sort_by" class="form-label small font-weight-bold">Sắp xếp theo</label>
                                    <select class="form-control" id="sort_by" name="sort_by">
                                        <option value="name" <?php echo $sort_by == 'name' ? 'selected' : ''; ?>>Tên sản phẩm</option>
                                        <option value="stock_quantity" <?php echo $sort_by == 'stock_quantity' ? 'selected' : ''; ?>>Số lượng tồn</option>
                                        <option value="value" <?php echo $sort_by == 'value' ? 'selected' : ''; ?>>Giá trị</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="search" class="form-label small font-weight-bold">Tìm kiếm</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="search" name="search" 
                                               placeholder="Tên, mã sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter mr-1"></i> Lọc
                                    </button>
                                    <a href="stock_quantity_reports.php" class="btn btn-secondary ml-2">
                                        <i class="fas fa-sync-alt mr-1"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- stock_quantity Report Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Báo cáo tồn kho</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">Mã SP</th>
                                            <th scope="col">Tên sản phẩm</th>
                                            <th scope="col">Danh mục</th>
                                            <th scope="col" class="text-right">Tồn kho</th>
                                            <th scope="col" class="text-right">Giá bán</th>
                                            <th scope="col" class="text-right">Giá trị tồn</th>
                                            <th scope="col" class="text-center">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($products_result) > 0): ?>
                                            <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($product['product_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                    <td class="text-right"><?php echo number_format($product['stock_quantity']); ?></td>
                                                    <td class="text-right"><?php echo format_currency($product['unit_price']); ?></td>
                                                    <td class="text-right"><?php echo format_currency($product['stock_quantity'] * $product['unit_price']); ?></td>
                                                    <td class="text-center">
                                                        <?php if ($product['stock_quantity'] == 0): ?>
                                                            <span class="badge badge-danger">Hết hàng</span>
                                                        <?php elseif ($product['stock_quantity'] <= $low_stock_quantity_threshold): ?>
                                                            <span class="badge badge-warning">Sắp hết hàng</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-success">Còn hàng</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7">
                                                    <div class="empty-state text-center py-5">
                                                        <div class="icon mb-3">
                                                            <i class="fas fa-chart-bar fa-4x text-gray-300"></i>
                                                        </div>
                                                        <h5 class="text-gray-800 mb-1">Chưa có dữ liệu báo cáo</h5>
                                                        <p class="text-gray-600 mb-3">
                                                            Hãy thử thay đổi điều kiện lọc hoặc thêm sản phẩm vào kho.
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-center mt-4">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination->getPreviousPage(); ?>&search=<?php echo urlencode($search); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&supplier_id=<?php echo urlencode($supplier_id); ?>" aria-label="Previous">
                                                 Previous </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $category; ?>&stock_quantity_status=<?php echo $stock_quantity_status; ?>&sort_by=<?php echo $sort_by; ?>&search=<?php echo urlencode($search); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page+1); ?>&category=<?php echo $category; ?>&stock_quantity_status=<?php echo $stock_quantity_status; ?>&sort_by=<?php echo $sort_by; ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
                                                Next </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

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

    <!-- Custom scripts for all pages-->
    <script src="../../admin/js/sb-admin-2.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[title]').tooltip();
        
        // Print report
        $('#printReport').click(function() {
            window.print();
        });
        
        // Export to Excel functionality
        $('#exportExcel').click(function() {
            var params = new URLSearchParams(window.location.search);
            params.append('export', 'excel');
            window.location.href = 'export_stock_quantity_report.php?' + params.toString();
        });
    });
    </script>
</body>
</html> 