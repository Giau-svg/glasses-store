<?php
require '../busmanage/check_business_manager_login.php';
require '../busmanage/root.php';
require_once '../include/pagination_class.php';

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra kết nối database
if (!$connect) {
    die("Lỗi kết nối database: " . mysqli_connect_error());
}
mysqli_set_charset($connect, "utf8");

// Đặt trang hiện tại
$active_page = 'stock_report';
$page_title = 'Báo Cáo Tồn Kho';

// Lấy giá trị bộ lọc
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : '';
$brand_id = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : '';
$min_stock = isset($_GET['min_stock']) ? (int)$_GET['min_stock'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'product_id';

// Xây dựng điều kiện WHERE cho bộ lọc
$where_clause = "WHERE 1=1";
if (!empty($category_id)) {
    $where_clause .= " AND p.category_id = " . mysqli_real_escape_string($connect, $category_id);
}
if (!empty($brand_id)) {
    $where_clause .= " AND p.brand_id = " . mysqli_real_escape_string($connect, $brand_id);
}
if (!empty($min_stock)) {
    $where_clause .= " AND p.stock_quantity >= " . mysqli_real_escape_string($connect, $min_stock);
}
if (!empty($search)) {
    $where_clause .= " AND (p.product_name LIKE '%" . mysqli_real_escape_string($connect, $search) . "%' OR p.product_id LIKE '%" . mysqli_real_escape_string($connect, $search) . "%')";
}

// Xây dựng điều kiện ORDER BY
$order_by = "ORDER BY ";
switch ($sort_by) {
    case 'stock_quantity':
        $order_by .= "p.stock_quantity DESC";
        break;
    case 'price':
        $order_by .= "p.price DESC";
        break;
    default:
        $order_by .= "p.product_id ASC";
}

// Lấy danh sách danh mục và thương hiệu cho dropdown
$categories_query = "SELECT category_id, category_name FROM categories ORDER BY category_name";
$categories_result = mysqli_query($connect, $categories_query);

$brands_query = "SELECT brand_id, brand_name FROM brands ORDER BY brand_name";
$brands_result = mysqli_query($connect, $brands_query);

// Lấy thống kê cho bảng điều khiển và modal
$total_products = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as total FROM products"))['total'];
$low_stock_products = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as total FROM products WHERE stock_quantity < 50"))['total'];
$critical_stock_products = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) as total FROM products WHERE stock_quantity < 20"))['total'];
$total_value = mysqli_fetch_assoc(mysqli_query($connect, "SELECT SUM(p.price * p.stock_quantity) as total FROM products p"))['total'];

