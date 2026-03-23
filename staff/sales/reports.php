<?php

require_once '../check_sales_login.php';
require_once '../../admin/root.php';

// Thiết lập trang hiện tại
$active_page = 'reports';
$page_title = 'Báo cáo bán hàng';

// Lấy tháng và năm hiện tại
$month = date('Y-m');
$year = date('Y');

// Doanh thu tháng
$sql = "SELECT SUM(total_amount) as total FROM `orders` 
        WHERE DATE_FORMAT(order_date, '%Y-%m') = '$month' 
        AND order_status = 'delivered'";
$result = mysqli_query($connect, $sql);
$eachMonth = mysqli_fetch_array($result);

// Số đơn hàng trong tháng
$sql = "SELECT COUNT(*) as total_orders FROM `orders` 
        WHERE DATE_FORMAT(order_date, '%Y-%m') = '$month' 
        AND order_status != 'cancelled'";
$result = mysqli_query($connect, $sql);
$month_orders = mysqli_fetch_array($result);

// Số sản phẩm đã bán trong tháng
$sql = "SELECT SUM(od.quantity) as total_sold
        FROM order_details od
        JOIN orders o ON od.order_id = o.order_id
        WHERE DATE_FORMAT(o.order_date, '%Y-%m') = '$month'
        AND o.order_status = 'delivered'";
$result = mysqli_query($connect, $sql);
$month_sold = mysqli_fetch_array($result);

// Lấy doanh thu theo từng tháng trong năm
$sql = "SELECT MONTH(order_date) as month, SUM(total_amount) as monthly_revenue
        FROM orders
        WHERE YEAR(order_date) = '$year'
        AND order_status = 'delivered'
        GROUP BY MONTH(order_date)
        ORDER BY MONTH(order_date)";
$result = mysqli_query($connect, $sql);
$monthly_revenue = array_fill(1, 12, 0); // Khởi tạo mảng doanh thu từ tháng 1-12 với giá trị 0

while ($row = mysqli_fetch_array($result)) {
    $monthly_revenue[$row['month']] = $row['monthly_revenue'];
}

// Chuyển mảng thành chuỗi JSON để dùng trong biểu đồ
$monthly_revenue_json = json_encode(array_values($monthly_revenue));

// Đếm số đơn hàng theo trạng thái
$sql_pending = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'";
$result_pending = mysqli_query($connect, $sql_pending);
$pending_count = mysqli_fetch_array($result_pending)['count'];

$sql_processing = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'processing'";
$result_processing = mysqli_query($connect, $sql_processing);
$processing_count = mysqli_fetch_array($result_processing)['count'];

$sql_completed = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'delivered'";
$result_completed = mysqli_query($connect, $sql_completed);
$completed_count = mysqli_fetch_array($result_completed)['count'];

$sql_cancelled = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'cancelled'";
$result_cancelled = mysqli_query($connect, $sql_cancelled);
$cancelled_count = mysqli_fetch_array($result_cancelled)['count'];

// Hàm định dạng số tiền VND
function vnd_format($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include '../partials/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container-fluid {
            padding: 4rem 1.5rem 1.5rem 1.5rem;
        }
        .stats-card {
            border-radius: 10px;
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            margin: 2rem auto;
            height: 300px;
            padding: 1rem;
        }
        .card {
            margin-top: 2rem;
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'sales_sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'sales_topbar.php'; ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-left-primary shadow h-100 py-2 stats-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Doanh thu (Tháng <?php echo date('m/Y'); ?>)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo vnd_format($eachMonth['total'] ?? 0); ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card border-left-success shadow h-100 py-2 stats-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Đơn hàng (Tháng <?php echo date('m/Y'); ?>)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $month_orders['total_orders'] ?? 0; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card border-left-info shadow h-100 py-2 stats-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Sản phẩm đã bán (Tháng <?php echo date('m/Y'); ?>)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $month_sold['total_sold'] ?? 0; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card border-left-warning shadow h-100 py-2 stats-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Đơn hàng đang chờ xử lý</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $pending_count; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-comments fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Chart -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Doanh thu theo tháng (<?php echo $year; ?>)</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
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

    <script>
        // Biểu đồ doanh thu
        var ctx = document.getElementById('revenueChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6", "Tháng 7", "Tháng 8", "Tháng 9", "Tháng 10", "Tháng 11", "Tháng 12"],
                datasets: [{
                    label: 'Doanh thu',
                    data: <?php echo $monthly_revenue_json; ?>,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN', { 
                                    style: 'currency', 
                                    currency: 'VND',
                                    maximumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN', { 
                                    style: 'currency', 
                                    currency: 'VND',
                                    maximumFractionDigits: 0
                                }).format(context.parsed.y);
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>