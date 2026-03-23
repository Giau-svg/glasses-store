<?php
    require '../check_admin_login.php'; 
    require '../root.php';

    // Bật hiển thị lỗi cho mục đích debug
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Xử lý tìm kiếm và lọc
    $search = trim($_GET['search'] ?? '');
    $status_filter = $_GET['status'] ?? '';

    // Phân trang
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max(1, $page); // Đảm bảo page >= 1
    $records_per_page = 10;
    $offset = ($page - 1) * $records_per_page;

    // Xây dựng điều kiện WHERE cho câu truy vấn
    $where_conditions = [];
    if (!empty($search)) {
        $search_escaped = mysqli_real_escape_string($connect, $search);
        $where_conditions[] = "(o.shipping_name LIKE '%$search_escaped%' OR u.full_name LIKE '%$search_escaped%' OR o.order_date LIKE '%$search_escaped%')";
    }
    if (!empty($status_filter)) {
        $where_conditions[] = "o.order_status = '$status_filter'";
    }
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Đếm tổng số bản ghi để phân trang
    $sqlCount = "SELECT COUNT(*) as total FROM orders o $where_clause";
    $resultCount = mysqli_query($connect, $sqlCount);
    $row = mysqli_fetch_assoc($resultCount);
    $total_records = $row['total'];
    $total_pages = ceil($total_records / $records_per_page);

    // Đếm số đơn hàng mới (tạo trong 1 giờ gần đây và trạng thái chờ xử lý)
    $sqlNewOrders = "SELECT COUNT(*) as new_orders FROM orders 
                    WHERE order_status = 'pending' 
                    AND order_date >= NOW() - INTERVAL 1 HOUR";
    $resultNewOrders = mysqli_query($connect, $sqlNewOrders);
    $newOrdersCount = mysqli_fetch_assoc($resultNewOrders)['new_orders'];

    // Lấy thời gian đơn hàng mới nhất
    $sqlLatestOrder = "SELECT MAX(order_date) as latest_order FROM orders";
    $resultLatestOrder = mysqli_query($connect, $sqlLatestOrder);
    $latestOrderTime = mysqli_fetch_assoc($resultLatestOrder)['latest_order'];

    // Thời gian cập nhật cuối cùng
    $lastUpdated = date('H:i:s d/m/Y');

    // Lấy danh sách đơn hàng
    $sql = "SELECT o.*, u.full_name, u.email, u.phone
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            $where_clause
            ORDER BY  
            CASE 
                WHEN o.order_status = 'pending' THEN 1
                WHEN o.order_status = 'processing' THEN 2
                WHEN o.order_status = 'shipped' THEN 3
                WHEN o.order_status = 'delivered' THEN 4
                WHEN o.order_status = 'cancelled' THEN 5
            END,
            o.order_date DESC
            LIMIT $offset, $records_per_page";
    $result = mysqli_query($connect, $sql);
    if(empty($result)) {
        header('location:../partials/404.php');
    }

    // Hàm định dạng tiền tệ
    function vnd_format($amount) {
        return number_format($amount, 0, ',', '.') . ' đ';
    }

    // Hàm tạo đơn hàng mẫu cho mục đích demo
    function create_sample_orders() {
        global $connect;
        
        // Kiểm tra xem đã có đơn hàng nào chưa
        $check_query = "SELECT COUNT(*) as count FROM orders";
        $check_result = mysqli_query($connect, $check_query);
        $order_count = mysqli_fetch_assoc($check_result)['count'];
        
        // Nếu đã có đơn hàng, không tạo thêm
        if ($order_count > 0) {
            return;
        }
        
        // Danh sách trạng thái đơn hàng
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        // Thông tin người dùng demo
        $user_id = 1; // Giả sử ID người dùng mặc định là 1
        
        // Tạo 5 đơn hàng mẫu
        for ($i = 1; $i <= 5; $i++) {
            $order_date = date('Y-m-d H:i:s', strtotime("-$i day"));
            $status = $statuses[array_rand($statuses)];
            $total_amount = rand(500000, 3000000);
            $shipping_name = "Khách hàng mẫu $i";
            $shipping_phone = "098765432$i";
            $shipping_address = "Địa chỉ mẫu $i, Quận X, TP HCM";
            
            $sql = "INSERT INTO orders (user_id, order_date, total_amount, order_status, payment_status, shipping_name, shipping_phone, shipping_address)
                    VALUES ('$user_id', '$order_date', '$total_amount', '$status', 'pending', '$shipping_name', '$shipping_phone', '$shipping_address')";
            mysqli_query($connect, $sql);
            
            // Lấy ID đơn hàng vừa tạo
            $order_id = mysqli_insert_id($connect);
            
            // Tạo các mục chi tiết đơn hàng
            $product_id = rand(1, 10); // Giả sử có 10 sản phẩm
            $quantity = rand(1, 3);
            $unit_price = round($total_amount / $quantity);
            $subtotal = $unit_price * $quantity;
            
            $detail_sql = "INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal)
                          VALUES ('$order_id', '$product_id', '$quantity', '$unit_price', '$subtotal')";
            mysqli_query($connect, $detail_sql);
        }
    }

    // Tạo đơn hàng mẫu nếu không có đơn hàng nào
    create_sample_orders();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Quản lý Đơn hàng - EYEGLASSES</title>
    
    <!-- Custom fonts for this template-->
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles for this template-->
    <link href="../public/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="../public/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        @keyframes blink {
            0% { background-color: rgba(255, 193, 7, 0.1); }
            50% { background-color: rgba(255, 193, 7, 0.3); }
            100% { background-color: rgba(255, 193, 7, 0.1); }
        }
        
        .blinking {
            animation: blink 1s infinite;
        }
        
        .refresh-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .new-order-badge {
            background-color: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: bold;
            animation: blink 1s infinite;
        }
    </style>
    <script>
        // Tự động làm mới trang sau mỗi 15 giây để cập nhật đơn hàng mới
        let refreshTimeout;
        
        function startRefreshTimer() {
            refreshTimeout = setTimeout(function() {
                checkForNewOrders();
            }, 15000);
        }
        
        // Kiểm tra đơn hàng mới và thông báo
        function checkForNewOrders() {
            // Lưu số lượng đơn hàng hiện tại để so sánh sau khi làm mới
            const currentOrderCount = <?php echo $total_records; ?>;
            
            fetch('check_new_orders.php')
                .then(response => response.json())
                .then(data => {
                    if (data.new_orders > 0) {
                        // Có đơn hàng mới, chơi âm thanh thông báo
                        playNotificationSound();
                        
                        // Thêm một thông báo nhỏ
                        showNotification('Có ' + data.new_orders + ' đơn hàng mới!');
                        
                        // Làm mới trang
                        window.location.reload();
                    } else {
                        // Không có đơn hàng mới, tiếp tục hẹn giờ
                        startRefreshTimer();
                    }
                })
                .catch(error => {
                    console.error('Lỗi kiểm tra đơn hàng mới:', error);
                    startRefreshTimer();
                });
        }
        
        // Phát âm thanh thông báo
        function playNotificationSound() {
            const audio = new Audio('../public/sounds/notification.mp3');
            audio.play().catch(e => console.log('Không thể phát âm thanh:', e));
        }
        
        // Hiển thị thông báo
        function showNotification(message) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('Đơn hàng mới', {
                    body: message,
                    icon: '../public/img/logo.png'
                });
            }
        }
        
        // Yêu cầu quyền thông báo
        function requestNotificationPermission() {
            if ('Notification' in window) {
                Notification.requestPermission();
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            let seconds = 15;
            const countdownElement = document.getElementById('refreshCountdown');
            
            const interval = setInterval(function() {
                seconds--;
                countdownElement.textContent = `Làm mới sau: ${seconds}s`;
                
                if (seconds <= 0) {
                    clearInterval(interval);
                }
            }, 1000);
            
            startRefreshTimer();
            requestNotificationPermission();
            
            // Kiểm tra xem có đơn hàng mới hay không để hiệu ứng nhấp nháy
            const newOrdersCount = <?php echo $newOrdersCount; ?>;
            if (newOrdersCount > 0) {
                const infoCard = document.querySelector('.card-header h6');
                if (infoCard) {
                    infoCard.innerHTML += ` <span class="new-order-badge">${newOrdersCount} mới</span>`;
                }
            }
        });
        
        // Hủy hẹn giờ khi người dùng rời khỏi trang
        window.addEventListener('beforeunload', function() {
            clearTimeout(refreshTimeout);
        });
    </script>
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
                        <h1 class="h3 mb-0 text-gray-800">Quản lý Đơn hàng</h1>
                        <div class="refresh-info">
                            <?php if ($newOrdersCount > 0): ?>
                                <span class="badge badge-danger"><?php echo $newOrdersCount; ?> đơn hàng mới</span>
                            <?php endif; ?>
                            <span class="text-muted small">Cập nhật: <?php echo $lastUpdated; ?></span>
                            <span id="refreshCountdown" class="text-muted mr-2">Làm mới sau: 15s</span>
                            <a href="javascript:window.location.reload();" class="btn btn-sm btn-primary">
                                <i class="fas fa-sync-alt fa-sm"></i> Làm mới ngay
                            </a>
                        </div>
                    </div>

                    <!-- Filter and Search -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm & Lọc</h6>
                        </div>
                        <div class="card-body">
                            <form method="get" class="form-inline">
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="search" class="sr-only">Tìm kiếm</label>
                                    <input type="text" class="form-control" id="search" name="search" placeholder="Tên khách hàng hoặc ngày đặt..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                </div>
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="status" class="sr-only">Trạng thái</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">-- Tất cả trạng thái --</option>
                                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Chờ xử lý</option>
                                        <option value="processing" <?php echo ($status_filter == 'processing') ? 'selected' : ''; ?>>Đang xử lý</option>
                                        <option value="shipped" <?php echo ($status_filter == 'shipped') ? 'selected' : ''; ?>>Đang giao hàng</option>
                                        <option value="delivered" <?php echo ($status_filter == 'delivered') ? 'selected' : ''; ?>>Hoàn thành</option>
                                        <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary mb-2">
                                    <i class="fas fa-search fa-sm"></i> Tìm kiếm
                                </button>
                                <a href="index.php" class="btn btn-secondary mb-2 ml-2">
                                    <i class="fas fa-sync-alt fa-sm"></i> Đặt lại
                                </a>
                            </form>
                        </div>
                    </div>

                    <!-- Orders List -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách Đơn hàng</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Ngày đặt</th>
                                            <th>Khách hàng</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($order = mysqli_fetch_assoc($result)): 
                                                // Kiểm tra xem đơn hàng có phải là đơn hàng mới hay không (trong vòng 1 giờ)
                                                $isNewOrder = (strtotime($order['order_date']) >= strtotime('-1 hour'));
                                                $rowClass = ($isNewOrder && $order['order_status'] == 'pending') ? 'blinking' : '';
                                            ?>
                                                <tr class="<?php echo $rowClass; ?>">
                                                    <td>
                                                        #<?php echo $order['order_id']; ?>
                                                        <?php if ($isNewOrder && $order['order_status'] == 'pending'): ?>
                                                            <span class="badge badge-pill badge-danger">Mới</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                                    <td>
                                                        <?php if (!empty($order['full_name'])): ?>
                                                            <p><strong>Người đặt:</strong> <?php echo htmlspecialchars($order['full_name'] ?? ''); ?></p>
                                                        <?php endif; ?>
                                                        <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['shipping_name'] ?? ''); ?></p>
                                                        <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['shipping_phone'] ?? ''); ?></p>
                                                        <?php if (!empty($order['shipping_address'])): ?>
                                                            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars(substr($order['shipping_address'] ?? '', 0, 50) . (strlen($order['shipping_address']) > 50 ? '...' : '')); ?></p>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo vnd_format($order['total_amount']); ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php 
                                                            switch ($order['order_status']) {
                                                                case 'pending': echo 'warning'; break;
                                                                case 'processing': echo 'primary'; break;
                                                                case 'shipped': echo 'info'; break;
                                                                case 'delivered': echo 'success'; break;
                                                                case 'cancelled': echo 'danger'; break;
                                                                default: echo 'secondary';
                                                            }
                                                        ?>">
                                                            <?php 
                                                                switch ($order['order_status']) {
                                                                    case 'pending': echo 'Chờ xử lý'; break;
                                                                    case 'processing': echo 'Đang xử lý'; break;
                                                                    case 'shipped': echo 'Đang giao hàng'; break;
                                                                    case 'delivered': echo 'Hoàn thành'; break;
                                                                    case 'cancelled': echo 'Đã hủy'; break;
                                                                    default: echo $order['order_status'];
                                                                }
                                                            ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="detail.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info btn-sm mb-1">
                                                            <i class="fas fa-eye"></i> Xem chi tiết
                                                        </a>
                                                        
                                                        <?php if($order['order_status'] == 'pending'): ?>
                                                            <a href="update.php?id=<?php echo $order['order_id']; ?>&status=processing" class="btn btn-primary btn-sm mb-1">
                                                                <i class="fas fa-check"></i> Xử lý
                                                            </a>
                                                            <a href="update.php?id=<?php echo $order['order_id']; ?>&status=cancelled" class="btn btn-danger btn-sm mb-1" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                                                                <i class="fas fa-times"></i> Hủy
                                                            </a>
                                                        <?php elseif($order['order_status'] == 'processing'): ?>
                                                            <a href="update.php?id=<?php echo $order['order_id']; ?>&status=shipped" class="btn btn-primary btn-sm mb-1">
                                                                <i class="fas fa-shipping-fast"></i> Giao hàng
                                                            </a>
                                                            <a href="update.php?id=<?php echo $order['order_id']; ?>&status=cancelled" class="btn btn-danger btn-sm mb-1" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                                                                <i class="fas fa-times"></i> Hủy
                                                            </a>
                                                        <?php elseif($order['order_status'] == 'shipped'): ?>
                                                            <a href="update.php?id=<?php echo $order['order_id']; ?>&status=delivered" class="btn btn-success btn-sm mb-1">
                                                                <i class="fas fa-check-circle"></i> Hoàn thành
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted small d-block">Không thể cập nhật</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Không tìm thấy đơn hàng nào</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                            <div class="mt-3">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
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
</body>
</html>