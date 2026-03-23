<?php
// Lấy thông tin người dùng đang đăng nhập
$staff_name = isset($_SESSION['staff_name']) ? $_SESSION['staff_name'] : 'Nhân viên bán hàng';

// Xử lý tìm kiếm theo mã hóa đơn
$search_query = '';
$search_result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $search_query = trim($_POST['search']);
    require_once '../../admin/root.php';

    // Kiểm tra kết nối cơ sở dữ liệu
    if (!$connect) {
        die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
    }

    // Xử lý tìm kiếm
    if (!empty($search_query)) {
        $search_escaped = mysqli_real_escape_string($connect, $search_query);
        $sql_search = "SELECT * FROM orders WHERE order_id LIKE '%$search_escaped%'";
        $search_result = mysqli_query($connect, $sql_search);

        // Kiểm tra truy vấn
        if (!$search_result) {
            die("Truy vấn thất bại: " . mysqli_error($connect));
        }
    }
}
?>

<nav class="topnav navbar navbar-expand shadow navbar-light bg-white">
    <!-- Sidebar Toggle Button -->
    <button class="btn btn-link order-lg-0 ms-2 me-lg-0" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Brand -->
    <a class="navbar-brand d-none d-lg-inline-block ms-2" href="dashboard.php">
        <div class="d-flex align-items-center">
            <span class="brand-name">EYEGLASSES SALES</span>
        </div>
    </a>

    <!-- Search Form - show only on desktop -->
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0" method="POST" action="">
        <div class="input-group">
            <input class="form-control" type="text" name="search" placeholder="Tìm kiếm mã hóa đơn..." aria-label="Tìm kiếm mã hóa đơn..." aria-describedby="basic-addon2" value="<?php echo htmlspecialchars($search_query); ?>">
            <button class="btn btn-search" type="submit">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>

    <!-- Navbar -->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <!-- Orders Alert -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" id="alertsDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell"></i>
                <?php
                // Check for pending orders
                require_once '../../admin/root.php';
                $sql = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'";
                $result = mysqli_query($connect, $sql);
                $pending_count = mysqli_fetch_assoc($result)['count'];
                
                if ($pending_count > 0) {
                    echo '<span class="badge badge-counter">' . $pending_count . '</span>';
                }
                ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="alertsDropdown">
                <li>
                    <h6 class="dropdown-header">Thông báo</h6>
                </li>
                <?php
                if ($pending_count > 0) {
                    echo '<li><a class="dropdown-item" href="pending_orders.php">
                        <div class="d-flex align-items-center">
                            <div class="small text-warning"><i class="fas fa-circle me-1"></i> Đơn hàng chờ duyệt</div>
                            <span class="ms-auto badge bg-warning">' . $pending_count . '</span>
                        </div>
                    </a></li>';
                } else {
                    echo '<li><a class="dropdown-item"><div class="small text-gray">Không có thông báo mới</div></a></li>';
                }
                ?>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item text-center small text-gray" href="orders.php">Xem tất cả đơn hàng</a>
                </li>
            </ul>
        </li>

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

<?php
// Hiển thị kết quả tìm kiếm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($search_result)) {
    if (mysqli_num_rows($search_result) > 0) {
        // Lấy mã hóa đơn đầu tiên từ kết quả tìm kiếm
        $row = mysqli_fetch_assoc($search_result);
        $order_id = $row['order_id'];

        // Chuyển hướng đến trang chi tiết đơn hàng bằng thẻ <meta>
        echo '<meta http-equiv="refresh" content="0;url=order_detail.php?id=' . $order_id . '">';
        exit;
    } else {
        // Nếu không tìm thấy kết quả, hiển thị thông báo lỗi
        echo '<div class="container mt-3">';
        echo '<div class="alert alert-danger">Không tìm thấy đơn hàng với mã hóa đơn đã nhập.</div>';
        echo '</div>';
    }
}
?>

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