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
$count_sql = "SELECT COUNT(*) FROM stock_receipts sr LEFT JOIN suppliers s ON sr.supplier_id = s.supplier_id $where_clause"; // <-- Đã thêm join
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
// Get chart data based on user selection
// Get chart data based on user selection
$chart_view = isset($_GET['chart_view']) ? $_GET['chart_view'] : 'daily'; // Default to daily
$selected_month = isset($_GET['chart_month']) ? $_GET['chart_month'] : '';
$selected_year = isset($_GET['chart_year']) ? $_GET['chart_year'] : '';


$chart_sql = "";
$chart_labels = [];
$chart_data = [];

switch ($chart_view) {
case 'monthly':
        // Data grouped by day, filtered by specific month and year
        // Requires both month and year to be selected for daily breakdown
        if (!empty($selected_year) && !empty($selected_month)) {
            $chart_sql = "SELECT DATE(receipt_date) as period, SUM(total_amount) as total
                          FROM stock_receipts
                          WHERE YEAR(receipt_date) = ? AND MONTH(receipt_date) = ?
                          GROUP BY period
                          ORDER BY period";

            $month_year_params = [$selected_year, (int)$selected_month];
            $month_year_param_types = 'ii';

             if ($stmt = mysqli_prepare($connect, $chart_sql)) {
                mysqli_stmt_bind_param($stmt, $month_year_param_types, ...$month_year_params);
                mysqli_stmt_execute($stmt);
                $chart_result = mysqli_stmt_get_result($stmt);
             } else {
                 error_log("Lỗi chuẩn bị truy vấn biểu đồ tháng (chi tiết theo ngày): " . mysqli_error($connect));
                 $chart_result = false;
             }

            if ($chart_result) {
                while ($row = mysqli_fetch_assoc($chart_result)) {
                    // Format label as DD/MM for daily view within the month
                    $chart_labels[] = date('d/m', strtotime($row['period']));
                    $chart_data[] = $row['total'];
                }
                if (isset($stmt)) mysqli_stmt_close($stmt);
                mysqli_free_result($chart_result);
            }

        } else {
            // If month or year is not selected, don't show daily breakdown for the month
            // You could choose to show nothing, or perhaps the monthly total if needed.
            // For this request, we'll show an empty chart if specific month/year isn't picked.
            $chart_labels = [];
            $chart_data = [];
            // Optionally set an error message
            // $error_message = "Vui lòng chọn tháng và năm cụ thể để xem biểu đồ chi tiết theo ngày.";
        }
        break;
    case 'yearly':
        // Data grouped by year, filtered by specific year if selected (though selecting a specific year for yearly view is redundant, keep the option)
        $chart_sql = "SELECT YEAR(receipt_date) as period, SUM(total_amount) as total
                      FROM stock_receipts";
        $year_conditions = [];
        $year_params = [];
        $year_param_types = '';

        if (!empty($selected_year)) {
            $year_conditions[] = "YEAR(receipt_date) = ?";
            $year_params[] = $selected_year;
            $year_param_types .= 'i';
        }

        if (!empty($year_conditions)) {
            $chart_sql .= " WHERE " . implode(" AND ", $year_conditions);
        }

        $chart_sql .= " GROUP BY period ORDER BY period";

         // Use prepared statement if there are filters
        if (!empty($year_params)) {
             if ($stmt = mysqli_prepare($connect, $chart_sql)) {
                mysqli_stmt_bind_param($stmt, $year_param_types, ...$year_params);
                mysqli_stmt_execute($stmt);
                $chart_result = mysqli_stmt_get_result($stmt);
             } else {
                 error_log("Lỗi chuẩn bị truy vấn biểu đồ năm: " . mysqli_error($connect));
                 $chart_result = false; // Đặt false để xử lý lỗi bên dưới
             }
        } else {
             // No year filter selected, just group by year for all time
             $chart_result = mysqli_query($connect, $chart_sql);
        }


        if ($chart_result) {
            while ($row = mysqli_fetch_assoc($chart_result)) {
                $chart_labels[] = $row['period']; // Label as YYYY
                $chart_data[] = $row['total'];
            }
             if (isset($stmt)) mysqli_stmt_close($stmt); // Đóng statement nếu dùng prepared
            mysqli_free_result($chart_result);
        }
        break;
    case 'daily':
    default:
        // Data grouped by day (last 30 days) - Keep original logic
        // No specific month/year filter applied here, as it's for the last 30 days
        $chart_sql = "SELECT DATE(receipt_date) as period, SUM(total_amount) as total
                     FROM stock_receipts
                     WHERE receipt_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                     GROUP BY period
                     ORDER BY period";
        $chart_result = mysqli_query($connect, $chart_sql);
        if ($chart_result) {
            while ($row = mysqli_fetch_assoc($chart_result)) {
                $chart_labels[] = date('d/m', strtotime($row['period'])); // Label as DD/MM
                $chart_data[] = $row['total'];
            }
             mysqli_free_result($chart_result);
        }
        break;
}


