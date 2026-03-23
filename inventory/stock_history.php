<?php
require_once '../check_inventory_login.php';
require_once '../../admin/root.php';
require_once '../../include/pagination_class.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set active page
$active_page = 'stock_history';
$page_title = 'Lịch sử nhập kho';

// Default filter settings
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$supplier_id = isset($_GET['supplier_id']) ? $_GET['supplier_id'] : '';
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 3;

// Build query conditions
$conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $conditions[] = "(receipt_id LIKE ? OR notes LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= 'ss';
}

if (!empty($start_date)) {
    $conditions[] = "DATE(receipt_date) >= ?";
    $params[] = $start_date;
    $param_types .= 's';
}

if (!empty($end_date)) {
    $conditions[] = "DATE(receipt_date) <= ?";
    $params[] = $end_date;
    $param_types .= 's';
}

if (!empty($supplier_id)) {
    $conditions[] = "sr.supplier_id = ?";
    $params[] = $supplier_id;
    $param_types .= 'i';
}

// Combine conditions
$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Count total records for pagination
$count_sql = "SELECT COUNT(*) FROM stock_receipts sr $where_clause";
$total_records = 0;

if (!empty($params)) {
    // Sử dụng prepared statement
    $stmt = mysqli_prepare($connect, $count_sql);
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    // Sử dụng store_result() trước bind_result() nếu cần,
    // nhưng bind_result() + fetch() thường đủ cho COUNT(*)
    mysqli_stmt_bind_result($stmt, $fetched_total);
    mysqli_stmt_fetch($stmt);
    $total_records = (int)$fetched_total; // Ép kiểu sang số nguyên
    mysqli_stmt_close($stmt);
} else {
    // Không có tham số lọc, sử dụng truy vấn thường
    $result = mysqli_query($connect, $count_sql);
    // Kiểm tra kết quả trước khi fetch
    if ($result) {
        $row = mysqli_fetch_row($result);
        // Kiểm tra xem có dòng kết quả nào không và ép kiểu sang số nguyên
        $total_records = $row ? (int)$row[0] : 0;
        mysqli_free_result($result); // Giải phóng bộ nhớ
    } else {
        // Xử lý lỗi truy vấn nếu có
        $total_records = 0;
        error_log("Database error counting records: " . mysqli_error($connect));
    }
}

// Initialize pagination
$pagination = new Pagination($current_page, $items_per_page, $total_records);
$offset = $pagination->getOffset();

// Get stock receipts with supplier information
$sql = "SELECT sr.*, s.supplier_name
        FROM stock_receipts sr
        LEFT JOIN suppliers s ON sr.supplier_id = s.supplier_id
        $where_clause
        ORDER BY sr.receipt_date DESC
        LIMIT ? OFFSET ?";

$params[] = $items_per_page;
$params[] = $offset;
$param_types .= 'ii';

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, $param_types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get suppliers for filter dropdown
$suppliers_sql = "SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name";
$suppliers_result = mysqli_query($connect, $suppliers_sql);

// Format currency function
function vnd_format($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}

// Get chart data for recent receipts
$chart_sql = "SELECT DATE(receipt_date) as date, SUM(total_amount) as total 
             FROM stock_receipts 
             WHERE receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(receipt_date)
             ORDER BY date";
$chart_result = mysqli_query($connect, $chart_sql);
$chart_data = [];
$chart_labels = [];
while ($row = mysqli_fetch_assoc($chart_result)) {
    $chart_labels[] = date('d/m', strtotime($row['date']));
    $chart_data[] = $row['total'];
}

// Get today's receipts count
$today = date('Y-m-d');
$today_sql = "SELECT COUNT(*) FROM stock_receipts WHERE DATE(receipt_date) = '$today'";
$today_result = mysqli_query($connect, $today_sql);
$today_count = mysqli_fetch_row($today_result)[0];

// Get total value of all receipts
$value_sql = "SELECT SUM(total_amount) FROM stock_receipts";
$value_result = mysqli_query($connect, $value_sql);
$total_value = mysqli_fetch_row($value_result)[0] ?? 0;

