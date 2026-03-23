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
mysqli_set_charset($connect, "utf8");

// Set active page
$active_page = 'stock_receipts';
$page_title = 'Quản Lý Phiếu Nhập Kho';

// Xác định hành động (add, edit, delete)
$action = isset($_GET['action']) ? $_GET['action'] : 'add';
$success = '';
$error = '';

// Kiểm tra user_id của người dùng hiện tại (dùng làm inventory_manager_id)
$inventory_manager_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($inventory_manager_id == 0) {
    $error = "Lỗi: Không tìm thấy thông tin người dùng. Vui lòng đăng nhập lại.";
} else {
    // Kiểm tra xem inventory_manager_id có tồn tại trong bảng users không
    $stmt_check_user = $connect->prepare("SELECT user_id FROM users WHERE user_id = ?");
    if (!$stmt_check_user) {
        $error = "Lỗi chuẩn bị truy vấn kiểm tra user: " . $connect->error;
    } else {
        $stmt_check_user->bind_param("i", $inventory_manager_id);
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
}

// Lấy danh sách nhà cung cấp và sản phẩm
$suppliers = null;
$products = null;

if ($action == 'add' && empty($error)) {
    $stmt_suppliers = $connect->prepare("SELECT supplier_id, supplier_name FROM suppliers");
    if (!$stmt_suppliers) {
        $error = "Lỗi chuẩn bị truy vấn nhà cung cấp: " . $connect->error;
    } else {
        $stmt_suppliers->execute();
        $suppliers = $stmt_suppliers->get_result();
        $stmt_suppliers->close();
    }

    $stmt_products = $connect->prepare("SELECT product_id, product_name, cost_price FROM products WHERE brand_id IN (SELECT brand_id FROM brands)");
    if (!$stmt_products) {
        $error = "Lỗi chuẩn bị truy vấn sản phẩm: " . $connect->error;
    } else {
        $stmt_products->execute();
        $products = $stmt_products->get_result();
        $stmt_products->close();
    }
}

if ($action == 'add' && empty($error)) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $supplier_id = (int)$_POST['supplier_id'];
        $receipt_date = $_POST['receipt_date'];
        $products = $_POST['products'];
        $quantities = $_POST['quantities'];
        $prices = $_POST['prices'];
        $notes = isset($_POST['notes']) ? $_POST['notes'] : '';

        // Kiểm tra supplier_id
        $stmt_check_supplier = $connect->prepare("SELECT supplier_id FROM suppliers WHERE supplier_id = ?");
        if (!$stmt_check_supplier) {
            $error = "Lỗi chuẩn bị truy vấn kiểm tra nhà cung cấp: " . $connect->error;
        } else {
            $stmt_check_supplier->bind_param("i", $supplier_id);
            if (!$stmt_check_supplier->execute()) {
                $error = "Lỗi thực thi truy vấn kiểm tra nhà cung cấp: " . $stmt_check_supplier->error;
            } else {
                $supplier_exists = $stmt_check_supplier->get_result()->num_rows > 0;
                if (!$supplier_exists) {
                    $error = "Lỗi: Nhà cung cấp không tồn tại.";
                }
                $stmt_check_supplier->close();
            }
        }

        if (empty($error)) {
            $total_amount = 0;
            for ($i = 0; $i < count($products); $i++) {
                $total_amount += $quantities[$i] * $prices[$i];
            }

            $stmt = $connect->prepare("INSERT INTO stock_receipts (inventory_manager_id, supplier_id, total_amount, receipt_date, notes) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                $error = "Lỗi chuẩn bị truy vấn thêm phiếu nhập: " . $connect->error;
            } else {
                $stmt->bind_param("iids", $inventory_manager_id, $supplier_id, $total_amount, $receipt_date);
                
                if ($stmt->execute()) {
                    $receipt_id = $stmt->insert_id;
                    
                    $stmt_detail = $connect->prepare("INSERT INTO stock_receipt_details (receipt_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                    if (!$stmt_detail) {
                        $error = "Lỗi chuẩn bị truy vấn chi tiết phiếu nhập: " . $connect->error;
                    } else {
                        for ($i = 0; $i < count($products); $i++) {
                            // Kiểm tra product_id có brand_id hợp lệ không
                            $product_id = (int)$products[$i];
                            $stmt_check_product = $connect->prepare("SELECT product_id FROM products WHERE product_id = ? AND brand_id IN (SELECT brand_id FROM brands)");
                            $stmt_check_product->bind_param("i", $product_id);
                            $stmt_check_product->execute();
                            $product_exists = $stmt_check_product->get_result()->num_rows > 0;
                            $stmt_check_product->close();

                            if (!$product_exists) {
                                $error = "Lỗi: Sản phẩm có ID $product_id không hợp lệ (brand_id không tồn tại).";
                                break;
                            }

                            $subtotal = $quantities[$i] * $prices[$i];
                            $stmt_detail->bind_param("iiidd", $receipt_id, $products[$i], $quantities[$i], $prices[$i], $subtotal);
                            if (!$stmt_detail->execute()) {
                                $error = "Lỗi thực thi truy vấn chi tiết phiếu nhập: " . $stmt_detail->error;
                                break;
                            }
                            
                            $stmt_update = $connect->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
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
                        $success = "Thêm phiếu nhập thành công!";
                    }
                } else {
                    $error = "Lỗi thực thi truy vấn thêm phiếu nhập: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
} elseif ($action == 'edit' && empty($error)) {
    if (!isset($_GET['id'])) {
        header('Location: index.php');
        exit();
    }
    $receipt_id = (int)$_GET['id'];
    $stmt = $connect->prepare("SELECT sr.*, s.supplier_name FROM stock_receipts sr JOIN suppliers s ON sr.supplier_id = s.supplier_id WHERE sr.receipt_id = ?");
    if (!$stmt) {
        $error = "Lỗi chuẩn bị truy vấn: " . $connect->error;
    } else {
        $stmt->bind_param("i", $receipt_id);
        $stmt->execute();
        $receipt = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$receipt) {
            header('Location: index.php');
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $receipt_date = $_POST['receipt_date'];
        $notes = isset($_POST['notes']) ? $_POST['notes'] : '';

        $stmt = $connect->prepare("UPDATE stock_receipts SET receipt_date = ?, notes = ? WHERE receipt_id = ?");
        if (!$stmt) {
            $error = "Lỗi chuẩn bị truy vấn: " . $connect->error;
        } else {
            $stmt->bind_param("ssi", $receipt_date, $notes, $receipt_id);
            
            if ($stmt->execute()) {
                $success = "Cập nhật phiếu nhập thành công!";
            } else {
                $error = "Lỗi: " . $stmt->error;
            }
            $stmt->close();
        }
    }
} elseif ($action == 'delete') {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $error = "Lỗi: ID phiếu nhập không hợp lệ.";
    } else {
        $receipt_id = (int)$_GET['id'];

        $stmt_details = $connect->prepare("SELECT product_id, quantity FROM stock_receipt_details WHERE receipt_id = ?");
        if (!$stmt_details) {
            $error = "Lỗi chuẩn bị truy vấn chi tiết phiếu nhập: " . $connect->error;
        } else {
            $stmt_details->bind_param("i", $receipt_id);
            if (!$stmt_details->execute()) {
                $error = "Lỗi thực thi truy vấn chi tiết phiếu nhập: " . $stmt_details->error;
            } else {
                $details = $stmt_details->get_result();

                while ($detail = $details->fetch_assoc()) {
                    $stmt_update = $connect->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                    if (!$stmt_update) {
                        $error = "Lỗi chuẩn bị truy vấn cập nhật tồn kho: " . $connect->error;
                        break;
                    }
                    $stmt_update->bind_param("ii", $detail['quantity'], $detail['product_id']);
                    if (!$stmt_update->execute()) {
                        $error = "Lỗi thực thi truy vấn cập nhật tồn kho: " . $stmt_update->error;
                        break;
                    }
                    $stmt_update->close();
                }
                $stmt_details->close();

                if (empty($error)) {
                    $stmt_delete_details = $connect->prepare("DELETE FROM stock_receipt_details WHERE receipt_id = ?");
                    if (!$stmt_delete_details) {
                        $error = "Lỗi chuẩn bị truy vấn xóa chi tiết phiếu nhập: " . $connect->error;
                    } else {
                        $stmt_delete_details->bind_param("i", $receipt_id);
                        if (!$stmt_delete_details->execute()) {
                            $error = "Lỗi thực thi truy vấn xóa chi tiết phiếu nhập: " . $stmt_delete_details->error;
                        }
                        $stmt_delete_details->close();
                    }

                    if (empty($error)) {
                        $stmt = $connect->prepare("DELETE FROM stock_receipts WHERE receipt_id = ?");
                        if (!$stmt) {
                            $error = "Lỗi chuẩn bị truy vấn xóa phiếu nhập: " . $connect->error;
                        } else {
                            $stmt->bind_param("i", $receipt_id);
                            if ($stmt->execute()) {
                                if ($stmt->affected_rows > 0) {
                                    $success = "Xóa phiếu nhập thành công! Bạn sẽ được chuyển về trang danh sách sau 2 giây.";
                                    header("Refresh: 2; url=index.php");
                                } else {
                                    $error = "Lỗi: Phiếu nhập không tồn tại.";
                                }
                            } else {
                                $error = "Lỗi thực thi truy vấn xóa phiếu nhập: " . $stmt->error;
                            }
                            $stmt->close();
                        }
                    }
                }
            }
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
    <title><?php echo $action == 'add' ? 'Thêm' : ($action == 'edit' ? 'Sửa' : 'Xóa'); ?> Phiếu Nhập Kho - EYEGLASSES</title>
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
                    <h1 class="h3 mb-4 text-gray-800"><?php echo $action == 'add' ? 'Thêm' : ($action == 'edit' ? 'Sửa' : 'Xóa'); ?> Phiếu Nhập Kho<?php if ($action == 'edit' && isset($receipt)) echo ' #' . $receipt['receipt_id']; ?><?php if ($action == 'delete') echo ' #' . (isset($_GET['id']) ? $_GET['id'] : ''); ?></h1>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <?php if ($action == 'add' && $suppliers && $products): ?>
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label>Nhà Cung Cấp</label>
                                        <select class="form-control" name="supplier_id" required>
                                            <option value="">Chọn nhà cung cấp</option>
                                            <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                                                <option value="<?php echo $supplier['supplier_id']; ?>">
                                                    <?php echo htmlspecialchars($supplier['supplier_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Ngày Nhập Kho</label>
                                        <input type="datetime-local" class="form-control" name="receipt_date" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Ghi Chú</label>
                                        <textarea class="form-control" name="notes" rows="4"></textarea>
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
                                                        <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['cost_price']; ?>">
                                                            <?php echo htmlspecialchars($product['product_name']); ?>
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
                                                <input type="number" class="form-control price" name="prices[]" step="0.01" required>
                                            </div>
                                            <div class="form-group col-md-2">
                                                <button type="button" class="btn btn-danger remove-product mt-4">Xóa</button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-success mb-3" id="add-product">Thêm Sản Phẩm</button>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Lưu Phiếu Nhập</button>
                                        <a href="index.php" class="btn btn-secondary">Hủy</a>
                                    </div>
                                </form>
                            <?php elseif ($action == 'edit' && isset($receipt)): ?>
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label>Nhà Cung Cấp</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($receipt['supplier_name']); ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Ngày Nhập Kho</label>
                                        <input type="datetime-local" class="form-control" name="receipt_date" value="<?php echo date('Y-m-d\TH:i', strtotime($receipt['receipt_date'])); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Ghi Chú</label>
                                        <textarea class="form-control" name="notes" rows="4"><?php echo htmlspecialchars($receipt['notes']); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Cập Nhật</button>
                                        <a href="index.php" class="btn btn-secondary">Hủy</a>
                                    </div>
                                </form>
                            <?php elseif ($action == 'delete'): ?>
                                <p>Nếu không có lỗi, bạn sẽ được chuyển về trang danh sách phiếu nhập kho sau 2 giây.</p>
                                <a href="index.php" class="btn btn-secondary">Quay lại ngay</a>
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
    <?php if ($action == 'add' && empty($error)): ?>
    <script>
        $(document).ready(function() {
            // Nút "Thêm Sản Phẩm"
            $('#add-product').click(function() {
                var productItem = $('.product-item:first').clone();
                productItem.find('input').val('');
                productItem.find('select').val('');
                $('#product-list').append(productItem);
            });

            // Nút "Xóa"
            $(document).on('click', '.remove-product', function() {
                var productItems = $('#product-list .product-item');
                if (productItems.length > 1) {
                    $(this).closest('.product-item').remove();
                } else {
                    alert("Phải có ít nhất 1 sản phẩm trong phiếu nhập.");
                }
            });

            // Cập nhật giá khi chọn sản phẩm
            $(document).on('change', '.product-select', function() {
                var price = $(this).find('option:selected').data('price');
                $(this).closest('.product-item').find('.price').val(price);
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>