// Thống kê sản phẩm theo danh mục cho modal
$category_stats_query = "
    SELECT c.category_name, COUNT(p.product_id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.category_id = p.category_id
    GROUP BY c.category_id, c.category_name
";
$category_stats_result = mysqli_query($connect, $category_stats_query);

// Lấy danh sách sản phẩm với phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

$products_query = "
    SELECT p.product_id, p.product_name, c.category_name, p.stock_quantity, p.price, p.brand_id, b.brand_name
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    JOIN brands b ON p.brand_id = b.brand_id
    $where_clause
    $order_by
    LIMIT $offset, $records_per_page
";

$total_query = "
    SELECT COUNT(*) as total
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    JOIN brands b ON p.brand_id = b.brand_id
    $where_clause
";

$products = mysqli_query($connect, $products_query);
if (!$products) {
    die("Lỗi truy vấn danh sách sản phẩm: " . mysqli_error($connect));
}
$total_records = mysqli_fetch_assoc(mysqli_query($connect, $total_query))['total'];
$total_pages = ceil($total_records / $records_per_page);

// Format số tiền thành định dạng tiền tệ
function format_currency($number) {
    return number_format($number, 0, ',', '.') . ' đ';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $page_title; ?> - EYEGLASSES</title>
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .table thead th {
            background-color: #f8f9fc;
        }
        .badge {
            padding: 5px 10px;
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include '../partials/busmanage_sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include '../partials/busmanage_topbar.php'; ?>
                <div class="container-fluid">
                    <!-- Tiêu đề trang -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
                        <div>
                            <button class="btn btn-sm btn-info shadow-sm mr-2" data-toggle="modal" data-target="#statsModal">
                                <i class="fas fa-chart-bar fa-sm text-white-50"></i> Thống kê
                            </button>
                            <button class="btn btn-sm btn-primary shadow-sm" id="printReport">
                                <i class="fas fa-print fa-sm text-white-50"></i> In báo cáo
                            </button>
                        </div>
                    </div>

                    <!-- Modal Thống Kê -->
                    <div class="modal fade" id="statsModal" tabindex="-1" role="dialog" aria-labelledby="statsModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="statsModalLabel">Thống Kê Tồn Kho</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-primary shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                                Tổng sản phẩm</div>
                                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_products; ?></div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-box fa-2x text-gray-300"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-warning shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                                Sản phẩm tồn kho thấp (<50)</div>
                                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $low_stock_products; ?></div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-danger shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                                Sản phẩm sắp hết (<20)</div>
                                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $critical_stock_products; ?></div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-success shadow h-100 py-2">
                                                <div class="card-body">
                                                    <div class="row no-gutters align-items-center">
                                                        <div class="col mr-2">
                                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                                Tổng giá trị tồn kho</div>
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
                                    <div class="mt-4">
                                        <h6 class="font-weight-bold text-primary">Số sản phẩm theo danh mục</h6>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Danh mục</th>
                                                    <th>Số sản phẩm</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($stat = $category_stats_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($stat['category_name']); ?></td>
                                                        <td><?php echo $stat['product_count']; ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hàng thẻ thống kê -->
                    <div class="row">
                        <!-- Thẻ tổng số sản phẩm -->
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
                                            <i class="fas fa-box fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thẻ sản phẩm tồn kho thấp -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Sản phẩm tồn kho thấp (<50)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $low_stock_products; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thẻ sản phẩm sắp hết hàng -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Sản phẩm sắp hết (<20)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $critical_stock_products; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Thẻ tổng giá trị tồn kho -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Tổng giá trị tồn kho</div>
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

                    <!-- Thẻ bộ lọc -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc tồn kho</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="" class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="category_id" class="form-label small font-weight-bold">Danh mục</label>
                                    <select class="form-control" id="category_id" name="category_id">
                                        <option value="">-- Tất cả --</option>
                                        <?php while ($category = $categories_result->fetch_assoc()): ?>
                                            <option value="<?php echo $category['category_id']; ?>" <?php echo $category_id == $category['category_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="brand_id" class="form-label small font-weight-bold">Thương hiệu</label>
                                    <select class="form-control" id="brand_id" name="brand_id">
                                        <option value="">-- Tất cả --</option>
                                        <?php $brands_result->data_seek(0); while ($brand = $brands_result->fetch_assoc()): ?>
                                            <option value="<?php echo $brand['brand_id']; ?>" <?php echo $brand_id == $brand['brand_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($brand['brand_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="min_stock" class="form-label small font-weight-bold">Tồn kho tối thiểu</label>
                                    <input type="number" class="form-control" id="min_stock" name="min_stock" value="<?php echo htmlspecialchars($min_stock); ?>" min="0">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="search" class="form-label small font-weight-bold">Tìm kiếm</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="search" name="search" placeholder="Mã SP, Tên SP..." value="<?php echo htmlspecialchars($search); ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="sort_by" class="form-label small font-weight-bold">Sắp xếp theo</label>
                                    <select class="form-control" id="sort_by" name="sort_by">
                                        <option value="product_id" <?php echo $sort_by == 'product_id' ? 'selected' : ''; ?>>Mã SP</option>
                                        <option value="stock_quantity" <?php echo $sort_by == 'stock_quantity' ? 'selected' : ''; ?>>Tồn kho</option>
                                        <option value="price" <?php echo $sort_by == 'price' ? 'selected' : ''; ?>>Giá bán</option>
                                    </select>
                                </div>
                                <div class="col-12 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter mr-1"></i> Lọc
                                    </button>
                                    <a href="index.php" class="btn btn-secondary ml-2">
                                        <i class="fas fa-sync-alt mr-1"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Bảng danh sách tồn kho -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Danh Sách Tồn Kho</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>MÃ SP</th>
                                            <th>TÊN SẢN PHẨM</th>
                                            <th>DANH MỤC</th>
                                            <th>TỒN KHO</th>
                                            <th>GIÁ BÁN</th>
                                            <th>GIÁ TRỊ TỒN</th>
                                            <th>TRẠNG THÁI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($products) > 0): ?>
                                            <?php while ($product = $products->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $product['product_id']; ?></td>
                                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                    <td><?php echo $product['stock_quantity']; ?></td>
                                                    <td><?php echo format_currency($product['price']); ?></td>
                                                    <td><?php echo format_currency($product['price'] * $product['stock_quantity']); ?></td>
                                                    <td class="text-center">
                                                        <span class="badge badge-warning"><?php echo $product['stock_quantity'] > 0 ? 'Còn hàng' : 'Hết hàng'; ?></span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7">
                                                    <div class="empty-state text-center py-5">
                                                        <div class="icon mb-3">
                                                            <i class="fas fa-box fa-4x text-gray-300"></i>
                                                        </div>
                                                        <h5 class="text-gray-800 mb-1">Chưa có dữ liệu tồn kho</h5>
                                                        <p class="text-gray-600 mb-3">
                                                            Hãy thử thay đổi điều kiện lọc hoặc kiểm tra lại dữ liệu.
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
                                    <nav aria-label="Phân trang">
                                        <ul class="pagination">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo ($page - 1); ?>&category_id=<?php echo $category_id; ?>&brand_id=<?php echo $brand_id; ?>&min_stock=<?php echo $min_stock; ?>&search=<?php echo urlencode($search); ?>&sort_by=<?php echo $sort_by; ?>" aria-label="Trước">
                                                        Trước
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>&category_id=<?php echo $category_id; ?>&brand_id=<?php echo $brand_id; ?>&min_stock=<?php echo $min_stock; ?>&search=<?php echo urlencode($search); ?>&sort_by=<?php echo $sort_by; ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                            <?php if ($page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo ($page + 1); ?>&category_id=<?php echo $category_id; ?>&brand_id=<?php echo $brand_id; ?>&min_stock=<?php echo $min_stock; ?>&search=<?php echo urlencode($search); ?>&sort_by=<?php echo $sort_by; ?>" aria-label="Tiếp">
                                                        Tiếp
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright © EYEGLASSES 2025</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <script src="../public/vendor/jquery/jquery.min.js"></script>
    <script src="../public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.easing@1.4.1/jquery.easing.min.js"></script>
    <script src="../public/js/sb-admin-2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#printReport').click(function() {
                window.print();
            });
        });
    </script>
</body>
</html>