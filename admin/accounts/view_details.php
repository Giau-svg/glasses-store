<?php
require '../check_super_admin_login.php';
require '../root.php';

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra ID được truyền qua URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("<div class='alert alert-danger'>ID tài khoản không hợp lệ.</div>");
}

$user_id = (int)$_GET['id'];

// Lấy thông tin người dùng
$sql = "SELECT u.*, r.role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.role_id 
        WHERE u.user_id = ?";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    die("<div class='alert alert-danger'>Không tìm thấy tài khoản.</div>");
}

$user = mysqli_fetch_assoc($result);

// Hàm định dạng ngày
function format_date($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Hàm hiển thị badge vai trò
function get_role_badge($role_name) {
    switch ($role_name) {
        case 'admin':
            return '<span class="badge badge-primary">Admin</span>';
        case 'sales':
            return '<span class="badge badge-info">Nhân viên bán hàng</span>';
        case 'stock':
            return '<span class="badge badge-warning">Nhân viên kho</span>';
        case 'inventory':
            return '<span class="badge badge-dark">Quản lý kho hàng</span>';
        default:
            return '<span class="badge badge-secondary">' . htmlspecialchars($role_name) . '</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Chi tiết Tài khoản - EYEGLASSES</title>
    
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../public/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include '../partials/sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include '../partials/topbar.php'; ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Chi tiết Tài khoản</h1>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin Tài khoản #<?php echo $user['user_id']; ?></h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ID:</strong> <?php echo $user['user_id']; ?></p>
                                    <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($user['full_name'] ?? 'Chưa cập nhật'); ?></p>
                                    <p><strong>Tài khoản:</strong> <?php echo htmlspecialchars($user['username'] ?? 'Chưa cập nhật'); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? 'Chưa cập nhật'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Chưa cập nhật'); ?></p>
                                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($user['address'] ?? 'Chưa cập nhật'); ?></p>
                                    <p><strong>Vai trò:</strong> <?php echo get_role_badge($user['role_name']); ?></p>
                                    <p><strong>Ngày tạo:</strong> <?php echo isset($user['created_at']) ? format_date($user['created_at']) : 'N/A'; ?></p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="form_update.php?id=<?php echo $user['user_id']; ?>" class="btn btn-info">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include '../partials/footer.php'; ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="../public/vendor/jquery/jquery.min.js"></script>
    <script src="../public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../public/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../public/js/sb-admin-2.min.js"></script>
</body>
</html>