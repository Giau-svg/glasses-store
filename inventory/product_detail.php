<?php
require_once '../check_inventory_login.php'; // Kiểm tra đăng nhập
require_once '../../admin/root.php'; // Kết nối database và cài đặt gốc

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Thiết lập trang hiện tại (tùy chọn)
$active_page = 'products'; // Hoặc một trang mới nếu bạn muốn
$page_title = 'Chi tiết sản phẩm'; // Tiêu đề trang

// Kiểm tra kết nối database
if (!isset($connect)) {
    die("Không thể kết nối đến database");
}

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Kiểm tra xem có ID sản phẩm hợp lệ không
if ($product_id <= 0) {
    die("ID sản phẩm không hợp lệ."); // Xử lý trường hợp không có ID
}

// Truy vấn database để lấy thông tin chi tiết sản phẩm
$sql = "SELECT p.*, c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.product_id = ?";

$product_details = null;

if ($stmt = mysqli_prepare($connect, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $product_details = mysqli_fetch_assoc($result);
    } else {
        // Không tìm thấy sản phẩm với ID này
        die("Không tìm thấy sản phẩm.");
    }

    mysqli_stmt_close($stmt);
} else {
    die("Lỗi chuẩn bị truy vấn: " . mysqli_error($connect));
}

// Nếu tìm thấy sản phẩm, biến $product_details sẽ chứa dữ liệu

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include '../partials/head.php'; ?>
    <style>
        .product-detail-image {
            max-width: 300px; /* Điều chỉnh kích thước ảnh hiển thị */
            height: auto;
            object-fit: contain;
            border: 1px solid #ddd;
            padding: 5px;
            background-color: #fff;
        }
    </style>
</head>
<body id="page-top">

    <div id="wrapper">

        <?php include 'inventory_sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">
                <div id="page-content">

                    <?php include 'inventory_topbar.php'; ?>
                    <div class="container-fluid">

                        <div class="d-sm-flex align-items-center justify-content-between mb-4">
                            <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?>: <?php echo htmlspecialchars($product_details['product_name'] ?? '...'); ?></h1>
                             <a href="low_stock.php" class="btn btn-secondary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay lại
                            </a>
                        </div>

                        <?php if ($product_details): ?>
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Thông tin sản phẩm</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 text-center">
                                            <?php if (!empty($product_details['image_path'])): ?>
                                                <img src="../../uploads/products/<?php echo htmlspecialchars($product_details['image_path']); ?>"
                                                     alt="<?php echo htmlspecialchars($product_details['product_name']); ?>"
                                                     class="product-detail-image">
                                            <?php else: ?>
                                                <img src="../../uploads/products/no-image.jpg"
                                                     alt="No Image"
                                                     class="product-detail-image">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-8">
                                            <p><strong>ID Sản phẩm:</strong> <?php echo $product_details['product_id']; ?></p>
                                            <p><strong>Tên sản phẩm:</strong> <?php echo htmlspecialchars($product_details['product_name']); ?></p>
                                            <p><strong>Loại sản phẩm:</strong> <?php echo htmlspecialchars($product_details['category_name'] ?? 'N/A'); ?></p>
                                            <p><strong>Giá nhập:</strong> <?php echo number_format($product_details['cost_price'], 0, ',', '.'); ?> đ</p>
                                            <p><strong>Giá bán:</strong> <?php echo number_format($product_details['price'], 0, ',', '.'); ?> đ</p>
                                            <p><strong>Tồn kho:</strong> <?php echo $product_details['stock_quantity']; ?></p>
                                            <p><strong>Ngày tạo:</strong> <?php echo date('d/m/Y H:i', strtotime($product_details['created_at'])); ?></p>
                                            <p><strong>Cập nhật cuối:</strong> <?php echo date('d/m/Y H:i', strtotime($product_details['updated_at'])); ?></p>
                                            </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    </div>
            </div>
            <?php include '../partials/footer.php'; ?>
            </div>
        </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include '../logout_modal.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/jquery.easing@1.4.1/jquery.easing.min.js"></script>

    <script src="../../admin/public/js/sb-admin-2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Khởi tạo tooltip nếu bạn muốn dùng trên trang này
             $('[data-toggle="tooltip"]').tooltip();
        });
    </script>

</body>
</html>