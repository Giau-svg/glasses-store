<?php
require 'check_business_manager_login.php';
require 'root.php';

// Xử lý xóa
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    if (isset($_GET['type']) && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = null;
        if ($_GET['type'] == 'order') {
            $stmt = $connect->prepare("DELETE FROM orders WHERE order_id = ?");
        } elseif ($_GET['type'] == 'product') {
            $stmt = $connect->prepare("DELETE FROM products WHERE product_id = ?");
        }
        
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: business_dashboard.php');
        exit();
    } else {
        header('Location: business_dashboard.php');
        exit();
    }
}

// 1. Lấy dữ liệu tổng quan
$stmt_total_sales = $connect->prepare("SELECT SUM(total_amount) as total_sales FROM orders WHERE order_status = 'delivered'");
$stmt_total_sales->execute();
$total_sales = $stmt_total_sales->get_result()->fetch_assoc()['total_sales'] ?? 0;
$stmt_total_sales->close();

$stmt_total_stock = $connect->prepare("SELECT SUM(stock_quantity) as total_stock FROM products");
$stmt_total_stock->execute();
$total_stock = $stmt_total_stock->get_result()->fetch_assoc()['total_stock'] ?? 0;
$stmt_total_stock->close();

// Thêm số lượng sản phẩm đã bán
$stmt_total_sold = $connect->prepare("
    SELECT SUM(od.quantity) as total_sold_products
    FROM order_details od
    JOIN orders o ON od.order_id = o.order_id
    WHERE o.order_status = 'delivered'
");
$stmt_total_sold->execute();
$total_sold_products = $stmt_total_sold->get_result()->fetch_assoc()['total_sold_products'] ?? 0;
$stmt_total_sold->close();

// Thêm thống kê sản phẩm sắp hết hàng
$low_stock_count = 0;
$stmt_low_stock = $connect->prepare("
    SELECT COUNT(*) as low_stock_count 
    FROM products 
    WHERE stock_quantity < 10
");
if ($stmt_low_stock && $stmt_low_stock->execute()) {
    $result = $stmt_low_stock->get_result();
    $low_stock_count = $result ? ($result->fetch_assoc()['low_stock_count'] ?? 0) : 0;
}
$stmt_low_stock->close();

// Thêm thống kê sản phẩm hết hàng
$out_of_stock_count = 0;
$stmt_out_of_stock = $connect->prepare("
    SELECT COUNT(*) as out_of_stock_count 
    FROM products 
    WHERE stock_quantity = 0
");
if ($stmt_out_of_stock && $stmt_out_of_stock->execute()) {
    $result = $stmt_out_of_stock->get_result();
    $out_of_stock_count = $result ? ($result->fetch_assoc()['out_of_stock_count'] ?? 0) : 0;
}
$stmt_out_of_stock->close();

// Thêm top sản phẩm bán chạy
$top_products = null;
$stmt_top_products = $connect->prepare("
    SELECT p.product_id, p.product_name, SUM(od.quantity) as total_sold
    FROM order_details od
    JOIN products p ON od.product_id = p.product_id
    JOIN orders o ON od.order_id = o.order_id
    WHERE o.order_status = 'delivered'
    GROUP BY p.product_id, p.product_name
    ORDER BY total_sold DESC
    LIMIT 5
");
if ($stmt_top_products && $stmt_top_products->execute()) {
    $top_products = $stmt_top_products->get_result();
}

// 2. Lấy dữ liệu Đơn bán hàng gần đây
$stmt_orders = $connect->prepare("
    SELECT order_id, shipping_name, total_amount, order_status, order_date
    FROM orders
    ORDER BY order_date DESC
    LIMIT 5
");
$stmt_orders->execute();
$recent_orders = $stmt_orders->get_result();

// 3. Lấy dữ liệu Tồn kho
$stmt_inventory = $connect->prepare("
    SELECT product_id, product_name, stock_quantity, price
    FROM products
    WHERE stock_quantity < 10
    ORDER BY stock_quantity ASC
    LIMIT 5
");
$stmt_inventory->execute();
$low_inventory = $stmt_inventory->get_result();

$monthly_sales = 0;
$stmt_monthly_sales = $connect->prepare("
    SELECT SUM(total_amount) as monthly_sales 
    FROM orders 
    WHERE order_status = 'delivered' 
    AND MONTH(order_date) = MONTH(CURRENT_DATE())
    AND YEAR(order_date) = YEAR(CURRENT_DATE())
");
if ($stmt_monthly_sales && $stmt_monthly_sales->execute()) {
    $result = $stmt_monthly_sales->get_result();
    $monthly_sales = $result ? ($result->fetch_assoc()['monthly_sales'] ?? 0) : 0;
}
$stmt_monthly_sales->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tổng Quan - EYEGLASSES</title>
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
                    <h1 class="h3 mb-4 text-gray-800">Tổng Quan Hệ Thống</h1>

                    <!-- Thống kê tổng quan -->
                    <div class="row">
                        <!-- Doanh thu -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2 card-dashboard">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Tổng Doanh Thu</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo number_format($total_sales, 0, ',', '.') . ' đ'; ?>
                                            </div>
                                            <div class="text-xs text-muted mt-1">
                                                Tháng này: <?php echo number_format($monthly_sales, 0, ',', '.') . ' đ'; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill-wave stat-icon text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Đơn hàng -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2 card-dashboard">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Đơn Hàng</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo isset($order_stats['delivered_orders']) ? $order_stats['delivered_orders'] : 0; ?> hoàn thành
                                            </div>
                                            <div class="text-xs text-muted mt-1">
                                                <?php echo isset($order_stats['pending_orders']) ? $order_stats['pending_orders'] : 0; ?> chờ xử lý
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-shopping-cart stat-icon text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tồn kho -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2 card-dashboard">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Tồn Kho</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $total_stock; ?> sản phẩm
                                            </div>
                                            <div class="text-xs text-muted mt-1">
                                                <?php echo $low_stock_count; ?> sản phẩm sắp hết
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-boxes stat-icon text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sản phẩm -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2 card-dashboard">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Sản Phẩm</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $total_sold_products; ?> đã bán
                                            </div>
                                            <div class="text-xs text-muted mt-1">
                                                <?php echo $out_of_stock_count; ?> sản phẩm hết hàng
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-box stat-icon text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Thao Tác Nhanh</h6>
                                </div>
                                <div class="card-body">
                                    <div class="quick-actions">
                                        <a href="../orders/manage.php?action=add" class="quick-action-btn">
                                            <i class="fas fa-plus-circle mb-2"></i>
                                            <div>Tạo Đơn Hàng</div>
                                        </a>
                                        <a href="../stock_receipts/manage.php?action=add" class="quick-action-btn">
                                            <i class="fas fa-box-open mb-2"></i>
                                            <div>Nhập Kho</div>
                                        </a>
                                        <a href="../products/manage.php?action=add" class="quick-action-btn">
                                            <i class="fas fa-plus mb-2"></i>
                                            <div>Thêm Sản Phẩm</div>
                                        </a>
                                        <a href="../reports/index.php" class="quick-action-btn">
                                            <i class="fas fa-chart-bar mb-2"></i>
                                            <div>Báo Cáo</div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Biểu đồ và bảng thống kê -->
                    <div class="row">
                        <!-- Biểu đồ doanh thu -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Doanh Thu Theo Thời Gian</h6>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="timeRangeDropdown" data-toggle="dropdown">
                                            Tháng này
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" data-range="week">Tuần này</a>
                                            <a class="dropdown-item" href="#" data-range="month">Tháng này</a>
                                            <a class="dropdown-item" href="#" data-range="year">Năm nay</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area" style="height: 350px;">
                                        <canvas id="salesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top sản phẩm bán chạy -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Top Sản Phẩm Bán Chạy</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Sản phẩm</th>
                                                    <th>Đã bán</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($top_products): ?>
                                                    <?php while ($product = $top_products->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                        <td class="text-right"><?php echo $product['total_sold']; ?></td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr><td colspan="2">Không có dữ liệu</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Đơn hàng gần đây và tồn kho -->
                    <div class="row">
                        <!-- Đơn hàng gần đây -->
                        <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Đơn Hàng Gần Đây</h6>
                            <div>
                                        <a href="../orders/index.php" class="btn btn-sm btn-primary">Xem tất cả</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                        <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Mã Đơn</th>
                                            <th>Khách Hàng</th>
                                            <th>Tổng Tiền</th>
                                            <th>Trạng Thái</th>
                                            <th>Ngày Đặt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
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
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                    </div>
                            </div>
                        </div>
                    </div>

                        <!-- Tồn kho thấp -->
                        <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">Sản Phẩm Sắp Hết Hàng</h6>
                            <div>
                                        <a href="../stock_receipts/index.php" class="btn btn-sm btn-primary">Xem tất cả</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                        <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                                    <th>Sản phẩm</th>
                                                    <th>Tồn kho</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($product = $low_inventory->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $product['stock_quantity'] == 0 ? 'danger' : 'warning'; ?>">
                                                    <?php echo $product['stock_quantity']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                    </div>
                                </div>
                            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script>
        // Biểu đồ Doanh thu
        $.getJSON('get_sales_stats.php', function(data) {
            var labels = [];
            var values = [];
            for (var date in data) {
                labels.push(moment(date).format('DD/MM'));
                values.push(data[date]);
            }
            var ctxSales = document.getElementById('salesChart').getContext('2d');
            new Chart(ctxSales, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Doanh thu (VND)',
                        data: values,
                        borderColor: 'rgba(78, 115, 223, 1)',
                        backgroundColor: 'rgba(78, 115, 223, 0.3)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        xAxes: [{
                            gridLines: {
                                display: false
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Ngày trong tháng'
                            }
                        }],
                        yAxes: [{
                            gridLines: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Doanh thu (VND)'
                            },
                            ticks: {
                                beginAtZero: true,
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN');
                                }
                            }
                        }]
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltips: {
                        enabled: true,
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return 'Doanh thu: ' + tooltipItem.yLabel.toLocaleString('vi-VN') + ' đ';
                            }
                        }
                    }
                }
            });
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log('Lỗi khi lấy dữ liệu doanh thu: ' + textStatus + ' - ' + errorThrown);
        });
    </script>
</body>
</html>
<?php
$stmt_orders->close();
$stmt_inventory->close();
?>