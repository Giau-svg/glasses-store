<?php
require_once '../check_inventory_login.php';
require_once '../../admin/root.php';

// Set active page
$active_page = 'reports';

// Get current month and year for default filtering
$current_month = date('m');
$current_year = date('Y');
$selected_month = isset($_GET['month']) ? $_GET['month'] : $current_month;
$selected_year = isset($_GET['year']) ? $_GET['year'] : $current_year;
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'inventory_value';

// Format date for queries
$start_date = $selected_year . '-' . $selected_month . '-01';
$end_date = date('Y-m-t', strtotime($start_date));

// Function to format currency
function vnd_format($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}

// First, let's check what columns exist in the products table
$table_info_sql = "SHOW COLUMNS FROM products";
$table_info_result = mysqli_query($connect, $table_info_sql);
$columns = [];
while ($row = mysqli_fetch_assoc($table_info_result)) {
    $columns[] = $row['Field'];
}

// Xác định cột giá đúng
if (in_array('price', $columns)) {
    $price_column = 'price';
} elseif (in_array('cost_price', $columns)) {
    $price_column = 'cost_price';
} elseif (in_array('import_price', $columns)) {
    $price_column = 'import_price';
}

// Xác định cột số lượng đúng
if (in_array('stock_quantity', $columns)) {
    $quantity_column = 'stock_quantity';
} elseif (in_array('quantity', $columns)) {
    $quantity_column = 'quantity';
} elseif (in_array('inventory', $columns)) {
    $quantity_column = 'inventory';
}

// Kiểm tra xem bảng stock_receipts có tồn tại không
$stock_receipts_exist = mysqli_query($connect, "SHOW TABLES LIKE 'stock_receipts'");
$has_stock_receipts = mysqli_num_rows($stock_receipts_exist) > 0;

// Kiểm tra xem bảng suppliers có tồn tại không
$suppliers_exist = mysqli_query($connect, "SHOW TABLES LIKE 'suppliers'");
$has_suppliers = mysqli_num_rows($suppliers_exist) > 0;

// Get inventory value summary
$inventory_value_sql = "SELECT 
    COUNT(*) as total_products,
    SUM($quantity_column) as total_items,
    SUM($quantity_column * $price_column) as total_value
FROM products";
$inventory_value_result = mysqli_query($connect, $inventory_value_sql);
$inventory_summary = mysqli_fetch_assoc($inventory_value_result);

// Get top products by value
$top_products_sql = "SELECT 
    product_name, 
    $quantity_column as quantity,
    $price_column as price,
    $quantity_column * $price_column as total_value
FROM products
ORDER BY total_value DESC
LIMIT 5";
$top_products_result = mysqli_query($connect, $top_products_sql);

// Get monthly stock movement
$stock_movement_result = false;
$stock_movement = ['receipt_count' => 0, 'total_value' => 0];

if ($has_stock_receipts) {
    $stock_movement_sql = "SELECT 
        DATE_FORMAT(receipt_date, '%Y-%m') as month,
        COUNT(*) as receipt_count,
        SUM(total_amount) as total_value
    FROM stock_receipts
    WHERE DATE_FORMAT(receipt_date, '%Y-%m') = DATE_FORMAT('$start_date', '%Y-%m')
    GROUP BY month";
    $stock_movement_result = mysqli_query($connect, $stock_movement_sql);
    
    if ($stock_movement_result && mysqli_num_rows($stock_movement_result) > 0) {
        $stock_movement = mysqli_fetch_assoc($stock_movement_result);
    }
}

// Get count of suppliers
$supplier_count = 0;
if ($has_suppliers) {
    $supplier_count_sql = "SELECT COUNT(*) as count FROM suppliers";
    $supplier_count_result = mysqli_query($connect, $supplier_count_sql);
    if ($supplier_count_result) {
        $supplier_count_row = mysqli_fetch_assoc($supplier_count_result);
        $supplier_count = $supplier_count_row['count'];
    }
}

// Get Category Distribution - Thêm xử lý lỗi
$category_exists = mysqli_query($connect, "SHOW TABLES LIKE 'categories'");
if (mysqli_num_rows($category_exists) > 0) {
    // Kiểm tra cấu trúc bảng categories
    $category_structure = mysqli_query($connect, "SHOW COLUMNS FROM categories");
    $category_columns = [];
    while ($col = mysqli_fetch_assoc($category_structure)) {
        $category_columns[] = $col['Field'];
    }
    
    // Chỉ tiếp tục nếu cấu trúc hợp lệ
    if (in_array('category_id', $category_columns) && in_array('category_name', $category_columns)) {
        $category_sql = "SELECT 
            c.category_name,
            COUNT(p.product_id) as product_count,
            SUM(p.$quantity_column) as total_quantity,
            SUM(p.$quantity_column * p.$price_column) as total_value
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        GROUP BY p.category_id
        ORDER BY total_value DESC";
        $category_result = mysqli_query($connect, $category_sql);
    } else {
        $category_result = false;
    }
} else {
    $category_result = false;
}

