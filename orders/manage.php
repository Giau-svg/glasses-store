<?php
require '../busmanage/check_business_manager_login.php';
require '../busmanage/root.php';

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra kết nối database
if (!$connect) {
    die("Lỗi kết nối database: " . mysqli_connect_error());
}

// Xác định hành động (add, edit)
$action = isset($_GET['action']) ? $_GET['action'] : 'add';
$success = '';
$error = '';

if ($action == 'add') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $shipping_name = $_POST['shipping_name'];
        $shipping_phone = $_POST['shipping_phone'];
        $shipping_address = $_POST['shipping_address'];
        $order_date = $_POST['order_date'];
        $order_status = $_POST['order_status'];
        $products = $_POST['products'];
        $quantities = $_POST['quantities'];
        // Sửa: Lấy user_id từ $_SESSION['user_id']
        $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        if ($user_id == 0) {
            $error = "Lỗi: Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.";
        } else {
            // Kiểm tra user_id có tồn tại trong bảng users không
            $stmt_check_user = $connect->prepare("SELECT user_id FROM users WHERE user_id = ?");
            if (!$stmt_check_user) {
                $error = "Lỗi chuẩn bị truy vấn kiểm tra user: " . $connect->error;
            } else {
                $stmt_check_user->bind_param("i", $user_id);
                if (!$stmt_check_user->execute()) {
                    $error = "Lỗi thực thi truy vấn kiểm tra user: " . $stmt_check_user->error;
                } else {
                    $user_exists = $stmt_check_user->get_result()->num_rows > 0;
                    if (!$user_exists) {
                        $error = "Lỗi: Người dùng không tồn tại trong bảng users.";
                    }
                    $stmt_check_user->close();
                }
            }

            if (empty($error)) {
                $total_amount = 0;
                // Tính tổng tiền dựa trên giá từ bảng products và kiểm tra tồn kho
                for ($i = 0; $i < count($products); $i++) {
                    $stmt_price = $connect->prepare("SELECT price, stock_quantity FROM products WHERE product_id = ?");
                    if (!$stmt_price) {
                        $error = "Lỗi chuẩn bị truy vấn giá sản phẩm: " . $connect->error;
                        break;
                    }
                    $stmt_price->bind_param("i", $products[$i]);
                    if (!$stmt_price->execute()) {
                        $error = "Lỗi thực thi truy vấn giá sản phẩm: " . $stmt_price->error;
                        break;
                    }
                    $price_result = $stmt_price->get_result()->fetch_assoc();
                    $price = $price_result['price'] ?? 0;
                    $stock = $price_result['stock_quantity'] ?? 0;
                    $stmt_price->close();

                    if ($quantities[$i] > $stock) {
                        $error = "Lỗi: Sản phẩm có ID " . $products[$i] . " không đủ số lượng trong kho (còn: " . $stock . ").";
                        break;
                    }

                    $total_amount += $quantities[$i] * $price;
                }

                if (empty($error)) {
                    // Thêm user_id vào câu lệnh INSERT
                    $stmt = $connect->prepare("INSERT INTO orders (user_id, shipping_name, shipping_phone, shipping_address, total_amount, order_status, order_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    if (!$stmt) {
                        $error = "Lỗi chuẩn bị truy vấn: " . $connect->error;
                    } else {
                        $stmt->bind_param("isssdss", $user_id, $shipping_name, $shipping_phone, $shipping_address, $total_amount, $order_status, $order_date);
                        
                        if ($stmt->execute()) {
                            $order_id = $stmt->insert_id;
                            
                            // Thêm chi tiết đơn hàng
                            $stmt_detail = $connect->prepare("INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                            if (!$stmt_detail) {
                                $error = "Lỗi chuẩn bị truy vấn chi tiết đơn hàng: " . $connect->error;
                            } else {
                                for ($i = 0; $i < count($products); $i++) {
                                    $stmt_price = $connect->prepare("SELECT price FROM products WHERE product_id = ?");
                                    if (!$stmt_price) {
                                        $error = "Lỗi chuẩn bị truy vấn giá sản phẩm: " . $connect->error;
                                        break;
                                    }
                                    $stmt_price->bind_param("i", $products[$i]);
                                    if (!$stmt_price->execute()) {
                                        $error = "Lỗi thực thi truy vấn giá sản phẩm: " . $stmt_price->error;
                                        break;
                                    }
                                    $price_result = $stmt_price->get_result()->fetch_assoc();
                                    $unit_price = $price_result['price'] ?? 0;
                                    $stmt_price->close();

                                    $subtotal = $quantities[$i] * $unit_price;
                                    $stmt_detail->bind_param("iiidd", $order_id, $products[$i], $quantities[$i], $unit_price, $subtotal);
                                    if (!$stmt_detail->execute()) {
                                        $error = "Lỗi thực thi truy vấn chi tiết đơn hàng: " . $stmt_detail->error;
                                        break;
                                    }
                                    
                                    // Cập nhật số lượng tồn kho
                                    $stmt_update = $connect->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                                    if (!$stmt_update) {
                                        $error = "Lỗi chuẩn bị truy vấn cập nhật tồn kho: " . $connect->error;
                                        break;
                                    }
                                    $stmt_update->bind_param("ii", $quantities[$i], $products[$i]);
                                    if (!$stmt_update->execute()) {
                                        $error = "Lỗi thực thi truy vấn cập nhật tồn kho: " . $stmt_update->error;
                                        break;
                                    }
                                    $stmt_update->close();
                                }
                                $stmt_detail->close();
                            }
                            if (empty($error)) {
                                $success = "Thêm đơn hàng thành công!";
                            }
                        } else {
                            $error = "Lỗi: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
    $stmt_products = $connect->prepare("SELECT product_id, product_name, stock_quantity, price FROM products WHERE stock_quantity > 0");
    if (!$stmt_products) {
        $error = "Lỗi chuẩn bị truy vấn: " . $connect->error;
    } else {
        $stmt_products->execute();
        $products = $stmt_products->get_result();
        $stmt_products->close();
    }
} elseif ($action == 'edit') {
    if (!isset($_GET['id'])) {
        header('Location: index.php');
        exitZEN();
    }
    $order_id = (int)$_GET['id'];
    $stmt = $connect->prepare("SELECT * FROM orders WHERE order_id = ?");
    if (!$stmt) {
        $error = "Lỗi chuẩn bị truy vấn: " . $connect->error;
    } else {
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            header('Location: index.php');
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $shipping_name = $_POST['shipping_name'];
        $shipping_phone = $_POST['shipping_phone'];
        $shipping_address = $_POST['shipping_address'];
        $order_date = $_POST['order_date'];
        $order_status = $_POST['order_status'];

        $stmt = $connect->prepare("UPDATE orders SET shipping_name = ?, shipping_phone = ?, shipping_address = ?, order_date = ?, order_status = ? WHERE order_id = ?");
        if (!$stmt) {
            $error = "Lỗi chuẩn bị truy vấn: " . $connect->error;
        } else {
            $stmt->bind_param("sssssi", $shipping_name, $shipping_phone, $shipping_address, $order_date, $order_status, $order_id);
            
            if ($stmt->execute()) {
                $success = "Cập nhật đơn hàng thành công!";
            } else {
                $error = "Lỗi: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $action == 'add' ? 'Thêm' : 'Sửa'; ?> Đơn Hàng - EYEGLASSES</title>
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include '../partials/busmanage_sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include '../partials/busmanage_topbar.php'; ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800"><?php echo $action == 'add' ? 'Thêm' : 'Sửa'; ?> Đơn Hàng<?php if ($action == 'edit' && isset($order)) echo ' #' . $order['order_id']; ?></h1>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <?php if ($action == 'add'): ?>
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label>Tên Khách Hàng</label>
                                        <input type="text" class="form-control" name="shipping_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Số Điện Thoại</label>
                                        <input type="text" class="form-control" name="shipping_phone" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Địa Chỉ</label>
                                        <textarea class="form-control" name="shipping_address" required></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Ngày Đặt Hàng</label>
                                        <input type="datetime-local" class="form-control" name="order_date" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Trạng Thái</label>
                                        <select class="form-control" name="order_status" required>
                                            <option value="pending">Chờ xử lý</option>
                                            <option value="processing">Đang xử lý</option>
                                            <option value="delivered">Hoàn thành</option>
                                            <option value="cancelled">Đã hủy</option>
                                        </select>
                                    </div>
                                    <div id="product-list">
                                        <div class="form-row product-item">
                                            <div class="form-group col-md-4">
                                                <label>Sản Phẩm</label>
                                                <select class="form-control product-select" name="products[]" required>
                                                    <option value="">Chọn sản phẩm</option>
                                                    <?php 
                                                    $products->data_seek(0); // Đặt con trỏ về đầu để tái sử dụng
                                                    while ($product = $products->fetch_assoc()): ?>
                                                        <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['price']; ?>" data-stock="<?php echo $product['stock_quantity']; ?>">
                                                            <?php echo htmlspecialchars($product['product_name']); ?> (Tồn: <?php echo $product['stock_quantity']; ?>)
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Số Lượng</label>
                                                <input type="number" class="form-control quantity" name="quantities[]" min="1" required>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Đơn Giá</label>
                                                <input type="number" class="form-control price" name="prices[]" readonly>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <button type="button" class="btn btn-danger remove-product mt-4">Xóa</button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-success mb-3" id="add-product">Thêm Sản Phẩm</button>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Lưu Đơn Hàng</button>
                                        <a href="index.php" class="btn btn-secondary">Hủy</a>
                                    </div>
                                </form>
                            <?php elseif ($action == 'edit' && isset($order)): ?>
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label>Tên Khách Hàng</label>
                                        <input type="text" class="form-control" name="shipping_name" value="<?php echo htmlspecialchars($order['shipping_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Số Điện Thoại</label>
                                        <input type="text" class="form-control" name="shipping_phone" value="<?php echo htmlspecialchars($order['shipping_phone']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Địa Chỉ</label>
                                        <textarea class="form-control" name="shipping_address" required><?php echo htmlspecialchars($order['shipping_address']); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Ngày Đặt Hàng</label>
                                        <input type="datetime-local" class="form-control" name="order_date" value="<?php echo date('Y-m-d\TH:i', strtotime($order['order_date'])); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Trạng Thái</label>
                                        <select class="form-control" name="order_status" required>
                                            <option value="pending" <?php if ($order['order_status'] == 'pending') echo 'selected'; ?>>Chờ xử lý</option>
                                            <option value="processing" <?php if ($order['order_status'] == 'processing') echo 'selected'; ?>>Đang xử lý</option>
                                            <option value="delivered" <?php if ($order['order_status'] == 'delivered') echo 'selected'; ?>>Hoàn thành</option>
                                            <option value="cancelled" <?php if ($order['order_status'] == 'cancelled') echo 'selected'; ?>>Đã hủy</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Cập Nhật</button>
                                        <a href="index.php" class="btn btn-secondary">Hủy</a>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright © EYEGLASSES 2025</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <script src="../public/vendor/jquery/jquery.min.js"></script>
    <script src="../public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.easing@1.4.1/jquery.easing.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>
    <?php if ($action == 'add'): ?>
    <script>
        $(document).ready(function() {
            $('#add-product').click(function() {
                var productItem = $('.product-item:first').clone();
                productItem.find('input').val('');
                productItem.find('select').val('');
                $('#product-list').append(productItem);
            });

            $(document).on('click', '.remove-product', function() {
                if ($('.product-item').length > 1) {
                    $(this).closest('.product-item').remove();
                } else {
                    alert("Phải có ít nhất 1 sản phẩm trong đơn hàng.");
                }
            });

            $(document).on('change', '.product-select', function() {
                var price = $(this).find('option:selected').data('price');
                $(this).closest('.product-item').find('.price').val(price);
            });

            $(document).on('input', '.quantity', function() {
                var stock = $(this).closest('.product-item').find('.product-select option:selected').data('stock');
                var quantity = parseInt($(this).val());
                if (quantity > stock) {
                    alert('Số lượng vượt quá tồn kho (còn: ' + stock + ').');
                    $(this).val(stock);
                }
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>