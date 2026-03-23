<?php
require '../check_admin_login.php';
require '../root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Xử lý tìm kiếm
$search = trim($_GET['search'] ?? '');

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Đảm bảo page >= 1
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Xây dựng điều kiện WHERE cho câu truy vấn - chỉ hiển thị khách hàng
$where_conditions = ["r.role_name = 'customer'"]; // Chỉ hiển thị tài khoản khách hàng

if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($connect, $search);
    $where_conditions[] = "(u.full_name LIKE '%$search_escaped%' 
        OR u.username LIKE '%$search_escaped%' 
        OR u.email LIKE '%$search_escaped%' 
        OR u.phone LIKE '%$search_escaped%')";
}
$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Đếm tổng số bản ghi để phân trang
$sqlCount = "SELECT COUNT(*) as total 
            FROM users u 
            JOIN roles r ON u.role_id = r.role_id 
            $where_clause";
$resultCount = mysqli_query($connect, $sqlCount);
$row = mysqli_fetch_assoc($resultCount);
$total_records = $row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Lấy danh sách người dùng
$sql = "SELECT u.*, r.role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.role_id
        $where_clause
        ORDER BY u.user_id DESC
        LIMIT $offset, $records_per_page";
$result = mysqli_query($connect, $sql);

// Format date
function format_date($date) {
    return date('d/m/Y H:i', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản lý Khách hàng - EYEGLASSES</title>
    
    <!-- Custom fonts for this template-->
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles for this template-->
    <link href="../public/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../public/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <script>
        // Tự động làm mới trang sau mỗi 60 giây
        setTimeout(function() {
            window.location.reload();
        }, 60000);
    </script>
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
                        <h1 class="h3 mb-0 text-gray-800">Quản lý Khách hàng</h1>
                        <div>
                            <span id="refreshCountdown" class="text-muted mr-2">Làm mới sau: 60s</span>
                            <a href="javascript:window.location.reload();" class="btn btn-sm btn-primary">
                                <i class="fas fa-sync-alt fa-sm"></i> Làm mới ngay
                            </a>
                        </div>
                    </div>

                    <!-- Filter and Search -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm & Lọc</h6>
                        </div>
                        <div class="card-body">
                            <form method="get" class="mb-3">
                                <div class="row">
                                    <div class="col-md-8 mb-2">
                                        <input type="text" class="form-control" id="search" name="search" placeholder="Tên, username, email, số điện thoại..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-search fa-sm"></i> Tìm kiếm
                                        </button>
                                        <a href="index.php" class="btn btn-secondary">
                                            <i class="fas fa-sync-alt fa-sm"></i> Đặt lại
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Users List -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách Khách hàng (<?php echo $total_records; ?> khách hàng)</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Họ tên</th>
                                            <th>Tài khoản & Email</th>
                                            <th>Liên hệ</th>
                                            <th>Ngày đăng ký</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td><?php echo $user['user_id']; ?></td>
                                                    <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                                                    <td>
                                                        <strong>Username:</strong> <?php echo htmlspecialchars($user['username'] ?? ''); ?><br>
                                                        <strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?>
                                                    </td>
                                                    <td>
                                                        <strong>SĐT:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Chưa cập nhật'); ?><br>
                                                        <strong>Địa chỉ:</strong> <?php echo htmlspecialchars($user['address'] ?? 'Chưa cập nhật'); ?>
                                                    </td>
                                                    <td><?php echo isset($user['created_at']) ? format_date($user['created_at']) : 'N/A'; ?></td>
                                                    <td>
                                                        <a href="view_orders.php?id=<?php echo $user['user_id']; ?>" class="btn btn-info btn-sm mb-1">
                                                            <i class="fas fa-shopping-cart"></i> Xem đơn hàng
                                                        </a>
                                                        <a href="view_details.php?id=<?php echo $user['user_id']; ?>" class="btn btn-primary btn-sm mb-1">
                                                            <i class="fas fa-user"></i> Chi tiết
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Không tìm thấy khách hàng nào</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                            <div class="mt-3">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let seconds = 60;
            const countdownElement = document.getElementById('refreshCountdown');
            
            const interval = setInterval(function() {
                seconds--;
                countdownElement.textContent = `Làm mới sau: ${seconds}s`;
                
                if (seconds <= 0) {
                    clearInterval(interval);
                }
            }, 1000);
        });
    </script>
</body>
</html>