// Get suppliers count
$suppliers_count_sql = "SELECT COUNT(*) FROM suppliers";
$suppliers_count_result = mysqli_query($connect, $suppliers_count_sql);
$suppliers_count = mysqli_fetch_row($suppliers_count_result)[0];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include '../partials/head.php'; ?>
</head>
<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include 'inventory_sidebar.php'; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include 'inventory_topbar.php'; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Total Receipts Value Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Tổng giá trị nhập kho</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo vnd_format($total_value); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Receipts Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Phiếu nhập hôm nay</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $today_count; ?> phiếu</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Suppliers Card -->
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Nhà cung cấp</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $suppliers_count; ?> nhà cung cấp</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-building fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm phiếu nhập</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="" class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="search" class="form-label small font-weight-bold">Tìm kiếm</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="search" name="search" placeholder="Mã phiếu, ghi chú..." value="<?php echo htmlspecialchars($search); ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="start_date" class="form-label small font-weight-bold">Từ ngày</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="end_date" class="form-label small font-weight-bold">Đến ngày</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="supplier_id" class="form-label small font-weight-bold">Nhà cung cấp</label>
                                    <select class="form-control" id="supplier_id" name="supplier_id">
                                        <option value="">-- Tất cả --</option>
                                        <?php 
                                        mysqli_data_seek($suppliers_result, 0);
                                        while ($supplier = mysqli_fetch_assoc($suppliers_result)): 
                                        ?>
                                        <option value="<?php echo $supplier['supplier_id']; ?>" <?php echo ($supplier_id == $supplier['supplier_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="col-12 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter mr-1"></i> Lọc
                                    </button>
                                    <a href="stock_history.php" class="btn btn-secondary ml-2">
                                        <i class="fas fa-sync-alt mr-1"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Chart Card -->
                    

                    <!-- Stock History Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách phiếu nhập kho</h6>
                            <a href="stock_in.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus-circle mr-1"></i> Tạo phiếu mới
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col" width="70">Mã phiếu</th>
                                            <th scope="col" width="150">Ngày nhập</th>
                                            <th scope="col">Nhà cung cấp</th>
                                            <th scope="col" class="text-right" width="150">Tổng tiền</th>
                                            <th scope="col">Ghi chú</th>
                                            <th scope="col" class="text-center" width="120">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td>
                                                    <div class="font-weight-bold">#<?php echo $row['receipt_id']; ?></div>
                                                    <?php
                                                    $days = floor((time() - strtotime($row['receipt_date'])) / (60 * 60 * 24));
                                                    if ($days <= 7): ?>
                                                        <span class="badge badge-success">Mới</span>
                                                    <?php elseif ($days <= 30): ?>
                                                        <span class="badge badge-info">Tháng này</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Cũ</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo date('d/m/Y', strtotime($row['receipt_date'])); ?>
                                                    <div class="small text-muted"><?php echo date('H:i', strtotime($row['receipt_date'])); ?></div>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                                                <td class="text-right total-value">
                                                    <?php echo vnd_format($row['total_amount']); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $note = trim(htmlspecialchars($row['notes']));
                                                    if (!empty($note)):
                                                    ?>
                                                    <div class="text-truncate" style="max-width: 200px;" title="<?php echo $note; ?>">
                                                        <?php echo $note; ?>
                                                    </div>
                                                    <?php else: ?>
                                                    <span class="text-muted font-italic">Không có ghi chú</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <a href="receipt_detail.php?id=<?php echo $row['receipt_id']; ?>" class="btn btn-sm btn-info" title="Chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="print_receipt.php?id=<?php echo $row['receipt_id']; ?>" class="btn btn-sm btn-primary" target="_blank" title="In phiếu">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6">
                                                    <div class="empty-state text-center py-5">
                                                        <div class="icon mb-3">
                                                            <i class="fas fa-box-open fa-4x text-gray-300"></i>
                                                        </div>
                                                        <h5 class="text-gray-800 mb-1">Không tìm thấy phiếu nhập kho</h5>
                                                        <p class="text-gray-600 mb-3">
                                                            Không có phiếu nhập kho nào phù hợp với điều kiện tìm kiếm.
                                                            Hãy thử thay đổi điều kiện lọc hoặc <a href="stock_in.php">tạo phiếu nhập mới</a>.
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if ($total_records > $items_per_page): ?>
                                <div class="d-flex justify-content-center align-items-center mt-3">
                                <nav>
                                    <ul class="pagination pagination-sm">
                                        <?php if ($pagination->hasPrevious()): ?>
                                            <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination->getPreviousPage(); ?>&search=<?php echo urlencode($search); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&supplier_id=<?php echo urlencode($supplier_id); ?>" aria-label="Previous">
                                            Previous </a>
                                            </li>
                                        <?php else: // Thêm else để vô hiệu hóa nút Previous khi ở trang đầu ?>
                                            <li class="page-item disabled">
                                            <span class="page-link" aria-label="Previous">
                                                    Previous </span>

                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php foreach ($pagination->getPages() as $page): ?>
                                            <?php if ($page['isSeparator']): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            <?php else: ?>
                                                <li class="page-item <?php echo ($page['number'] == $current_page) ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $page['number']; ?>&search=<?php echo urlencode($search); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&supplier_id=<?php echo urlencode($supplier_id); ?>">
                                                        <?php echo $page['number']; ?>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        
                                        <?php if ($pagination->hasNext()): ?>
                                            <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination->getNextPage(); ?>&search=<?php echo urlencode($search); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&supplier_id=<?php echo urlencode($supplier_id); ?>" aria-label="Next">
                                            Next </a>
                                            </li>
                                        <?php else: // Thêm else để vô hiệu hóa nút Next khi ở trang cuối ?>
                                            <li class="page-item disabled">
                                            <span class="page-link" aria-label="Next">
                                            Next </span>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

                <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Biểu đồ giá trị nhập kho 30 ngày gần đây</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="stockChart"></canvas>
                            </div>
                        </div>
                    </div>
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; EYEGLASSES 2023</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Bạn muốn đăng xuất?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Chọn "Đăng xuất" bên dưới nếu bạn muốn kết thúc phiên làm việc hiện tại.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Hủy</button>
                    <a class="btn btn-primary" href="../logout.php">Đăng xuất</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="https://cdn.jsdelivr.net/npm/jquery.easing@1.4.1/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../admin/js/sb-admin-2.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[title]').tooltip();
        
        // Initialize chart
        const ctx = document.getElementById('stockChart').getContext('2d');
        const stockChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Giá trị nhập kho (đ)',
                    data: <?php echo json_encode($chart_data); ?>,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
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
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + ' đ';
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
                            return tooltipItem.yLabel.toLocaleString('vi-VN') + ' đ';
                        }
                    }
                }
            }
        });
    });
    </script>
</body>
</html> 