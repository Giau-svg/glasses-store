<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Xác định tab nào đang active dựa trên URL hiện tại
$is_dashboard = ($current_dir == 'dashboard');
$is_products = ($current_dir == 'products');
$is_categories = ($current_dir == 'categories');
$is_manufactures = ($current_dir == 'manufactures');
$is_orders = ($current_dir == 'orders');
$is_users = ($current_dir == 'users');
$is_accounts = ($current_dir == 'accounts');
$is_settings = ($current_dir == 'settings');
?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../dashboard/index.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-glasses"></i>
        </div>
        <div class="sidebar-brand-text mx-3">EYEGLASSES</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?php echo $is_dashboard ? 'active' : ''; ?>">
        <a class="nav-link" href="../dashboard/index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Nav Item - Accounts (Tất cả tài khoản) -->
    <li class="nav-item <?php echo ($is_accounts || $is_users) ? 'active' : ''; ?>">
        <a class="nav-link" href="../accounts/index.php">
            <i class="fas fa-fw fa-user-shield"></i>
            <span>Tài khoản</span>
        </a>
    </li>

    <!-- Nav Item - Customers -->
    <li class="nav-item <?php echo $is_accounts ? 'active' : ''; ?>">
        <a class="nav-link" href="../accounts/index.php">
            <i class="fas fa-fw fa-user-shield"></i>
            <span>Tài khoản</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar --> 