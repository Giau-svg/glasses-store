<?php
// Lấy thông tin người dùng đang đăng nhập
$staff_name = isset($_SESSION['staff_name']) ? $_SESSION['staff_name'] : 'Nhân viên kho';
?>
<nav class="topnav navbar navbar-expand shadow navbar-light bg-white">
    <!-- Sidebar Toggle Button -->
    <button class="btn btn-link order-lg-0 ms-2 me-lg-0" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Brand -->
    <a class="navbar-brand d-none d-lg-inline-block ms-2" href="dashboard.php">
        <div class="d-flex align-items-center">
            <span class="brand-name">EYEGLASSES INVENTORY</span>
        </div>
    </a>

    <!-- Search Form - show only on desktop -->
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        
    </form>

    <!-- Navbar -->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
   

        <!-- User Information -->
        <li class="nav-item">
            <a class="nav-link" id="userLogout" href="../logout.php">
                <div class="d-flex align-items-center">
                    <div class="me-2 d-none d-lg-inline text-end">
                        <span class="user-name"><?php echo htmlspecialchars($staff_name); ?></span>
                    </div>
                    <i class="fas fa-sign-out-alt fa-fw me-1"></i>
                </div>
            </a>
        </li>
    </ul>
</nav>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Bạn có chắc chắn muốn đăng xuất?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Chọn "Đăng xuất" để kết thúc phiên làm việc hiện tại.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <a class="btn btn-primary" href="../logout.php">Đăng xuất</a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Navbar styling */
    .topnav {
        height: 60px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .brand-text {
        font-weight: 600;
        letter-spacing: 0.5px;
        color: var(--wood-beige);
    }
    
    .btn-search {
        background-color: var(--wood-beige);
        color: white;
        border: 1px solid var(--wood-beige);
    }
    
    .btn-search:hover {
        background-color: var(--light-gold);
        border-color: var(--light-gold);
        color: var(--black);
    }
    
    .input-group .form-control {
        border-color: var(--wood-beige);
        border-right: none;
    }
    
    .user-name {
        font-weight: 500;
        color: var(--black);
    }
    
    .user-role {
        font-size: 0.8rem;
        color: var(--bs-gray-600);
    }
    
    .badge-counter {
        position: absolute;
        transform: scale(0.85);
        transform-origin: top right;
        right: 0.25rem;
        top: 0.1rem;
        background-color: var(--light-gold);
        color: var(--black);
        font-weight: 600;
        padding: 0.2rem 0.5rem;
        border-radius: 50%;
    }
    
    .dropdown-menu {
        border: none;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
    }
    
    .dropdown-header {
        font-weight: 600;
        background-color: var(--cream-white);
        color: var(--wood-beige);
    }
    
    .dropdown-item:hover {
        background-color: var(--cream-white);
    }
    
    .btn-sidebar-toggle {
        color: var(--wood-beige);
    }
    
    .btn-sidebar-toggle:hover {
        color: var(--light-gold);
    }
    
    /* Modal styling */
    .modal-header {
        background-color: var(--wood-beige);
        color: white;
    }
    
    .modal-footer .btn-primary {
        background-color: var(--wood-beige);
        border-color: var(--wood-beige);
    }
    
    .modal-footer .btn-primary:hover {
        background-color: var(--light-gold);
        border-color: var(--light-gold);
        color: var(--black);
    }
    
    /* Hide dropdown arrows */
    .dropdown-toggle::after {
        display: none !important;
    }
</style> 