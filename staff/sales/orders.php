<?php
require_once '../check_sales_login.php';
require_once '../../admin/root.php';

// Thiết lập trang hiện tại
$active_page = 'orders';
$page_title = 'Quản lý đơn hàng';

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
    if (is_numeric($search)) {
        $where_conditions[] = "o.order_id = '$search_escaped'";
    } else {
        $where_conditions[] = "(o.shipping_name LIKE '%$search_escaped%' OR o.shipping_phone LIKE '%$search_escaped%')";
    }
}
if (!empty($status_filter)) {
    $status_escaped = mysqli_real_escape_string($connect, $status_filter);
    $where_conditions[] = "o.order_status = '$status_escaped'";
}
$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Đếm tổng số bản ghi để phân trang
$sqlCount = "SELECT COUNT(*) as total FROM orders o $where_clause";
$resultCount = mysqli_query($connect, $sqlCount);
if (!$resultCount) {
    die("Lỗi truy vấn đếm bản ghi: " . mysqli_error($connect));
}
$row = mysqli_fetch_assoc($resultCount);
$total_records = $row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Lấy danh sách đơn hàng
$sql = "SELECT o.order_id, o.shipping_name, o.shipping_phone, o.shipping_address, o.total_amount, o.order_status, o.order_date, o.payment_method
        FROM orders o
        $where_clause
        ORDER BY  
        CASE 
            WHEN o.order_status = 'pending' THEN 1
            WHEN o.order_status = 'processing' THEN 2
            WHEN o.order_status = 'shipping' THEN 3
            WHEN o.order_status = 'delivered' THEN 4
            WHEN o.order_status = 'cancelled' THEN 5
        END,
        o.order_date DESC
        LIMIT $offset, $records_per_page";
$result_orders = mysqli_query($connect, $sql);
if (!$result_orders) {
    die("Lỗi truy vấn danh sách đơn hàng: " . mysqli_error($connect));
}

// Hàm định dạng số tiền VND
function vnd_format($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}

// Hàm trả về trạng thái đơn hàng tiếng Việt
function get_status_text($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning text-dark">Chờ xử lý</span>';
        case 'processing':
            return '<span class="badge bg-primary text-white">Đang xử lý</span>';
        case 'shipping':
            return '<span class="badge bg-info text-white">Đang giao</span>';
        case 'delivered':
            return '<span class="badge bg-success text-white">Hoàn thành</span>';
        case 'cancelled':
            return '<span class="badge bg-danger text-white">Đã hủy</span>';
        default:
            return '<span class="badge bg-secondary text-white">Không xác định</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include '../partials/head.php'; ?>
    <style>
        :root {
            --primary-color: #b39e7c;
            --secondary-color: #d2b48c;
            --accent-color: #f9f8f3;
            --text-color: #333;
            --border-color: #e3e6f0;
            --hover-color: #d4af37;
        }

        .container-fluid {
            padding: 4rem 1.5rem 1.5rem 1.5rem;
        }

        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-header {
            background-color: var(--accent-color);
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem;
            border-radius: 0.75rem 0.75rem 0 0 !important;
        }

        .card-header h6 {
            color: var(--primary-color);
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table th {
            background: var(--accent-color);
            color: var(--primary-color);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
            border-top: none;
            white-space: nowrap;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid var(--border-color);
        }

        .table tbody tr:hover {
            background-color: rgba(179, 158, 124, 0.05);
        }

        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 600;
            border-radius: 0.375rem;
        }

        .pagination {
            margin: 1.5rem 0 0;
            justify-content: center;
        }

        .page-link {
            padding: 0.5rem 0.75rem;
            margin: 0 0.25rem;
            color: var(--primary-color);
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .page-link:hover {
            background: var(--accent-color);
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .page-item.active .page-link {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'sales_sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'sales_topbar.php'; ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
                    </div>

                    <!-- Search and Filter -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm & Lọc</h6>
                        </div>
                        <div class="card-body">
                            <form method="get" class="form-inline">
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="search" class="sr-only">Tìm kiếm</label>
                                    <input type="text" class="form-control" id="search" name="search" placeholder="Nhập mã hóa đơn, tên khách hàng,..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                </div>
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="status" class="sr-only">Trạng thái</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">-- Tất cả trạng thái --</option>
                                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Chờ xử lý</option>
                                        <option value="processing" <?php echo ($status_filter == 'processing') ? 'selected' : ''; ?>>Đang xử lý</option>
                                        <option value="shipped" <?php echo ($status_filter == 'shipping') ? 'selected' : ''; ?>>Đang giao hàng</option>
                                        <option value="delivered" <?php echo ($status_filter == 'delivered') ? 'selected' : ''; ?>>Hoàn thành</option>
                                        <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary mb-2">
                                    <i class="fas fa-search fa-sm"></i> Tìm kiếm
                                </button>
                                <a href="orders.php" class="btn btn-secondary mb-2 ml-2">
                                    <i class="fas fa-sync-alt fa-sm"></i> Đặt lại
                                </a>
                            </form>
                        </div>
                    </div>

                    <!-- Orders Table -->
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
                                            <th>Khách hàng</th>
                                            <th>Số điện thoại</th>
                                            <th>Địa chỉ</th>
                                            <th>Phương thức</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày đặt</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result_orders) > 0): ?>
                                            <?php while ($order = mysqli_fetch_assoc($result_orders)): ?>
                                                <tr>
                                                    <td>#<?php echo $order['order_id']; ?></td>
                                                    <td><?php echo htmlspecialchars($order['shipping_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($order['shipping_phone']); ?></td>
                                                    <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                                                    <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                                    <td><?php echo vnd_format($order['total_amount']); ?></td>
                                                    <td><?php echo get_status_text($order['order_status']); ?></td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                                    <td>
                                                        <a href="order_detail.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info btn-sm">
                                                            <i class="fas fa-eye"></i> Xem
                                                        </a>
                                                        <?php if ($order['order_status'] == 'pending'): ?>
                                                            <a href="process_order.php?id=<?php echo $order['order_id']; ?>&action=approve" class="btn btn-success btn-sm">
                                                                <i class="fas fa-check"></i> Duyệt
                                                            </a>
                                                            <a href="process_order.php?id=<?php echo $order['order_id']; ?>&action=cancel" class="btn btn-danger btn-sm">
                                                                <i class="fas fa-times"></i> Hủy
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center">Không có đơn hàng nào</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
            <?php include '../partials/footer.php'; ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <script src="../public/vendor/jquery/jquery.min.js"></script>
    <script src="../public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../public/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../public/js/sb-admin-2.min.js"></script>
</body>
</html>