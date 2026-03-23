<?php
require '../check_admin_login.php';
require '../root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Lấy tháng hiện tại để thống kê
$month = date('Y-m');

// Lấy năm hiện tại để thống kê
$year = date('Y');

// Doanh thu tháng - sửa lại câu truy vấn để lấy tất cả đơn hàng đã hoàn thành trong tháng hiện tại
$sql = "SELECT SUM(total_amount) as total FROM `orders` 
        WHERE DATE_FORMAT(order_date, '%Y-%m') = '$month' 
        AND order_status = 'delivered'";
$result = mysqli_query($connect, $sql);
$eachMonth = mysqli_fetch_array($result);

// Số đơn hàng trong tháng - bao gồm cả đơn chưa duyệt, loại trừ đơn đã hủy
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

// Đơn hàng mới nhất - lấy 10 đơn hàng gần nhất
$sql = "SELECT o.order_id, o.shipping_name as customer_name, o.total_amount, o.order_status, o.order_date
        FROM orders o
        ORDER BY o.order_date DESC
        LIMIT 10";
$result = mysqli_query($connect, $sql);

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

// Thống kê tổng số tài khoản và số lượng từng vai trò
$total_users = 0;
$role_counts = [
    'admin' => 0,
    'sales' => 0,
    'inventory' => 0,
    'stock' => 0
];
$recent_updated = 0;

// Đếm tổng số tài khoản
$stmt_count = $connect->prepare("SELECT COUNT(*) as total FROM users");
if ($stmt_count && $stmt_count->execute()) {
    $result = $stmt_count->get_result();
    $total_users = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
}
if ($stmt_count) $stmt_count->close();

// Đếm số lượng từng vai trò
$stmt_roles = $connect->prepare("SELECT r.role_name, COUNT(*) as count FROM users u JOIN roles r ON u.role_id = r.role_id GROUP BY r.role_name");
if ($stmt_roles && $stmt_roles->execute()) {
    $result = $stmt_roles->get_result();
    while ($row = $result->fetch_assoc()) {
        $role = $row['role_name'];
        if (isset($role_counts[$role])) {
            $role_counts[$role] = $row['count'];
        }
    }
}
if ($stmt_roles) $stmt_roles->close();

// Đếm số lượng khách hàng
$stmt_customer = $connect->prepare("SELECT COUNT(*) as total FROM users u JOIN roles r ON u.role_id = r.role_id WHERE r.role_name = 'customer'");
$customer_count = 0;
if ($stmt_customer && $stmt_customer->execute()) {
    $result = $stmt_customer->get_result();
    $customer_count = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
}
if ($stmt_customer) $stmt_customer->close();

// Đếm số tài khoản cập nhật trong 7 ngày gần nhất
$stmt_recent = $connect->prepare("SELECT COUNT(*) as total FROM users WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
if ($stmt_recent && $stmt_recent->execute()) {
    $result = $stmt_recent->get_result();
    $recent_updated = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
}
if ($stmt_recent) $stmt_recent->close();

// Hàm định dạng số tiền VND
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
    <title>Dashboard - EYEGLASSES</title>
    
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
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    </div>


                    <!-- Content Row -->
                    



                    <!-- Content Row -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng số tài khoản</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Admin</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $role_counts['admin']; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Nhân viên bán hàng</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $role_counts['sales']; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Quản lý kho</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $role_counts['inventory'] + $role_counts['stock']; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-left-secondary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Khách hàng</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $customer_count; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-left-dark shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Cập nhật 7 ngày</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $recent_updated; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-xl-6 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Tỷ lệ các loại tài khoản</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="userPieChart"></canvas>
                                    </div>
                                    <div id="userPieLegend" class="mt-4 text-center small"></div>
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

    <!-- Page level plugins -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Area Chart
            var areaCtx = document.getElementById("myAreaChart");
            if (areaCtx) {
                var myLineChart = new Chart(areaCtx, {
                    type: 'line',
                    data: {
                        labels: ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6", "Tháng 7", "Tháng 8", "Tháng 9", "Tháng 10", "Tháng 11", "Tháng 12"],
                        datasets: [{
                            label: "Doanh thu",
                            lineTension: 0.3,
                            backgroundColor: "rgba(78, 115, 223, 0.05)",
                            borderColor: "rgba(78, 115, 223, 1)",
                            pointRadius: 3,
                            pointBackgroundColor: "rgba(78, 115, 223, 1)",
                            pointBorderColor: "rgba(78, 115, 223, 1)",
                            pointHoverRadius: 3,
                            pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                            pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                            pointHitRadius: 10,
                            pointBorderWidth: 2,
                            data: <?php echo $monthly_revenue_json; ?>,
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                left: 10,
                                right: 25,
                                top: 25,
                                bottom: 0
                            }
                        },
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    maxTicksLimit: 7
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    maxTicksLimit: 5,
                                    padding: 10,
                                    callback: function(value, index, values) {
                                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ' đ';
                                    }
                                },
                                gridLines: {
                                    color: "rgb(234, 236, 244)",
                                    zeroLineColor: "rgb(234, 236, 244)",
                                    drawBorder: false,
                                    borderDash: [2],
                                    zeroLineBorderDash: [2]
                                }
                            }],
                        },
                        legend: {
                            display: false
                        },
                        tooltips: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyFontColor: "#858796",
                            titleMarginBottom: 10,
                            titleFontColor: '#6e707e',
                            titleFontSize: 14,
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: false,
                            intersect: false,
                            mode: 'index',
                            caretPadding: 10,
                            callbacks: {
                                label: function(tooltipItem, chart) {
                                    var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                                    return datasetLabel + ': ' + tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ' đ';
                                }
                            }
                        }
                    }
                });
            }

            // Pie Chart
            var pieCtx = document.getElementById("userPieChart");
            if (pieCtx) {
                var myPieChart = new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: [
                            "Admin",
                            "Nhân viên bán hàng",
                            "Quản lý kho",
                            "Khách hàng"
                        ],
                        datasets: [{
                            data: [
                                <?php echo (int)$role_counts['admin']; ?>,
                                <?php echo (int)$role_counts['sales']; ?>,
                                <?php echo (int)($role_counts['inventory'] + $role_counts['stock']); ?>,
                                <?php echo (int)$customer_count; ?>
                            ],
                            backgroundColor: [
                                '#36b9cc', // Admin
                                '#1cc88a', // Sales
                                '#f6c23e', // Kho
                                '#858796'  // Khách hàng
                            ],
                            hoverBackgroundColor: [
                                '#2c9faf',
                                '#17a673',
                                '#dda20a',
                                '#6c757d'
                            ],
                            borderColor: "#fff",
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    font: { size: 15 },
                                    color: '#333',
                                    padding: 20
                                }
                            },
                            datalabels: {
                                color: '#222',
                                font: { weight: 'bold', size: 16 },
                                formatter: function(value, context) {
                                    let total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    let percent = total ? Math.round((value / total) * 100) : 0;
                                    return value + ' (' + percent + '%)';
                                }
                            }
                        },
                        cutout: '65%',
                        layout: {
                            padding: 20
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            }
        });
    </script>
</body>
</html>