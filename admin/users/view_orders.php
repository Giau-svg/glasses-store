<?php
require '../check_admin_login.php';
require '../root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(empty($_GET['id'])){
    header('location:index.php?error=Phải chọn khách hàng để xem đơn hàng');
    exit;
}

$user_id = $_GET['id'];

// Lấy thông tin khách hàng
$sql_user = "SELECT u.*, r.role_name
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.user_id = '$user_id'";
$result_user = mysqli_query($connect, $sql_user);
$user = mysqli_fetch_assoc($result_user);

if(empty($user)) {
    header('location:../partials/404.php');
    exit;
}

// Lấy danh sách đơn hàng
$sql_orders = "SELECT * FROM orders
        WHERE user_id = '$user_id'
        ORDER BY order_date DESC";
$result_orders = mysqli_query($connect, $sql_orders);

// Format date
function format_date($date) {
    return date('d/m/Y H:i', strtotime($date));
}

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
    <title>Đơn hàng của <?php echo htmlspecialchars($user['full_name']); ?> - EYEGLASSES</title>
    
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
                        <h1 class="h3 mb-0 text-gray-800">Đơn hàng của: <?php echo htmlspecialchars($user['full_name']); ?></h1>
                        <a href="index.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
                        </a>
                    </div>

                    <div class="row">
                        <!-- Customer Info Card -->
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Thông tin khách hàng</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <tr>
                                                <th>Họ tên:</th>
                                                <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Username:</th>
                                                <td><?php echo htmlspecialchars($user['username'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Số điện thoại:</th>
                                                <td><?php echo htmlspecialchars($user['phone'] ?? 'Chưa cập nhật'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Địa chỉ:</th>
                                                <td><?php echo htmlspecialchars($user['address'] ?? 'Chưa cập nhật'); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Vai trò:</th>
                                                <td>
                                                    <span class="badge badge-<?php echo ($user['role_name'] == 'customer') ? 'primary' : 'info'; ?>">
                                                        <?php echo htmlspecialchars($user['role_name']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Ngày đăng ký:</th>
                                                <td><?php echo isset($user['created_at']) ? format_date($user['created_at']) : 'N/A'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <a href="view_details.php?id=<?php echo $user['user_id']; ?>" class="btn btn-primary btn-block">
                                        <i class="fas fa-user fa-sm"></i> Xem chi tiết người dùng
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Orders List Card -->
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn hàng</h6>
                                </div>
                                <div class="card-body">
                                    <?php if(mysqli_num_rows($result_orders) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Mã đơn</th>
                                                        <th>Ngày đặt</th>
                                                        <th>Người nhận</th>
                                                        <th>Tổng tiền</th>
                                                        <th>Trạng thái</th>
                                                        <th>Thao tác</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while($order = mysqli_fetch_assoc($result_orders)): ?>
                                                        <tr>
                                                            <td>#<?php echo $order['order_id']; ?></td>
                                                            <td><?php echo format_date($order['order_date']); ?></td>
                                                            <td><?php echo htmlspecialchars($order['shipping_name'] ?? ''); ?></td>
                                                            <td class="font-weight-bold"><?php echo vnd_format($order['total_amount']); ?></td>
                                                            <td>
                                                                <span class="badge badge-<?php 
                                                                    switch ($order['order_status']) {
                                                                        case 'pending': echo 'warning'; break;
                                                                        case 'processing': echo 'primary'; break;
                                                                        case 'shipped': echo 'info'; break;
                                                                        case 'delivered': echo 'success'; break;
                                                                        case 'cancelled': echo 'danger'; break;
                                                                        default: echo 'secondary';
                                                                    }
                                                                ?>">
                                                                    <?php 
                                                                        switch ($order['order_status']) {
                                                                            case 'pending': echo 'Chờ xử lý'; break;
                                                                            case 'processing': echo 'Đang xử lý'; break;
                                                                            case 'shipped': echo 'Đang giao hàng'; break;
                                                                            case 'delivered': echo 'Hoàn thành'; break;
                                                                            case 'cancelled': echo 'Đã hủy'; break;
                                                                            default: echo $order['order_status'];
                                                                        }
                                                                    ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a href="../orders/detail.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info btn-sm">
                                                                    <i class="fas fa-eye"></i> Chi tiết
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> Khách hàng này chưa có đơn hàng nào.
                                        </div>
                                    <?php endif; ?>
                                </div>
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