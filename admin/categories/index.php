<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Kiểm tra phiên đăng nhập
if (!isset($_SESSION['admin_level']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    header('location:../index.php?error=Vui lòng đăng nhập để tiếp tục');
    exit();
}

try {
    // Thử kết nối trực tiếp với database eyeglasses_shop (không pure)
    $connect = mysqli_connect('localhost', 'root', '', 'eyeglasses_shop');
    if (!$connect) {
        throw new Exception("Không thể kết nối đến cơ sở dữ liệu: " . mysqli_connect_error());
    }
    
    // Thiết lập UTF-8
    mysqli_set_charset($connect, 'utf8');
    
    // Thiết lập phân trang
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Biến tìm kiếm
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $search_clause = '';
    if (!empty($search)) {
        $search_escaped = mysqli_real_escape_string($connect, $search);
        $search_clause = " WHERE category_name LIKE '%$search_escaped%' OR description LIKE '%$search_escaped%'";
    }

    // Đếm tổng số bản ghi
    $count_sql = "SELECT COUNT(*) as total FROM categories" . $search_clause;
    $count_result = mysqli_query($connect, $count_sql);
    if (!$count_result) {
        throw new Exception("Lỗi truy vấn đếm danh mục: " . mysqli_error($connect));
    }
    $count_row = mysqli_fetch_assoc($count_result);
    $total_records = $count_row['total'];
    $total_pages = ceil($total_records / $limit);

    // Lấy danh sách danh mục - không sử dụng category_id mà dùng id nếu đó là tên cột thực tế
    $sql = "SHOW COLUMNS FROM categories LIKE 'category_id'";
    $column_check = mysqli_query($connect, $sql);
    
    if (mysqli_num_rows($column_check) > 0) {
        // Nếu cột category_id tồn tại
        $sql = "SELECT * FROM categories" . $search_clause . " ORDER BY category_id DESC LIMIT $offset, $limit";
    } else {
        // Nếu không tồn tại, thử dùng id
        $sql = "SELECT * FROM categories" . $search_clause . " ORDER BY id DESC LIMIT $offset, $limit";
    }
    
    $result = mysqli_query($connect, $sql);

    // Kiểm tra lỗi
    if (!$result) {
        throw new Exception("Lỗi truy vấn lấy danh mục: " . mysqli_error($connect));
    }
} catch (Exception $e) {
    // Hiển thị lỗi chi tiết
    echo '<div style="margin: 20px; padding: 20px; border: 1px solid red; background-color: #ffeeee;">';
    echo '<h2>Lỗi kết nối hoặc truy vấn:</h2>';
    echo '<p>' . $e->getMessage() . '</p>';
    
    // Kiểm tra thêm thông tin về MySQL
    echo '<h3>Thông tin hệ thống:</h3>';
    echo '<p>PHP Version: ' . phpversion() . '</p>';
    echo '<p>MySQLi Extension: ' . (extension_loaded('mysqli') ? 'Đã cài đặt' : 'Chưa cài đặt') . '</p>';
    
    // Kiểm tra kết nối đến MySQL
    try {
        $test_connect = mysqli_connect('localhost', 'root', '');
        echo '<p>Kết nối MySQL không DB: ' . ($test_connect ? 'Thành công' : 'Thất bại') . '</p>';
        if ($test_connect) {
            // Kiểm tra xem database eyeglasses_shop có tồn tại không
            $result = mysqli_query($test_connect, "SHOW DATABASES LIKE 'eyeglasses_shop'");
            echo '<p>Database "eyeglasses_shop" tồn tại: ' . (mysqli_num_rows($result) > 0 ? 'Có' : 'Không') . '</p>';
            
            if (mysqli_num_rows($result) > 0) {
                // Kiểm tra cấu trúc bảng categories
                mysqli_select_db($test_connect, 'eyeglasses_shop');
                $table_result = mysqli_query($test_connect, "SHOW TABLES LIKE 'categories'");
                echo '<p>Bảng "categories" tồn tại: ' . (mysqli_num_rows($table_result) > 0 ? 'Có' : 'Không') . '</p>';
                
                if (mysqli_num_rows($table_result) > 0) {
                    $cols_result = mysqli_query($test_connect, "SHOW COLUMNS FROM categories");
                    echo '<p>Cấu trúc bảng categories:</p><ul>';
                    while ($col = mysqli_fetch_assoc($cols_result)) {
                        echo '<li>' . $col['Field'] . ' (' . $col['Type'] . ')</li>';
                    }
                    echo '</ul>';
                }
            }
            
            mysqli_close($test_connect);
        }
    } catch (Exception $e2) {
        echo '<p>Lỗi kết nối MySQL: ' . $e2->getMessage() . '</p>';
    }
    
    echo '</div>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Quản lý danh mục</title>

    <!-- Custom fonts for this template-->
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../public/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include "../partials/sidebar.php"; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include "../partials/topbar.php"; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Quản lý danh mục</h1>
                    <p class="mb-4">Quản lý tất cả danh mục trong hệ thống.</p>

                    <!-- Search Form -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm danh mục</h6>
                        </div>
                        <div class="card-body">
                            <form action="" method="GET" class="form-inline">
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="search" class="sr-only">Tìm kiếm</label>
                                    <input type="text" class="form-control" id="search" name="search" placeholder="Tìm kiếm danh mục..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary mb-2">Tìm kiếm</button>
                                <?php if (!empty($search)) : ?>
                                    <a href="index.php" class="btn btn-secondary mb-2 ml-2">Đặt lại</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Categories Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách danh mục</h6>
                            <a href="form_insert.php" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Thêm danh mục mới
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Hình ảnh</th>
                                            <th>Tên danh mục</th>
                                            <th>Loại</th>
                                            <th>Mô tả</th>
                                            <th>Ngày tạo</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0) : ?>
                                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                                <?php 
                                                // Xác định id field dựa trên cấu trúc bảng
                                                $id_field = isset($row['category_id']) ? 'category_id' : 'id';
                                                ?>
                                                <tr>
                                                    <td><?php echo $row[$id_field]; ?></td>
                                                    <td>
                                                        <?php 
                                                        $image_exists = false;
                                                        if (!empty($row['image'])) {
                                                            $image_path = '../' . $row['image'];
                                                            if (file_exists($image_path)) {
                                                                $image_exists = true;
                                                            }
                                                        }
                                                        
                                                        if ($image_exists): 
                                                        ?>
                                                            <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($row['category_name']); ?>" width="50" height="50" style="object-fit: cover;">
                                                        <?php else: ?>
                                                            <img src="../public/img/no-image.png" alt="No Image" width="50" height="50">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['type'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                                    <td>
                                                        <a href="form_update.php?id=<?php echo $row[$id_field]; ?>" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i> Sửa
                                                        </a>
                                                        <a href="delete.php?id=<?php echo $row[$id_field]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này không?');">
                                                            <i class="fas fa-trash"></i> Xóa
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Không tìm thấy danh mục nào</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1) : ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1) : ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages) : ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
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
            <?php include "../partials/footer.php"; ?>
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