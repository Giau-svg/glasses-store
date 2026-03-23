<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Xác định tab nào đang active dựa trên URL hiện tại
$is_dashboard = ($current_page == 'dashboard.php');
$is_products = ($current_page == 'products.php' || $current_page == 'add_product.php' || $current_page == 'edit_product.php');
$is_low_stock = ($current_page == 'low_stock.php');
$is_stock_in = ($current_page == 'stock_in.php');
$is_suppliers = ($current_page == 'suppliers.php' || $current_page == 'add_supplier.php' || $current_page == 'edit_supplier.php');
$is_history = ($current_page == 'stock_history.php');
$is_reports = ($current_page == 'stock_reports.php');
?>
<!-- Sidebar -->
<div class="sidebar-wrapper">
    <nav class="sidenav shadow-right sidenav-light">
        <!-- Sidebar Brand -->
        <div class="sidenav-brand">
            <a href="dashboard.php">
            <div class="text-muted">Hệ thống quản lý kho</div>
            </a>
        </div>
        <hr class="mt-0 mb-2">
        
        <div class="sidenav-menu">
            <div class="nav">
                <!-- Dashboard -->
                <a class="nav-link <?php echo $is_dashboard ? 'active' : ''; ?>" href="dashboard.php">
                    <div class="nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Tổng quan
                </a>
                
                <!-- QUẢN LÝ KHO -->
                <div class="sidenav-menu-heading">QUẢN LÝ KHO</div>
                
                <!-- Sản phẩm -->
                <a class="nav-link <?php echo $is_products ? 'active' : ''; ?>" href="products.php">
                    <div class="nav-link-icon"><i class="fas fa-glasses"></i></div>
                    Quản lý sản phẩm
                </a>
                
                <!-- Hàng sắp hết -->
                <a class="nav-link <?php echo $is_low_stock ? 'active' : ''; ?>" href="low_stock.php">
                    <div class="nav-link-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    Hàng sắp hết
                    <?php
                    // Đếm số sản phẩm sắp hết hàng
                    require_once '../../admin/root.php';
                    $sql = "SELECT COUNT(*) as count FROM products WHERE stock_quantity <= 10";
                    $sidebar_low_stock_result = mysqli_query($connect, $sql);
                    $low_stock_count = mysqli_fetch_assoc($sidebar_low_stock_result)['count'];
                    if ($low_stock_count > 0) {
                        echo '<div class="ms-auto"><span class="badge rounded-pill bg-warning">' . $low_stock_count . '</span></div>';
                    }
                    ?>
                    </a>
                
                <!-- Nhập kho -->
                <a class="nav-link <?php echo $is_stock_in ? 'active' : ''; ?>" href="stock_in.php">
                    <div class="nav-link-icon"><i class="fas fa-truck-loading"></i></div>
                    Nhập kho
                </a>
                
                <!-- Nhà cung cấp -->
                <a class="nav-link <?php echo $is_suppliers ? 'active' : ''; ?>" href="suppliers.php">
                    <div class="nav-link-icon"><i class="fas fa-building"></i></div>
                    Nhà cung cấp
                </a>
                
                <!-- BÁO CÁO & THỐNG KÊ -->
                <div class="sidenav-menu-heading">BÁO CÁO & THỐNG KÊ</div>
                
                <!-- Lịch sử kho -->
                <a class="nav-link <?php echo $is_history ? 'active' : ''; ?>" href="stock_history.php">
                    <div class="nav-link-icon"><i class="fas fa-history"></i></div>
                    Lịch sử kho
                </a>
                
                <!-- Báo cáo kho -->
                <a class="nav-link <?php echo $is_reports ? 'active' : ''; ?>" href="stock_reports.php">
                    <div class="nav-link-icon"><i class="fas fa-chart-bar"></i></div>
                    Báo cáo kho
                </a>
            </div>
        </div>
        
        <!-- User profile in sidebar -->
        
    </nav>
</div>

<!-- Thêm CSS -->
<style>
    .sidenav .nav-link {
        margin: 0 0.75rem;
        border-radius: 0.375rem;
        padding: 0.75rem 1rem;
    }
    
    .sidenav .nav-link.active {
        border-left: none;
    }
    
    .sidenav-menu-heading {
        margin-top: 1rem;
    }
    
    /* Hide sidebar dropdown indicators */
    .nav-item::after,
    .nav-item > a::after,
    .nav-item .collapse-inner::after {
        display: none !important;
    }
</style> 