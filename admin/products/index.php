<?php
require '../check_admin_login.php';
require '../root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cập nhật cấu trúc bảng products nếu cần
$check_category_id = mysqli_query($connect, "SHOW COLUMNS FROM products LIKE 'category_id'");
if (mysqli_num_rows($check_category_id) == 0) {
    // Thêm cột category_id nếu chưa tồn tại
    mysqli_query($connect, "ALTER TABLE products ADD COLUMN category_id INT DEFAULT NULL AFTER description");
    echo "<script>console.log('Đã thêm cột category_id vào bảng products');</script>";
}

$check_manufacturer_id = mysqli_query($connect, "SHOW COLUMNS FROM products LIKE 'manufacturer_id'");
if (mysqli_num_rows($check_manufacturer_id) == 0) {
    // Thêm cột manufacturer_id nếu chưa tồn tại
    mysqli_query($connect, "ALTER TABLE products ADD COLUMN manufacturer_id INT DEFAULT NULL AFTER category_id");
    echo "<script>console.log('Đã thêm cột manufacturer_id vào bảng products');</script>";
}

// Xử lý tìm kiếm và lọc
$search = trim($_GET['search'] ?? '');
$category_filter = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Đảm bảo page >= 1
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Xây dựng điều kiện WHERE cho câu truy vấn
$where_conditions = [];
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($connect, $search);
    $where_conditions[] = "p.product_name LIKE '%$search_escaped%'";
}
if ($category_filter > 0) {
    $where_conditions[] = "p.category_id = $category_filter";
}
$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Đếm tổng số bản ghi để phân trang
$sqlCount = "SELECT COUNT(*) as total FROM products p $where_clause";
$resultCount = mysqli_query($connect, $sqlCount);
$row = mysqli_fetch_assoc($resultCount);
$total_records = $row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Lấy danh sách sản phẩm
$sql = "SELECT p.*, c.category_name, m.name as manufacturer_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN manufacturers m ON p.manufacturer_id = m.id
        $where_clause
        ORDER BY p.product_id DESC
        LIMIT $offset, $records_per_page";

$result = mysqli_query($connect, $sql);

// Lấy danh sách danh mục để lọc
$sqlCategories = "SELECT category_id as id, category_name as name FROM categories ORDER BY category_name";
$resultCategories = mysqli_query($connect, $sqlCategories);

// Hàm định dạng tiền tệ
function vnd_format($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản lý Sản phẩm</title>
    
    <!-- Custom fonts for this template-->
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles for this template-->
    <link href="../public/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../public/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <?php include '../partials/sidebar.php'; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?php include '../partials/topbar.php'; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Quản lý Sản phẩm</h1>
                        <a href="form_insert.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Thêm sản phẩm mới
                        </a>
                    </div>

                    <!-- Filter and Search -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm & Lọc</h6>
                        </div>
                        <div class="card-body">
                            <form method="get" class="form-inline">
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="search" class="sr-only">Tìm kiếm</label>
                                    <input type="text" class="form-control" id="search" name="search" placeholder="Tên sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="category_id" class="sr-only">Danh mục</label>
                                    <select class="form-control" id="category_id" name="category_id">
                                        <option value="0">-- Tất cả danh mục --</option>
                                        <?php while ($category = mysqli_fetch_assoc($resultCategories)): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo ($category_filter == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary mb-2">
                                    <i class="fas fa-search fa-sm"></i> Tìm kiếm
                                </button>
                                <a href="index.php" class="btn btn-secondary mb-2 ml-2">
                                    <i class="fas fa-sync-alt fa-sm"></i> Đặt lại
                                </a>
                            </form>
                        </div>
                    </div>

                    <!-- Products List -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách Sản phẩm</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Hình ảnh</th>
                                            <th>Tên sản phẩm</th>
                                            <th>Danh mục</th>
                                            <th>Nhà sản xuất</th>
                                            <th>Giá</th>
                                            <th>Tồn kho</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($product = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td><?php echo $product['product_id']; ?></td>
                                                    <td class="text-center">
                                                        <?php if (!empty($product['image_path'])): ?>
                                                            <img src="uploads/<?php echo $product['image_path']; ?>" width="50" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                                        <?php else: ?>
                                                            <span class="text-muted">No image</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($product['manufacturer_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo vnd_format($product['price']); ?></td>
                                                    <td><?php echo $product['stock_quantity']; ?></td>
                                                    <td>
                                                        <a href="form_update.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="delete.php?id=<?php echo $product['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center">Không tìm thấy sản phẩm nào</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mt-4">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category_id=<?php echo $category_filter; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
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

    <!-- Page level plugins -->
    <script src="../public/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../public/vendor/datatables/dataTables.bootstrap4.min.js"></script>
</body>
</html>