// Check for database errors if needed, though mysqli_query returning false is checked above.
// For prepared statements in other parts, proper error handling is already present.


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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
                        <a href="stock_in.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-plus-circle fa-sm text-white-50"></i> Tạo phiếu nhập mới
                        </a>
                    </div>

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
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Biểu đồ giá trị nhập kho</h6>
        <form id="chartViewForm" method="GET" action="">
             <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
             <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
             <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
             <input type="hidden" name="supplier_id" value="<?php echo htmlspecialchars($supplier_id); ?>">
             <input type="hidden" name="page" value="<?php echo htmlspecialchars($current_page); ?>">

            <label for="chart_view" class="small font-weight-bold mr-2 mb-0">Xem theo:</label>
            <select class="form-control-sm" id="chart_view" name="chart_view" onchange="this.form.submit()">
                <option value="daily" <?php echo (isset($_GET['chart_view']) && $_GET['chart_view'] == 'daily') ? 'selected' : ''; ?>>30 ngày gần đây</option>
                <option value="monthly" <?php echo (isset($_GET['chart_view']) && $_GET['chart_view'] == 'monthly') ? 'selected' : ''; ?>>Tháng</option>
                <option value="yearly" <?php echo (isset($_GET['chart_view']) && $_GET['chart_view'] == 'yearly') ? 'selected' : ''; ?>>Năm</option>
            </select>
            </select>

        <span id="monthYearSelectors" style="display: none;">
            <select class="form-control-sm ml-2" id="chart_month" name="chart_month">
                <option value="">-- Chọn tháng --</option>
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $month = str_pad($m, 2, '0', STR_PAD_LEFT);
                    $month_name = date('m', mktime(0, 0, 0, $m, 10));
                    $selected_month = (isset($_GET['chart_month']) && $_GET['chart_month'] == $month) ? 'selected' : '';
                    echo '<option value="' . $month . '" ' . $selected_month . '>' . $month_name . '</option>';
                }
                ?>
            </select>
            <select class="form-control-sm ml-2" id="chart_year" name="chart_year">
                <option value="">-- Chọn năm --</option>
                <?php
                // Lấy danh sách các năm có dữ liệu từ database
                $year_sql = "SELECT DISTINCT YEAR(receipt_date) as year FROM stock_receipts ORDER BY year DESC";
                $year_result = mysqli_query($connect, $year_sql);
                $current_year = date('Y');

                // Thêm năm hiện tại và các năm từ database
                $years = [$current_year];
                 if ($year_result) {
                    while ($row = mysqli_fetch_assoc($year_result)) {
                        if (!in_array($row['year'], $years)) {
                            $years[] = $row['year'];
                        }
                    }
                     mysqli_free_result($year_result);
                 }
                rsort($years); // Sắp xếp giảm dần

                foreach ($years as $year) {
                    $selected_year = (isset($_GET['chart_year']) && $_GET['chart_year'] == $year) ? 'selected' : '';
                    echo '<option value="' . $year . '" ' . $selected_year . '>' . $year . '</option>';
                }
                ?>
            </select>
        </span>
         <button type="submit" class="btn btn-sm btn-primary ml-2"><i class="fas fa-chart-line mr-1"></i> Xem</button>
             <button type="button" class="btn btn-sm btn-secondary ml-2" onclick="printChart()"><i class="fas fa-print"></i> In</button>
        </form>
    </div>
    <div class="card-body">
        <div class="chart-area">
            <canvas id="stockChart"></canvas>
        </div>
    </div>
</div>

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
    <script src="../../admin/public/js/sb-admin-2.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[title]').tooltip();
        
         // Hàm hiển thị/ẩn selectors tháng/năm
    function toggleMonthYearSelectors() {
        const chartView = $('#chart_view').val();
        if (chartView === 'monthly' || chartView === 'yearly') {
            $('#monthYearSelectors').show();
        } else {
            $('#monthYearSelectors').hide();
        }
    }

    // Gọi hàm khi trang tải
    toggleMonthYearSelectors();

    // Gọi hàm khi lựa chọn 'Xem theo' thay đổi
    $('#chart_view').change(function() {
        toggleMonthYearSelectors();
        // Không submit form ở đây nữa, chỉ ẩn/hiện selectors.
        // Form sẽ được submit khi nhấn nút "Cập nhật biểu đồ".
    });


        // Initialize chart
    const ctx = document.getElementById('stockChart').getContext('2d');
const stockChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>, // Đây là nơi dữ liệu labels từ PHP được truyền
        datasets: [{
            label: 'Giá trị nhập kho (đ)',
            data: <?php echo json_encode($chart_data); ?>, // Đây là nơi dữ liệu data từ PHP được truyền
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
                    maxTicksLimit: 7 // Có thể cần điều chỉnh hoặc bỏ đi tùy chế độ xem
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

    function printChart() {
    const chartCanvas = document.getElementById('stockChart'); // Lấy phần tử canvas của biểu đồ
    if (!chartCanvas) {
        console.error("Không tìm thấy canvas của biểu đồ!");
        return;
    }

    // Tạo URL hình ảnh từ canvas
    const chartImageUrl = chartCanvas.toDataURL('image/png');

    // Mở một cửa sổ mới để in
    const printWindow = window.open('', '_blank');

    // Viết nội dung HTML vào cửa sổ mới
    printWindow.document.write('<html><head><title>In Biểu Đồ Nhập Kho</title>');
    // Tùy chọn: Thêm CSS để định dạng hình ảnh khi in
    printWindow.document.write('<style>');
    printWindow.document.write('body { display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }');
    printWindow.document.write('img { max-width: 95%; max-height: 95vh; display: block; margin: auto; }');
    printWindow.document.write('@media print { body { min-height: auto; } img { max-width: 100%; max-height: none; } }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<img src="' + chartImageUrl + '" alt="Biểu đồ giá trị nhập kho">');
    printWindow.document.write('</body></html>');

    printWindow.document.close(); // Kết thúc việc ghi nội dung

    // Chờ cho nội dung (hình ảnh) được tải hoàn toàn trước khi in
    // Có thể cần một chút độ trễ hoặc sử dụng sự kiện onload
    // Cách đơn giản: dùng setTimeout
    setTimeout(function() {
        printWindow.print(); // Mở hộp thoại in
        printWindow.close(); // Đóng cửa sổ sau khi hộp thoại in hiện ra
    }, 250); // Độ trễ 250ms, có thể điều chỉnh nếu cần

}
    </script>
</body>
</html> 