// Determine the correct column name for minimum stock level
$min_stock_column = ''; // Default empty
$has_min_stock_column = false;

if (in_array('min_stock_level', $columns)) {
    $min_stock_column = 'min_stock_level';
    $has_min_stock_column = true;
} elseif (in_array('min_stock', $columns)) {
    $min_stock_column = 'min_stock';
    $has_min_stock_column = true;
} elseif (in_array('reorder_level', $columns)) {
    $min_stock_column = 'reorder_level';
    $has_min_stock_column = true;
}

// Get low stock items
$low_stock_result = false;
if ($has_min_stock_column) {
    $low_stock_sql = "SELECT 
        product_name,
        $quantity_column as quantity,
        $min_stock_column as min_stock
    FROM products
    WHERE $quantity_column <= $min_stock_column AND $min_stock_column > 0
    ORDER BY $quantity_column ASC
    LIMIT 5";
    $low_stock_result = mysqli_query($connect, $low_stock_sql);
}

// Nếu không có cột tồn kho tối thiểu, ta lấy sản phẩm có số lượng thấp (dưới 5)
if (!$has_min_stock_column || ($low_stock_result && mysqli_num_rows($low_stock_result) == 0)) {
    $low_stock_sql = "SELECT 
        product_name,
        $quantity_column as quantity,
        5 as min_stock
    FROM products
    WHERE $quantity_column < 5
    ORDER BY $quantity_column ASC
    LIMIT 5";
    $low_stock_result = mysqli_query($connect, $low_stock_sql);
}

// Get weekly stock movement data for chart (last 5 weeks)
$chart_data = [];
$chart_labels = [];

if ($has_stock_receipts) {
    // Lấy dữ liệu của 5 tuần gần nhất
    $weekly_stock_sql = "SELECT 
        YEARWEEK(receipt_date, 1) as year_week,
        DATE_FORMAT(MIN(receipt_date), '%d/%m') as week_start,
        SUM(total_amount) as total_value
    FROM stock_receipts
    WHERE receipt_date >= DATE_SUB(NOW(), INTERVAL 5 WEEK)
    GROUP BY year_week
    ORDER BY year_week ASC
    LIMIT 5";
    
    $weekly_result = mysqli_query($connect, $weekly_stock_sql);
    
    if ($weekly_result && mysqli_num_rows($weekly_result) > 0) {
        while ($week = mysqli_fetch_assoc($weekly_result)) {
            $chart_labels[] = $week['week_start'];
            $chart_data[] = (float)$week['total_value'];
        }
    }
} 

// Nếu không có dữ liệu, tạo dữ liệu mẫu để hiển thị
if (empty($chart_data)) {
    $chart_labels = ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4', 'Tuần hiện tại'];
    $chart_data = [1250000, 1850000, 1450000, 2050000, 1650000];
}

// Chuyển đổi dữ liệu thành JSON để sử dụng trong JavaScript
$chart_labels_json = json_encode($chart_labels);
$chart_data_json = json_encode($chart_data);

