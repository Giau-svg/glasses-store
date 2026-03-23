<?php
require '../busmanage/check_business_manager_login.php';
require '../busmanage/root.php';

// Kiểm tra kết nối database
if (!$connect) {
    die("Kết nối database thất bại: " . mysqli_connect_error());
}

// Xác định hành động (list, view, stats, delete)
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$success = '';
$error = '';

if ($action == 'list') {
    $orders = $connect->query("SELECT * FROM orders ORDER BY order_date DESC");
    if (!$orders) {
        die("Lỗi truy vấn danh sách đơn hàng: " . $connect->error);
    }
} elseif ($action == 'view') {
    if (!isset($_GET['id'])) {
        header('Location: index.php');
        exit();
    }
    $order_id = (int)$_GET['id'];
    $stmt = $connect->prepare("SELECT * FROM orders WHERE order_id = ?");
    if ($stmt === false) {
        die("Lỗi chuẩn bị truy vấn: " . $connect->error);
    }
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        header('Location: index.php');
        exit();
    }

    $stmt_details = $connect->prepare("SELECT od.*, p.product_name, p.price FROM order_details od JOIN products p ON od.product_id = p.product_id WHERE od.order_id = ?");
    if ($stmt_details === false) {
        die("Lỗi chuẩn bị truy vấn chi tiết đơn hàng: " . $connect->error);
    }
    $stmt_details->bind_param("i", $order_id);
    $stmt_details->execute();
    $order_details = $stmt_details->get_result();
    $stmt_details->close();
} elseif ($action == 'stats') {
    $first_day = date('Y-m-01');
    $last_day = date('Y-m-t');
    $stmt = $connect->prepare("SELECT DATE(order_date) as date, SUM(total_amount) as total FROM orders WHERE order_date BETWEEN ? AND ? AND order_status = 'delivered' GROUP BY DATE(order_date)");
    if ($stmt === false) {
        die("Lỗi chuẩn bị truy vấn thống kê: " . $connect->error);
    }
    $stmt->bind_param("ss", $first_day, $last_day);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = [];
    while ($row = $result->fetch_assoc()) {
        $stats[$row['date']] = $row['total'];
    }
    $stmt->close();

    $start = new DateTime($first_day);
    $end = new DateTime($last_day);
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end->modify('+1 day'));
    $filled_stats = [];
    foreach ($period as $date) {
        $date_str = $date->format('Y-m-d');
        $filled_stats[$date_str] = isset($stats[$date_str]) ? $stats[$date_str] : 0;
    }
} elseif ($action == 'delete') {
    // Kiểm tra id đơn hàng
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $error = "Lỗi: ID đơn hàng không hợp lệ.";
    } else {
        $order_id = (int)$_GET['id'];

        // Lấy chi tiết đơn hàng để hoàn lại tồn kho
        $stmt_details = $connect->prepare("SELECT product_id, quantity FROM order_details WHERE order_id = ?");
        if (!$stmt_details) {
            $error = "Lỗi chuẩn bị truy vấn chi tiết đơn hàng: " . $connect->error;
        } else {
            $stmt_details->bind_param("i", $order_id);
            if (!$stmt_details->execute()) {
                $error = "Lỗi thực thi truy vấn chi tiết đơn hàng: " . $stmt_details->error;
            } else {
                $details = $stmt_details->get_result();

                while ($detail = $details->fetch_assoc()) {
                    // Hoàn lại số lượng tồn kho
                    $stmt_update = $connect->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
                    if (!$stmt_update) {
                        $error = "Lỗi chuẩn bị truy vấn cập nhật tồn kho: " . $connect->error;
                        break;
                    }
                    $stmt_update->bind_param("ii", $detail['quantity'], $detail['product_id']);
                    if (!$stmt_update->execute()) {
                        $error = "Lỗi thực thi truy vấn cập nhật tồn kho: " . $stmt_update->error;
                        break;
                    }
                    $stmt_update->close();
                }
                $stmt_details->close();

                if (empty($error)) {
                    // Xóa chi tiết đơn hàng
                    $stmt_delete_details = $connect->prepare("DELETE FROM order_details WHERE order_id = ?");
                    if (!$stmt_delete_details) {
                        $error = "Lỗi chuẩn bị truy vấn xóa chi tiết đơn hàng: " . $connect->error;
                    } else {
                        $stmt_delete_details->bind_param("i", $order_id);
                        if (!$stmt_delete_details->execute()) {
                            $error = "Lỗi thực thi truy vấn xóa chi tiết đơn hàng: " . $stmt_delete_details->error;
                        }
                        $stmt_delete_details->close();
                    }

                    if (empty($error)) {
                        // Xóa đơn hàng
                        $stmt = $connect->prepare("DELETE FROM orders WHERE order_id = ?");
                        if (!$stmt) {
                            $error = "Lỗi chuẩn bị truy vấn xóa đơn hàng: " . $connect->error;
                        } else {
                            $stmt->bind_param("i", $order_id);
                            if ($stmt->execute()) {
                                if ($stmt->affected_rows > 0) {
                                    $success = "Xóa đơn hàng thành công! Bạn sẽ được chuyển về trang danh sách sau 2 giây.";
                                    header("Refresh: 2; url=index.php");
                                } else {
                                    $error = "Lỗi: Đơn hàng không tồn tại.";
                                }
                            } else {
                                $error = "Lỗi thực thi truy vấn xóa đơn hàng: " . $stmt->error;
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản Lý Bán Hàng - EYEGLASSES</title>
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
    
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include '../partials/busmanage_sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include '../partials/busmanage_topbar.php'; ?>
                <div class="container-fluid">
                    <?php if ($action == 'list'): ?>
                        <h1 class="h3 mb-4 text-gray-800">Quản Lý Bán Hàng</h1>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Danh Sách Đơn Hàng</h6>
                                <div>
                                    <a href="index.php?action=stats" class="btn btn-primary btn-sm mr-2">Thống kê</a>
                                    <a href="manage.php?action=add" class="btn btn-success btn-sm">Thêm</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Mã Đơn</th>
                                                <th>Khách Hàng</th>
                                                <th>Tổng Tiền</th>
                                                <th>Trạng Thái</th>
                                                <th>Ngày Đặt</th>
                                                <th>Hành Động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($order = $orders->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $order['order_id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['shipping_name']); ?></td>
                                                <td><?php echo number_format($order['total_amount'], 0, ',', '.') . ' đ'; ?></td>
                                                <td>
                                                    <span class="badge badge-<?php 
                                                        switch ($order['order_status']) {
                                                            case 'pending': echo 'warning'; break;
                                                            case 'processing': echo 'primary'; break;
                                                            case 'delivered': echo 'success'; break;
                                                            case 'cancelled': echo 'danger'; break;
                                                            default: echo 'secondary';
                                                        }
                                                    ?>">
                                                        <?php 
                                                            switch ($order['order_status']) {
                                                                case 'pending': echo 'Chờ xử lý'; break;
                                                                case 'processing': echo 'Đang xử lý'; break;
                                                                case 'delivered': echo 'Hoàn thành'; break;
                                                                case 'cancelled': echo 'Đã hủy'; break;
                                                                default: echo $order['order_status'];
                                                            }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                                <td>
                                                    <a href="index.php?action=view&id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">Xem</a>
                                                    <a href="manage.php?action=edit&id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-warning">Sửa</a>
                                                    <a href="index.php?action=delete&id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này?');">Xóa</a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($action == 'view'): ?>
                        <h1 class="h3 mb-4 text-gray-800">Chi Tiết Đơn Hàng #<?php echo $order['order_id']; ?></h1>
                        <div class="card shadow mb-4">
                            <div class="card-body">
                                <h6 class="font-weight-bold">Thông Tin Khách Hàng</h6>
                                <p>Tên: <?php echo htmlspecialchars($order['shipping_name']); ?></p>
                                <p>Số điện thoại: <?php echo htmlspecialchars($order['shipping_phone']); ?></p>
                                <p>Địa chỉ: <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                                <p>Tổng tiền: <?php echo number_format($order['total_amount'], 0, ',', '.') . ' đ'; ?></p>
                                <p>Trạng thái: 
                                    <span class="badge badge-<?php 
                                        switch ($order['order_status']) {
                                            case 'pending': echo 'warning'; break;
                                            case 'processing': echo 'primary'; break;
                                            case 'delivered': echo 'success'; break;
                                            case 'cancelled': echo 'danger'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>">
                                        <?php 
                                            switch ($order['order_status']) {
                                                case 'pending': echo 'Chờ xử lý'; break;
                                                case 'processing': echo 'Đang xử lý'; break;
                                                case 'delivered': echo 'Hoàn thành'; break;
                                                case 'cancelled': echo 'Đã hủy'; break;
                                                default: echo $order['order_status'];
                                            }
                                        ?>
                                    </span>
                                </p>
                                <p>Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>

                                <h6 class="font-weight-bold mt-4">Chi Tiết Sản Phẩm</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Sản Phẩm</th>
                                                <th>Số Lượng</th>
                                                <th>Đơn Giá</th>
                                                <th>Thành Tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($detail = $order_details->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($detail['product_name']); ?></td>
                                                <td><?php echo $detail['quantity']; ?></td>
                                                <td><?php echo number_format($detail['price'], 0, ',', '.') . ' đ'; ?></td>
                                                <td><?php echo number_format($detail['quantity'] * $detail['price'], 0, ',', '.') . ' đ'; ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="index.php?action=stats" class="btn btn-primary mr-2">Thống kê</a>
                                <a href="index.php" class="btn btn-secondary">Xem tất cả</a>
                            </div>
                        </div>
                    <?php elseif ($action == 'stats'): ?>
                        <h1 class="h3 mb-4 text-gray-800">Thống Kê Doanh Thu</h1>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Doanh Thu Tháng <?php echo date('m/Y'); ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="salesChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <a href="index.php" class="btn btn-secondary">Xem tất cả</a>
                    <?php elseif ($action == 'delete'): ?>
                        <h1 class="h3 mb-4 text-gray-800">Xóa Đơn Hàng #<?php echo (isset($_GET['id']) ? $_GET['id'] : ''); ?></h1>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <p>Nếu không có lỗi, bạn sẽ được chuyển về trang danh sách đơn hàng sau 2 giây.</p>
                        <a href="index.php" class="btn btn-secondary">Quay lại ngay</a>
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
    <?php if ($action == 'stats'): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script>
        $(document).ready(function() {
            $.getJSON('../busmanage/get_sales_stats.php', function(data) {
                var labels = [];
                var values = [];
                for (var date in data) {
                    labels.push(moment(date).format('DD'));
                    values.push(data[date]);
                }
                var ctx = document.getElementById('salesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Doanh thu (VND)',
                            data: values,
                            borderColor: 'rgba(78, 115, 223, 1)',
                            backgroundColor: 'rgba(78, 115, 223, 0.1)',
                            fill: true
                        }]
                    },
                    options: {
                        scales: {
                            xAxes: [{
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Ngày'
                                }
                            }],
                            yAxes: [{
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Doanh thu (VND)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString('vi-VN');
                                    }
                                }
                            }]
                        }
                    }
                });
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>