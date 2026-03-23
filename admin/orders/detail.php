<?php
require '../check_admin_login.php';
require '../root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$order_id = $_GET['id'] ?? 0;
if (empty($order_id)) {
    header('location:index.php');
    exit;
}

// Lấy thông tin đơn hàng
$sql = "SELECT o.*, u.full_name, u.email, u.phone 
        FROM orders o
        LEFT JOIN users u ON u.user_id = o.user_id
        WHERE o.order_id = '$order_id'";
$result_order = mysqli_query($connect, $sql);
$order = mysqli_fetch_assoc($result_order);

if (!$order) {
    header('location:../partials/404.php');
    exit;
}

// Lấy chi tiết đơn hàng
$sql = "SELECT od.*, p.product_name, p.image_path, c.category_name, m.name as manufacturer_name
        FROM order_details od
        JOIN products p ON p.product_id = od.product_id
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN manufacturers m ON p.manufacturer_id = m.id
        WHERE od.order_id = '$order_id'";
$result_details = mysqli_query($connect, $sql);

// Tính tổng tiền
$total_amount = 0;

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
    <title>Chi tiết đơn hàng #<?php echo $order_id; ?> - EYEGLASSES</title>
    
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
                        <h1 class="h3 mb-0 text-gray-800">Chi tiết đơn hàng #<?php echo $order_id; ?></h1>
                        <a href="index.php" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại
                        </a>
                    </div>

                    <!-- Order Information -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Thông tin đơn hàng</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <tr>
                                                <th>Mã đơn hàng:</th>
                                                <td>#<?php echo $order['order_id']; ?></td>
                                            </tr>
                                            <tr>
                                                <th>Ngày đặt:</th>
                                                <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Trạng thái:</th>
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
                                            </tr>
                                            <tr>
                                                <th>Tổng tiền:</th>
                                                <td class="text-primary font-weight-bold"><?php echo vnd_format($order['total_amount']); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Phương thức thanh toán:</th>
                                                <td><?php echo $order['payment_method'] ?? 'N/A'; ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Thông tin khách hàng</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" width="100%" cellspacing="0">
                                            <tr>
                                                <th colspan="2" class="bg-light">Thông tin người đặt</th>
                                            </tr>
                                            <?php if (!empty($order['full_name'])): ?>
                                            <tr>
                                                <th>Họ tên:</th>
                                                <td><?php echo htmlspecialchars($order['full_name'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td><?php echo htmlspecialchars($order['email'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Số điện thoại:</th>
                                                <td><?php echo htmlspecialchars($order['phone'] ?? ''); ?></td>
                                            </tr>
                                            <?php else: ?>
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">Không có thông tin người đặt</td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <th colspan="2" class="bg-light">Thông tin người nhận</th>
                                            </tr>
                                            <tr>
                                                <th>Họ tên:</th>
                                                <td><?php echo htmlspecialchars($order['shipping_name'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Số điện thoại:</th>
                                                <td><?php echo htmlspecialchars($order['shipping_phone'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th>Địa chỉ:</th>
                                                <td><?php echo htmlspecialchars($order['shipping_address'] ?? ''); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Sản phẩm đã đặt</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Ảnh</th>
                                            <th>Sản phẩm</th>
                                            <th>Đơn giá</th>
                                            <th>Số lượng</th>
                                            <th>Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result_details) > 0): ?>
                                            <?php while ($item = mysqli_fetch_assoc($result_details)): 
                                                $item_total = $item['price'] * $item['quantity'];
                                                $total_amount += $item_total;
                                            ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <?php if (!empty($item['image_path'])): ?>
                                                            <img src="../products/uploads/<?php echo $item['image_path']; ?>" width="50" alt="<?php echo htmlspecialchars($item['product_name'] ?? ''); ?>">
                                                        <?php else: ?>
                                                            <span class="text-muted">No image</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="font-weight-bold"><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></div>
                                                        <div class="small text-muted">
                                                            <?php if (!empty($item['category_name'])): ?>
                                                                Danh mục: <?php echo htmlspecialchars($item['category_name'] ?? ''); ?><br>
                                                            <?php endif; ?>
                                                            <?php if (!empty($item['manufacturer_name'])): ?>
                                                                Thương hiệu: <?php echo htmlspecialchars($item['manufacturer_name'] ?? ''); ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td><?php echo vnd_format($item['price']); ?></td>
                                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                                    <td class="text-right font-weight-bold"><?php echo vnd_format($item_total); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Không tìm thấy sản phẩm nào trong đơn hàng</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-right">Tổng tiền:</th>
                                            <th class="text-right text-primary"><?php echo vnd_format($total_amount); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mb-4">
                        <div class="col-md-12 text-center">
                            <?php if($order['order_status'] == 'pending'): ?>
                                <a href="update.php?id=<?php echo $order['order_id']; ?>&status=processing" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Xử lý đơn hàng
                                </a>
                                <a href="update.php?id=<?php echo $order['order_id']; ?>&status=cancelled" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                                    <i class="fas fa-times"></i> Hủy đơn hàng
                                </a>
                            <?php elseif($order['order_status'] == 'processing'): ?>
                                <a href="update.php?id=<?php echo $order['order_id']; ?>&status=shipped" class="btn btn-info">
                                    <i class="fas fa-shipping-fast"></i> Giao hàng
                                </a>
                                <a href="update.php?id=<?php echo $order['order_id']; ?>&status=cancelled" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                                    <i class="fas fa-times"></i> Hủy đơn hàng
                                </a>
                            <?php elseif($order['order_status'] == 'shipped'): ?>
                                <a href="update.php?id=<?php echo $order['order_id']; ?>&status=delivered" class="btn btn-success">
                                    <i class="fas fa-check-circle"></i> Xác nhận hoàn thành
                                </a>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại danh sách
                            </a>
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