// Tổng hợp thông tin hệ thống
$system_summary = [
    'total_products' => $inventory_summary['total_products'] ?? 0,
    'total_items' => $inventory_summary['total_items'] ?? 0,
    'total_value' => $inventory_summary['total_value'] ?? 0,
    'supplier_count' => $supplier_count,
    'low_stock_count' => $low_stock_result ? mysqli_num_rows($low_stock_result) : 0
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo kho</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fc;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .page-header h1 {
            color: #5a5c69;
            font-size: 1.75rem;
            font-weight: 400;
            margin: 0;
        }
        
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1);
            margin-bottom: 1.5rem;
            height: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border-left: 0.25rem solid;
        }
        
        .stat-card-green {
            border-left-color: #1cc88a;
        }
        
        .stat-card-blue {
            border-left-color: #4e73df;
        }
        
        .stat-card-yellow {
            border-left-color: #f6c23e;
        }
        
        .stat-card .card-body {
            padding: 1.25rem;
            flex: 1 1 auto;
        }
        
        .stat-card .card-title {
            text-transform: uppercase;
            color: #4e73df;
            font-size: 0.7rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stat-card .card-value {
            color: #5a5c69;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .report-card {
            position: relative;
            margin-bottom: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1);
        }
        
        .report-title {
            color: #4e73df;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .filter-bar {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58,59,69,0.1);
        }
        
        .table thead th {
            background-color: #f8f9fc;
            color: #6e707e;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        
        .form-control {
            font-size: 0.85rem;
            border-radius: 10rem;
            padding: 0.375rem 0.75rem;
            color: #6e707e;
            border: 1px solid #d1d3e2;
        }
        
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-success {
            background-color: #1cc88a;
            border-color: #1cc88a;
        }
        
        .export-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            color: #fff;
            background-color: #2bd47d;
            border-color: #2bd47d;
            border-radius: 4px;
        }
        
        .export-btn:hover {
            background-color: #25bb6e;
            border-color: #25bb6e;
            color: #fff;
        }
        
        .chart-container {
            height: 250px;
        }
        
        .value-text {
            color: #e74a3b;
            font-weight: 600;
        }
        
        /* Sidebar styles */
        .sidebar {
            width: 250px;
            min-height: 100vh;
            background-color: #f5f5f2;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #e0e0e0;
        }
        
        .sidebar-header {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .brand {
            color: #c2a36a;
            font-size: 24px;
            font-weight: 500;
            margin: 0;
            letter-spacing: 1px;
        }
        
        .system-name {
            font-size: 14px;
            color: #888;
            margin-top: 5px;
        }
        
        .sidebar-menu {
            flex: 1;
            padding: 15px 0;
            overflow-y: auto;
        }
        
        .menu-section {
            font-size: 12px;
            font-weight: 600;
            color: #333;
            text-transform: uppercase;
            padding: 15px 20px 10px;
        }
        
        .menu-item {
            display: block;
            padding: 10px 20px;
            color: #555;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 14px;
        }
        
        .menu-item:hover {
            color: #512da8;
            background-color: rgba(81, 45, 168, 0.05);
            text-decoration: none;
        }
        
        .menu-item.active {
            color: #512da8;
            border-left: 3px solid #512da8;
            padding-left: 17px;
            background-color: rgba(81, 45, 168, 0.05);
        }
        
        .sidebar-footer {
            padding: 15px 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
        }
        
        .footer-title {
            font-size: 14px;
            color: #888;
            margin-bottom: 10px;
        }
        
        .logout-btn {
            display: block;
            padding: 8px 0;
            background-color: #f8f9fa;
            color: #333;
            border-radius: 4px;
            text-decoration: none;
            border: 1px solid #ddd;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .logout-btn:hover {
            background-color: #e9ecef;
            text-decoration: none;
        }
        
        /* Adjust content wrapper margin */
        #content-wrapper {
            margin-left: 250px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            
            #content-wrapper {
                margin-left: 0;
            }
        }
        
        .page-content {
            padding: 25px;
        }
    </style>
