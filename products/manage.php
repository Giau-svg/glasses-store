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

// Xác định hành động (add, edit, delete)
$action = isset($_GET['action']) ? $_GET['action'] : 'add';
$success = '';
$error = '';

// Lấy danh sách danh mục và thương hiệu
$categories = null;
$brands = null;

if ($action == 'add' || $action == 'edit') {
    $stmt_categories = $connect->prepare("SELECT category_id, category_name FROM categories");
    if (!$stmt_categories) {
        $error = "Lỗi chuẩn bị truy vấn danh mục: " . $connect->error;
    } else {
        $stmt_categories->execute();
        $categories = $stmt_categories->get_result();
        $stmt_categories->close();
    }

    $stmt_brands = $connect->prepare("SELECT brand_id, brand_name FROM brands");
    if (!$stmt_brands) {
        $error = "Lỗi chuẩn bị truy vấn thương hiệu: " . $connect->error;
    } else {
        $stmt_brands->execute();
        $brands = $stmt_brands->get_result();
        $stmt_brands->close();
    }
}

if ($action == 'add') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $product_name = $_POST['product_name'];
        $category_id = (int)$_POST['category_id'];
        $brand_id = (int)$_POST['brand_id'];
        $price = (float)$_POST['price'];
        $cost_price = (float)$_POST['cost_price'];
        $stock_quantity = (int)$_POST['stock_quantity'];

        // Kiểm tra category_id
        $stmt_check_category = $connect->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $stmt_check_category->bind_param("i", $category_id);
        $stmt_check_category->execute();
        $category_exists = $stmt_check_category->get_result()->num_rows > 0;
        $stmt_check_category->close();

        if (!$category_exists) {
            $error = "Lỗi: Danh mục không tồn tại.";
        }

        // Kiểm tra brand_id
        $stmt_check_brand = $connect->prepare("SELECT brand_id FROM brands WHERE brand_id = ?");
        $stmt_check_brand->bind_param("i", $brand_id);
        $stmt_check_brand->execute();
        $brand_exists = $stmt_check_brand->get_result()->num_rows > 0;
        $stmt_check_brand->close();

        if (!$brand_exists) {
            $error = "Lỗi: Thương hiệu không tồn tại.";
        }

        if (empty($error)) {
            $stmt = $connect->prepare("INSERT INTO products (product_name, category_id, brand_id, price, cost_price, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                $error = "Lỗi chuẩn bị truy vấn thêm sản phẩm: " . $connect->error;
            } else {
                $stmt->bind_param("siiddi", $product_name, $category_id, $brand_id, $price, $cost_price, $stock_quantity);
                
                if ($stmt->execute()) {
                    $success = "Thêm sản phẩm thành công!";
                } else {
                    $error = "Lỗi thực thi truy vấn thêm sản phẩm: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
} elseif ($action == 'edit') {
    if (!isset($_GET['id'])) {
        header('Location: index.php');
        exit();
    }
    $product_id = (int)$_GET['id'];
    $stmt = $connect->prepare("SELECT * FROM products WHERE product_id = ?");
    if (!$stmt) {
        $error = "Lỗi chuẩn bị truy vấn: " . $connect->error;
    } else {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            header('Location: index.php');
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $product_name = $_POST['product_name'];
        $category_id = (int)$_POST['category_id'];
        $brand_id = (int)$_POST['brand_id'];
        $price = (float)$_POST['price'];
        $cost_price = (float)$_POST['cost_price'];
        $stock_quantity = (int)$_POST['stock_quantity'];

        $stmt_check_category = $connect->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $stmt_check_category->bind_param("i", $category_id);
        $stmt_check_category->execute();
        $category_exists = $stmt_check_category->get_result()->num_rows > 0;
        $stmt_check_category->close();

        if (!$category_exists) {
            $error = "Lỗi: Danh mục không tồn tại.";
        }

        $stmt_check_brand = $connect->prepare("SELECT brand_id FROM brands WHERE brand_id = ?");
        $stmt_check_brand->bind_param("i", $brand_id);
        $stmt_check_brand->execute();
        $brand_exists = $stmt_check_brand->get_result()->num_rows > 0;
        $stmt_check_brand->close();

        if (!$brand_exists) {
            $error = "Lỗi: Thương hiệu không tồn tại.";
        }

        if (empty($error)) {
            $stmt = $connect->prepare("UPDATE products SET product_name = ?, category_id = ?, brand_id = ?, price = ?, cost_price = ?, stock_quantity = ? WHERE product_id = ?");
            if (!$stmt) {
                $error = "Lỗi chuẩn bị truy vấn: " . $connect->error;
            } else {
                $stmt->bind_param("siiddii", $product_name, $category_id, $brand_id, $price, $cost_price, $stock_quantity, $product_id);
                
                if ($stmt->execute()) {
                    $success = "Cập nhật sản phẩm thành công!";
                } else {
                    $error = "Lỗi: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
} elseif ($action == 'delete') {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $error = "Lỗi: ID sản phẩm không hợp lệ.";
    } else {
        $product_id = (int)$_GET['id'];
        $stmt = $connect->prepare("DELETE FROM products WHERE product_id = ?");
        if (!$stmt) {
            $error = "Lỗi chuẩn bị truy vấn xóa sản phẩm: " . $connect->error;
        } else {
            $stmt->bind_param("i", $product_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = "Xóa sản phẩm thành công! Bạn sẽ được chuyển về trang danh sách sau 2 giây.";
                    header("Refresh: 2; url=index.php");
                } else {
                    $error = "Lỗi: Sản phẩm không tồn tại.";
                }
            } else {
                $error = "Lỗi thực thi truy vấn xóa sản phẩm: " . $stmt->error;
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
    <title><?php echo $action == 'add' ? 'Thêm' : ($action == 'edit' ? 'Sửa' : 'Xóa'); ?> Sản Phẩm - EYEGLASSES</title>
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
                    <h1 class="h3 mb-4 text-gray-800"><?php echo $action == 'add' ? 'Thêm' : ($action == 'edit' ? 'Sửa' : 'Xóa'); ?> Sản Phẩm<?php if ($action == 'edit' && isset($product)) echo ' #' . $product['product_id']; ?><?php if ($action == 'delete') echo ' #' . (isset($_GET['id']) ? $_GET['id'] : ''); ?></h1>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <?php if ($action == 'add' && $categories && $brands): ?>
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label>Tên Sản Phẩm</label>
                                        <input type="text" class="form-control" name="product_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Danh Mục</label>
                                        <select class="form-control" name="category_id" required>
                                            <option value="">Chọn danh mục</option>
                                            <?php while ($category = $categories->fetch_assoc()): ?>
                                                <option value="<?php echo $category['category_id']; ?>">
                                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Thương Hiệu</label>
                                        <select class="form-control" name="brand_id" required>
                                            <option value="">Chọn thương hiệu</option>
                                            <?php while ($brand = $brands->fetch_assoc()): ?>
                                                <option value="<?php echo $brand['brand_id']; ?>">
                                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Giá Bán</label>
                                        <input type="number" class="form-control" name="price" step="0.01" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Giá Nhập</label>
                                        <input type="number" class="form-control" name="cost_price" step="0.01" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Số Lượng Tồn Kho</label>
                                        <input type="number" class="form-control" name="stock_quantity" value="0" required>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Lưu Sản Phẩm</button>
                                        <a href="index.php" class="btn btn-secondary">Hủy</a>
                                    </div>
                                </form>
                            <?php elseif ($action == 'edit' && isset($product)): ?>
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label>Tên Sản Phẩm</label>
                                        <input type="text" class="form-control" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Danh Mục</label>
                                        <select class="form-control" name="category_id" required>
                                            <option value="">Chọn danh mục</option>
                                            <?php 
                                            $categories->data_seek(0);
                                            while ($category = $categories->fetch_assoc()): ?>
                                                <option value="<?php echo $category['category_id']; ?>" <?php if ($category['category_id'] == $product['category_id']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Thương Hiệu</label>
                                        <select class="form-control" name="brand_id" required>
                                            <option value="">Chọn thương hiệu</option>
                                            <?php 
                                            $brands->data_seek(0);
                                            while ($brand = $brands->fetch_assoc()): ?>
                                                <option value="<?php echo $brand['brand_id']; ?>" <?php if ($brand['brand_id'] == $product['brand_id']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Giá Bán</label>
                                        <input type="number" class="form-control" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Giá Nhập</label>
                                        <input type="number" class="form-control" name="cost_price" step="0.01" value="<?php echo $product['cost_price']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Số Lượng Tồn Kho</label>
                                        <input type="number" class="form-control" name="stock_quantity" value="<?php echo $product['stock_quantity']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Cập Nhật</button>
                                        <a href="index.php" class="btn btn-secondary">Hủy</a>
                                    </div>
                                </form>
                            <?php elseif ($action == 'delete'): ?>
                                <p>Nếu không có lỗi, bạn sẽ được chuyển về trang danh sách sản phẩm sau 2 giây.</p>
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
</body>
</html>