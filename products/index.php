<?php
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

// Xác định hành động (list, stats, view)
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'list') {
    // Lấy danh sách sản phẩm
    $products = null;
    $stmt = $connect->prepare("SELECT p.product_id, p.product_name, p.price, p.cost_price, p.stock_quantity, c.category_name, b.brand_name 
                               FROM products p 
                               JOIN categories c ON p.category_id = c.category_id 
                               JOIN brands b ON p.brand_id = b.brand_id 
                               ORDER BY p.product_id DESC");
    if (!$stmt) {
        die("Lỗi chuẩn bị truy vấn: " . $connect->error);
    }
    $stmt->execute();
    $products = $stmt->get_result();
    $stmt->close();
} elseif ($action == 'stats') {
    // Thống kê số lượng và giá trị tồn kho theo danh mục
    $stmt = $connect->prepare("SELECT c.category_name, 
                                      SUM(p.stock_quantity) as total_stock, 
                                      SUM(p.stock_quantity * p.price) as total_value 
                               FROM products p 
                               JOIN categories c ON p.category_id = c.category_id 
                               GROUP BY c.category_name");
    if (!$stmt) {
        die("Lỗi chuẩn bị truy vấn thống kê: " . $connect->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = [];
    while ($row = $result->fetch_assoc()) {
        $stats[$row['category_name']] = [
            'total_stock' => $row['total_stock'],
            'total_value' => $row['total_value']
        ];
    }
    $stmt->close();
} elseif ($action == 'view') {
    // Xem chi tiết sản phẩm
    if (!isset($_GET['id'])) {
        header('Location: index.php');
        exit();
    }
    $product_id = (int)$_GET['id'];
    $stmt = $connect->prepare("
        SELECT p.product_id, p.product_name, p.price, p.cost_price, p.stock_quantity, c.category_name, b.brand_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        JOIN brands b ON p.brand_id = b.brand_id 
        WHERE p.product_id = ?
    ");
    if (!$stmt) {
        die("Lỗi chuẩn bị truy vấn: " . $connect->error);
    }
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
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
    <title>Quản Lý Mua Hàng - EYEGLASSES</title>
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
                        <h1 class="h3 mb-4 text-gray-800">Danh Sách Sản Phẩm</h1>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-primary">Danh Sách Sản Phẩm</h6>
                                <div>
                                    <a href="index.php?action=stats" class="btn btn-primary btn-sm mr-2">Thống kê</a>
                                    <a href="manage.php?action=add" class="btn btn-success btn-sm">Thêm Sản Phẩm</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Tên Sản Phẩm</th>
                                                <th>Danh Mục</th>
                                                <th>Thương Hiệu</th>
                                                <th>Giá Bán</th>
                                                <th>Giá Nhập</th>
                                                <th>Tồn Kho</th>
                                                <th>Hành Động</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($product = $products->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $product['product_id']; ?></td>
                                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['brand_name']); ?></td>
                                                    <td><?php echo number_format($product['price'], 2, ',', '.'); ?></td>
                                                    <td><?php echo number_format($product['cost_price'], 2, ',', '.'); ?></td>
                                                    <td><?php echo $product['stock_quantity']; ?></td>
                                                    <td>
                                                        <a href="index.php?action=view&id=<?php echo $product['product_id']; ?>" class="btn btn-info btn-sm">Xem</a>
                                                        <a href="manage.php?action=edit&id=<?php echo $product['product_id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                                                        <a href="manage.php?action=delete&id=<?php echo $product['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">Xóa</a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($action == 'stats'): ?>
                        <h1 class="h3 mb-4 text-gray-800">Thống Kê Tồn Kho Theo Danh Mục</h1>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Số Lượng và Giá Trị Tồn Kho Theo Danh Mục</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area" style="height: 400px;">
                                    <canvas id="stockChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <a href="index.php" class="btn btn-secondary">Quay lại</a>
                    <?php elseif ($action == 'view'): ?>
                        <h1 class="h3 mb-4 text-gray-800">Chi Tiết Sản Phẩm #<?php echo $product['product_id']; ?></h1>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Thông Tin Sản Phẩm</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Mã Sản Phẩm:</strong> #<?php echo $product['product_id']; ?></p>
                                        <p><strong>Tên Sản Phẩm:</strong> <?php echo htmlspecialchars($product['product_name']); ?></p>
                                        <p><strong>Danh Mục:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
                                        <p><strong>Thương Hiệu:</strong> <?php echo htmlspecialchars($product['brand_name']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Giá Bán:</strong> <?php echo number_format($product['price'], 0, ',', '.') . ' đ'; ?></p>
                                        <p><strong>Giá Nhập:</strong> <?php echo number_format($product['cost_price'], 0, ',', '.') . ' đ'; ?></p>
                                        <p><strong>Số Lượng Tồn:</strong> <?php echo $product['stock_quantity']; ?></p>
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
    <?php if ($action == 'stats'): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script>
        $(document).ready(function() {
            // Dữ liệu thống kê từ PHP
            var stats = <?php echo json_encode($stats); ?>;
            var labels = [];
            var stockValues = [];
            var moneyValues = [];
            for (var category in stats) {
                labels.push(category);
                stockValues.push(stats[category].total_stock);
                moneyValues.push(stats[category].total_value);
            }
            var ctx = document.getElementById('stockChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Số lượng tồn kho',
                            data: stockValues,
                            backgroundColor: 'rgba(78, 115, 223, 0.6)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 1,
                            yAxisID: 'y-axis-stock'
                        },
                        {
                            label: 'Giá trị tồn kho (VND)',
                            data: moneyValues,
                            backgroundColor: 'rgba(46, 204, 113, 0.6)',
                            borderColor: 'rgba(46, 204, 113, 1)',
                            borderWidth: 1,
                            yAxisID: 'y-axis-money'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        xAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'Danh mục'
                            },
                            ticks: {
                                autoSkip: false
                            }
                        }],
                        yAxes: [
                            {
                                id: 'y-axis-stock',
                                position: 'left',
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Số lượng tồn kho'
                                },
                                ticks: {
                                    beginAtZero: true,
                                    callback: function(value) {
                                        return value.toLocaleString('vi-VN');
                                    }
                                }
                            },
                            {
                                id: 'y-axis-money',
                                position: 'right',
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Giá trị tồn kho (VND)'
                                },
                                ticks: {
                                    beginAtZero: true,
                                    callback: function(value) {
                                        return value.toLocaleString('vi-VN');
                                    }
                                },
                                gridLines: {
                                    drawOnChartArea: false
                                }
                            }
                        ]
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
                                var datasetLabel = data.datasets[tooltipItem.datasetIndex].label;
                                var value = tooltipItem.yLabel;
                                return datasetLabel + ': ' + value.toLocaleString('vi-VN') + (datasetLabel.includes('Giá trị') ? ' đ' : ' sản phẩm');
                            }
                        }
                    }
                }
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>