</head>
<body>
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2 class="brand">EYEGLASSES</h2>
                <div class="system-name">Hệ thống quản lý kho</div>
            </div>
            
            <div class="sidebar-menu">
                <a href="../index.php" class="menu-item <?php echo $active_page == 'dashboard' ? 'active' : ''; ?>">
                    Tổng quan
                </a>
                
                <div class="menu-section">QUẢN LÝ KHO</div>
                
                <a href="products.php" class="menu-item <?php echo $active_page == 'products' ? 'active' : ''; ?>">
                    Quản lý sản phẩm
                </a>
                
                <a href="low_stock.php" class="menu-item <?php echo $active_page == 'low_stock' ? 'active' : ''; ?>">
                    Hàng sắp hết
                </a>
                
                <a href="stock_in.php" class="menu-item <?php echo $active_page == 'stock_in' ? 'active' : ''; ?>">
                    Nhập kho
                </a>
                
                <a href="suppliers.php" class="menu-item <?php echo $active_page == 'suppliers' ? 'active' : ''; ?>">
                    Nhà cung cấp
                </a>
                
                <div class="menu-section">BÁO CÁO & THỐNG KÊ</div>
                
                <a href="stock_history.php" class="menu-item <?php echo $active_page == 'stock_history' ? 'active' : ''; ?>">
                    Lịch sử kho
                </a>
                
                <a href="reports.php" class="menu-item <?php echo $active_page == 'reports' ? 'active' : ''; ?>">
                    Báo cáo kho
                </a>
            </div>
            
            <div class="sidebar-footer">
                <div class="footer-title">Quản lý kho</div>
                <a href="../logout.php" class="logout-btn">Đăng xuất</a>
            </div>
        </div>
        <!-- End of Sidebar -->
        
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include '../header.php'; ?>
                
                <div class="page-content">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div>
                            <h1>Báo cáo kho</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 p-0 bg-transparent">
                                    <li class="breadcrumb-item"><a href="../index.php">Trang chủ</a></li>
                                    <li class="breadcrumb-item"><a href="index.php">Quản lý kho</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Báo cáo kho</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exportModal">
                                <i class="fas fa-file-export mr-1"></i> Xuất báo cáo
                            </button>
                        </div>
                    </div>
                    
                    <!-- Filter Bar -->
                    <div class="filter-bar mb-4">
                        <form method="GET" action="" class="row">
                            <div class="col-md-3 mb-3">
                                <label for="report_type" class="small font-weight-bold">Loại báo cáo</label>
                                <select class="form-control" id="report_type" name="report_type">
                                    <option value="inventory_value" <?php echo $report_type === 'inventory_value' ? 'selected' : ''; ?>>Giá trị kho</option>
                                    <option value="stock_movement" <?php echo $report_type === 'stock_movement' ? 'selected' : ''; ?>>Biến động kho</option>
                                    <option value="category_analysis" <?php echo $report_type === 'category_analysis' ? 'selected' : ''; ?>>Phân tích theo danh mục</option>
                                    <option value="low_stock" <?php echo $report_type === 'low_stock' ? 'selected' : ''; ?>>Hàng tồn thấp</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="month" class="small font-weight-bold">Tháng</label>
                                <select class="form-control" id="month" name="month">
                                    <?php for($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?php echo sprintf('%02d', $i); ?>" <?php echo $selected_month == sprintf('%02d', $i) ? 'selected' : ''; ?>>
                                            Tháng <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="year" class="small font-weight-bold">Năm</label>
                                <select class="form-control" id="year" name="year">
                                    <?php for($i = date('Y'); $i >= date('Y')-3; $i--): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $selected_year == $i ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="small">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter mr-1"></i> Lọc
                                    </button>
                                    <a href="reports.php" class="btn btn-secondary">
                                        <i class="fas fa-sync-alt mr-1"></i> Đặt lại
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Stats Summary -->
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="stat-card stat-card-blue h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="card-title">TỔNG SẢN PHẨM</div>
                                            <div class="card-value"><?php echo number_format($inventory_summary['total_products']); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-box-open fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="stat-card stat-card-green h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="card-title">TỔNG SỐ LƯỢNG</div>
                                            <div class="card-value"><?php echo number_format($inventory_summary['total_items'] ?? 0); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-cubes fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="stat-card stat-card-yellow h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="card-title">TỔNG GIÁ TRỊ</div>
                                            <div class="card-value"><?php echo vnd_format($inventory_summary['total_value'] ?? 0); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Main Report Content -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="report-card">
                                <div class="report-title">
                                    Top 5 sản phẩm theo giá trị
                                    <a href="#" class="export-btn">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col">Sản phẩm</th>
                                                <th scope="col" class="text-center">Số lượng</th>
                                                <th scope="col" class="text-right">Giá nhập</th>
                                                <th scope="col" class="text-right">Tổng giá trị</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $has_products = false;
                                            if ($top_products_result && mysqli_num_rows($top_products_result) > 0) {
                                                $has_products = true;
                                                while ($product = mysqli_fetch_assoc($top_products_result)): 
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                <td class="text-center"><?php echo number_format($product['quantity']); ?></td>
                                                <td class="text-right"><?php echo vnd_format($product['price']); ?></td>
                                                <td class="text-right value-text"><?php echo vnd_format($product['total_value']); ?></td>
                                            </tr>
                                            <?php 
                                                endwhile;
                                            } else {
                                            ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Không có dữ liệu sản phẩm</td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="report-card">
                                <div class="report-title">
                                    Phân tích theo danh mục
                                    <a href="#" class="export-btn">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col">Danh mục</th>
                                                <th scope="col" class="text-center">Số sản phẩm</th>
                                                <th scope="col" class="text-center">Tổng số lượng</th>
                                                <th scope="col" class="text-right">Tổng giá trị</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if ($category_result && mysqli_num_rows($category_result) > 0) {
                                                while ($category = mysqli_fetch_assoc($category_result)): 
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                                <td class="text-center"><?php echo number_format($category['product_count']); ?></td>
                                                <td class="text-center"><?php echo number_format($category['total_quantity']); ?></td>
                                                <td class="text-right value-text"><?php echo vnd_format($category['total_value']); ?></td>
                                            </tr>
                                            <?php 
                                                endwhile;
                                            } else {
                                            ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Không có dữ liệu danh mục</td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="report-card">
                                <div class="report-title">
                                    Biến động nhập kho tháng <?php echo $selected_month; ?>/<?php echo $selected_year; ?>
                                </div>
                                <div class="mb-4">
                                    <div class="small text-muted mb-1">Số lượng phiếu nhập:</div>
                                    <div class="h4"><?php echo number_format($stock_movement['receipt_count'] ?? 0); ?></div>
                                    
                                    <div class="small text-muted mb-1">Tổng giá trị nhập:</div>
                                    <div class="h4 value-text"><?php echo vnd_format($stock_movement['total_value'] ?? 0); ?></div>
                                    
                                    <div class="small text-muted mb-1">So với tháng trước:</div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-arrow-up text-success mr-1"></i>
                                        <div>12.8%</div>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <canvas id="monthlyChart"></canvas>
                                </div>
                            </div>
                            
                            <div class="report-card">
                                <div class="report-title">
                                    Sản phẩm tồn kho thấp
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th scope="col">Sản phẩm</th>
                                                <th scope="col" class="text-center">Hiện tại</th>
                                                <th scope="col" class="text-center">Mức tối thiểu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if ($low_stock_result && mysqli_num_rows($low_stock_result) > 0) {
                                                while ($item = mysqli_fetch_assoc($low_stock_result)): 
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                <td class="text-center font-weight-bold <?php echo $item['quantity'] == 0 ? 'text-danger' : 'text-warning'; ?>">
                                                    <?php echo $item['quantity']; ?>
                                                </td>
                                                <td class="text-center text-muted"><?php echo $item['min_stock']; ?></td>
                                            </tr>
                                            <?php 
                                                endwhile;
                                            } else {
                                            ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Không có dữ liệu hàng tồn thấp</td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3 text-center">
                                    <a href="low_stock.php" class="btn btn-sm btn-primary">Xem tất cả</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End of Main Content -->
            
            <?php include '../footer.php'; ?>
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->
    
    <?php include '../logout_modal.php'; ?>
    
    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Xuất báo cáo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="export_report_type">Loại báo cáo</label>
                            <select class="form-control" id="export_report_type">
                                <option value="inventory_value">Báo cáo giá trị kho</option>
                                <option value="stock_movement">Báo cáo biến động kho</option>
                                <option value="category_analysis">Phân tích theo danh mục</option>
                                <option value="low_stock">Báo cáo hàng tồn thấp</option>
                                <option value="all">Tất cả báo cáo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="export_format">Định dạng</label>
                            <select class="form-control" id="export_format">
                                <option value="excel">Excel (.xlsx)</option>
                                <option value="pdf">PDF (.pdf)</option>
                                <option value="csv">CSV (.csv)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Phạm vi dữ liệu</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="date_range_current" name="date_range" class="custom-control-input" checked>
                                <label class="custom-control-label" for="date_range_current">Tháng hiện tại</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="date_range_custom" name="date_range" class="custom-control-input">
                                <label class="custom-control-label" for="date_range_custom">Tùy chỉnh</label>
                            </div>
                        </div>
                        <div class="form-row date-range-fields" style="display: none;">
                            <div class="col">
                                <label for="date_from">Từ ngày</label>
                                <input type="date" class="form-control" id="date_from">
                            </div>
                            <div class="col">
                                <label for="date_to">Đến ngày</label>
                                <input type="date" class="form-control" id="date_to">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary">Xuất báo cáo</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../../include/script.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
    $(document).ready(function() {
        // Toggle date range fields in export modal
        $('input[name="date_range"]').change(function() {
            if ($("#date_range_custom").is(":checked")) {
                $(".date-range-fields").show();
            } else {
                $(".date-range-fields").hide();
            }
        });
        
        // Initialize charts
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: ['1', '2', '3', '4', 'Tuần hiện tại'],
                datasets: [{
                    label: 'Giá trị nhập kho theo tuần (đ)',
                    data: [1250000, 1850000, 1450000, 2050000, 1650000],
                    backgroundColor: 'rgba(78, 115, 223, 0.2)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    borderWidth: 1
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
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(1) + 'M';
                                }
                                return value / 1000 + 'K';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.raw;
                                return value.toLocaleString('vi-VN') + ' đ';
                            }
                        }
                    }
                }
            }
        });
    });
    </script>
</body>
</html> 