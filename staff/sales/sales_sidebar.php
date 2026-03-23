<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Xác định tab nào đang active dựa trên URL hiện tại
$is_dashboard = ($current_page == 'dashboard.php');
$is_orders = ($current_page == 'orders.php' || $current_page == 'order_detail.php' || $current_page == 'pending_orders.php');
$is_invoices = ($current_page == 'print_invoice.php');
$is_reports = ($current_page == 'reports.php' || $current_page == 'sales_report.php');
$is_history = ($current_page == 'order_history.php');
$is_pending = ($current_page == 'pending_orders.php');
?>

<!-- Sidebar -->
<div class="sidebar-wrapper">
    <nav class="sidenav shadow-right sidenav-light">
        <!-- Sidebar Brand -->
        <div class="sidenav-brand">
            <a href="dashboard.php">
                <div class="brand-name fs-4">EYEGLASSES</div>
                <div class="small text-muted">Hệ thống bán hàng</div>
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
                
                <!-- QUẢN LÝ ĐƠN HÀNG -->
                <div class="sidenav-menu-heading">QUẢN LÝ ĐƠN HÀNG</div>
                
                <!-- Đơn hàng -->
                <a class="nav-link <?php echo ($is_orders && !$is_pending) ? 'active' : ''; ?>" href="orders.php">
                    <div class="nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                    Quản lý đơn hàng
                </a>
                
                <!-- Đơn hàng chờ xử lý -->
                <a class="nav-link <?php echo $is_pending ? 'active' : ''; ?>" href="pending_orders.php">
                    <div class="nav-link-icon"><i class="fas fa-hourglass-half"></i></div>
                    Đơn hàng chờ xử lý
                    <?php
                    // Đếm số đơn hàng chờ xử lý
                    require_once '../../admin/root.php';
                    $sql = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'";
                    $result = mysqli_query($connect, $sql);
                    $pending_count = mysqli_fetch_assoc($result)['count'];
                    if ($pending_count > 0) {
                        echo '<div class="ms-auto"><span class="badge rounded-pill bg-warning">' . $pending_count . '</span></div>';
                    }
                    ?>
                </a>
                
                <!-- In hóa đơn -->
                <a class="nav-link <?php echo $is_invoices ? 'active' : ''; ?>" href="print_invoice.php">
                </a>
                
                <!-- BÁO CÁO & THỐNG KÊ -->
                <div class="sidenav-menu-heading">BÁO CÁO & THỐNG KÊ</div>
                
                <!-- Báo cáo bán hàng -->
                <a class="nav-link <?php echo $is_reports ? 'active' : ''; ?>" href="reports.php">
                    <div class="nav-link-icon"><i class="fas fa-chart-line"></i></div>
                    Báo cáo bán hàng
                </a>
                
                <!-- Lịch sử đơn hàng -->
                <a class="nav-link <?php echo $is_history ? 'active' : ''; ?>" href="order_history.php">
                    <div class="nav-link-icon"><i class="fas fa-history"></i></div>
                    Lịch sử đơn hàng
                </a>
            </div>
        </div>
        
        <!-- User profile in sidebar -->
        <div class="sidenav-footer">
            <div class="user-info p-3 text-center">
                <div class="user-avatar mb-2">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-name mb-2"><?php echo isset($_SESSION['staff_name']) ? htmlspecialchars($_SESSION['staff_name']) : 'Nhân viên'; ?></div>
                <a href="../logout.php" class="btn btn-sm btn-outline-dark w-100">
                    <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
                </a>
            </div>
        </div>
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