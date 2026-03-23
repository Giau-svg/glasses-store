<?php
// Đường dẫn tương đối đến check_business_manager_login.php và root.php
require '../busmanage/check_business_manager_login.php';
require '../busmanage/root.php';

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra kết nối database
if (!$connect) {
    die("Lỗi kết nối database: " . mysqli_connect_error());
}

// Xác định hành động (list, view)
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'list') {
    // Lấy danh sách tài khoản
    $stmt = $connect->prepare("SELECT u.user_id, u.username, u.email, r.role_name 
                               FROM users u 
                               JOIN roles r ON u.role_id = r.role_id 
                               ORDER BY u.user_id DESC");
    if (!$stmt) {
        die("Lỗi chuẩn bị truy vấn: " . $connect->error);
    }
    $stmt->execute();
    $users = $stmt->get_result();
    $stmt->close();
} elseif ($action == 'view') {
    // Xem chi tiết tài khoản
    if (!isset($_GET['id'])) {
        header('Location: index.php');
        exit();
    }
    $user_id = (int)$_GET['id'];
    $stmt = $connect->prepare("
        SELECT u.user_id, u.username, u.email, r.role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.role_id 
        WHERE u.user_id = ?
    ");
    if (!$stmt) {
        die("Lỗi chuẩn bị truy vấn: " . $connect->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản Lý Tài Khoản - EYEGLASSES</title>
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
    
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Đường dẫn tương đối đến busmanage_sidebar.php -->
        <?php include '../partials/busmanage_sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Đường dẫn tương đối đến busmanage_topbar.php -->
                <?php include '../partials/busmanage_topbar.php'; ?>
                <div class="container-fluid">
                    <?php if ($action == 'list'): ?>
                        <h1 class="h3 mb-4 text-gray-800">Danh Sách Tài Khoản</h1>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Danh Sách Tài Khoản</h6>
                                <div>
                                    <a href="manage.php?action=add" class="btn btn-success btn-sm">Thêm Tài Khoản</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Tên Người Dùng</th>
                                                <th>Email</th>
                                                <th>Vai Trò</th>
                                                <th>Hành Động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = $users->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $user['user_id']; ?></td>
                                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                                                    <td>
                                                        <a href="index.php?action=view&id=<?php echo $user['user_id']; ?>" class="btn btn-info btn-sm">Xem</a>
                                                        <a href="manage.php?action=edit&id=<?php echo $user['user_id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                                                        <a href="manage.php?action=delete&id=<?php echo $user['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?')">Xóa</a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($action == 'view'): ?>
                        <h1 class="h3 mb-4 text-gray-800">Chi Tiết Tài Khoản #<?php echo $user['user_id']; ?></h1>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Thông Tin Tài Khoản</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Mã Tài Khoản:</strong> #<?php echo $user['user_id']; ?></p>
                                        <p><strong>Tên Người Dùng:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                        <p><strong>Vai Trò:</strong> <?php echo htmlspecialchars($user['role_name']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="index.php" class="btn btn-secondary">Quay lại</a>
                    <?php endif; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>
</body>
</html>