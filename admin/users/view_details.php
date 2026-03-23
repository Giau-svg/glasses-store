<?php
require '../check_admin_login.php';
require '../root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(empty($_GET['id'])){
    header('location:index.php?error=Phải chọn khách hàng để xem chi tiết');
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

// Đếm số đơn hàng của khách hàng
$sql_order_count = "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN order_status IN ('pending', 'processing', 'shipped') THEN 1 ELSE 0 END) as active_orders
            FROM orders
            WHERE user_id = '$user_id'";
$result_order_count = mysqli_query($connect, $sql_order_count);
$order_stats = mysqli_fetch_assoc($result_order_count);

// Tính tổng chi tiêu của khách hàng
$sql_total_spent = "SELECT SUM(total_amount) as total_spent
                  FROM orders
                  WHERE user_id = '$user_id' AND order_status = 'delivered'";
$result_total_spent = mysqli_query($connect, $sql_total_spent);
$total_spent = mysqli_fetch_assoc($result_total_spent);

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
    <title>Chi tiết người dùng: <?php echo htmlspecialchars($user['full_name']); ?> - EYEGLASSES</title>
    
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
                        <h1 class="h3 mb-0 text-gray-800">Chi tiết người dùng: <?php echo htmlspecialchars($user['full_name']); ?></h1>
                        <div>
                            <a href="view_orders.php?id=<?php echo $user['user_id']; ?>" class="btn btn-info btn-sm shadow-sm mr-2">
                                <i class="fas fa-shopping-cart fa-sm text-white-50"></i> Xem đơn hàng
                            </a>
                            <a href="index.php" class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại danh sách
                            </a>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Total Orders Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Tổng số đơn hàng</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $order_stats['total_orders'] ?? 0; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Orders Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Đơn hàng đang xử lý</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $order_stats['active_orders'] ?? 0; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Completed Orders Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Đơn hàng hoàn thành</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $order_stats['completed_orders'] ?? 0; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Spent Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Tổng chi tiêu</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo vnd_format($total_spent['total_spent'] ?? 0); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- User Info Card -->
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Thông tin cá nhân</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <tr>
                                                <th style="width: 30%;">ID:</th>
                                                <td><?php echo $user['user_id']; ?></td>
                                            </tr>
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
                                            <tr>
                                                <th>Lần cập nhật cuối:</th>
                                                <td><?php echo isset($user['updated_at']) ? format_date($user['updated_at']) : 'N/A'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info Card -->
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Thống kê đơn hàng</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h5 class="small font-weight-bold">Tỷ lệ hoàn thành đơn hàng <span class="float-right">
                                            <?php 
                                                $completion_rate = 0;
                                                if (isset($order_stats['total_orders']) && $order_stats['total_orders'] > 0) {
                                                    $completion_rate = ($order_stats['completed_orders'] / $order_stats['total_orders']) * 100;
                                                }
                                                echo round($completion_rate, 2) . '%';
                                            ?>
                                        </span></h5>
                                        <div class="progress mb-4">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completion_rate; ?>%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <h5 class="small font-weight-bold">Tỷ lệ hủy đơn hàng <span class="float-right">
                                            <?php 
                                                $cancellation_rate = 0;
                                                if (isset($order_stats['total_orders']) && $order_stats['total_orders'] > 0) {
                                                    $cancellation_rate = ($order_stats['cancelled_orders'] / $order_stats['total_orders']) * 100;
                                                }
                                                echo round($cancellation_rate, 2) . '%';
                                            ?>
                                        </span></h5>
                                        <div class="progress mb-4">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $cancellation_rate; ?>%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="text-center">
                                            <a href="view_orders.php?id=<?php echo $user['user_id']; ?>" class="btn btn-info">
                                                <i class="fas fa-shopping-cart"></i> Xem chi tiết đơn hàng
                                            </a>
                                        </div>
                                    </div>
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