<?php
require_once '../check_inventory_login.php';
require_once '../../admin/root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Thiết lập trang hiện tại
$active_page = 'products';
$page_title = 'Quản lý sản phẩm';

// Khởi tạo thông báo
$success_message = '';
$error_message = '';
$edit_product_data = null; // Biến để lưu dữ liệu sản phẩm cần sửa

// Kiểm tra kết nối database
if (!isset($connect)) {
    die("Không thể kết nối đến database");
}

// Lấy danh sách brands cho dropdown
$brand_sql = "SELECT * FROM brands ORDER BY brand_name ASC";
$brand_result = mysqli_query($connect, $brand_sql);
$brands = [];
if ($brand_result && mysqli_num_rows($brand_result) > 0) {
    while ($brand = mysqli_fetch_assoc($brand_result)) {
        $brands[] = $brand;
    }
    mysqli_free_result($brand_result);
}

// --- CODE XỬ LÝ THÊM SẢN PHẨM MỚI ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['update_product'])) {
    // Lấy dữ liệu từ form thêm sản phẩm
    $product_name = trim($_POST['product_name'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $brand_id = $_POST['brand_id'] ?? null;
    //$cost_price = $_POST['cost_price'] ?? null;
    $price = $_POST['price'] ?? null;
    $stock_quantity = $_POST['stock_quantity'] ?? null;

    // Kiểm tra dữ liệu đầu vào cơ bản
    if (empty($product_name) || empty($category_id) || empty($brand_id) || /*!isset($cost_price) || */ !isset($price) || !isset($stock_quantity)) {
        $error_message = "Vui lòng điền đầy đủ thông tin sản phẩm.";
    } else {
        // Làm sạch dữ liệu
        $product_name = htmlspecialchars($product_name);
        $category_id = (int)$category_id;
        $brand_id = (int)$brand_id;
        //$cost_price = (float)$cost_price;
        $price = (float)$price;
        $stock_quantity = (int)$stock_quantity;

        $image_path = ''; // Mặc định không có ảnh
        $upload_dir = '../../uploads/products/'; // Thư mục lưu ảnh sản phẩm (đảm bảo thư mục này tồn tại và có quyền ghi)

        // Xử lý upload hình ảnh nếu có
        if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] == UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['image_path']['tmp_name'];
            $file_name = $_FILES['image_path']['name'];
            $file_size = $_FILES['image_path']['size'];
            $file_type = $_FILES['image_path']['type'];
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Tạo tên file duy nhất để tránh trùng lặp
            $new_file_name = md5(time() . $file_name) . '.' . $file_extension;
            $upload_path = $upload_dir . $new_file_name;

            // Kiểm tra loại file và kích thước
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_extension, $allowed_extensions) && $file_size <= 5 * 1024 * 1024) { // Giới hạn 5MB
                // Di chuyển file đã upload
                if (move_uploaded_file($file_tmp_path, $upload_path)) {
                    $image_path = $new_file_name;
                } else {
                    $error_message = "Lỗi khi upload hình ảnh.";
                }
            } else {
                $error_message = "File hình ảnh không hợp lệ hoặc quá lớn (tối đa 5MB).";
            }
        }

        // Chuẩn bị câu truy vấn INSERT (sử dụng prepared statement để tránh SQL Injection)
        $insert_sql = "INSERT INTO products (product_name, category_id, brand_id, price, stock_quantity, image_path) VALUES (?, ?, ?, ?, ?, ?)";

        // Sử dụng prepared statement
        if ($stmt = mysqli_prepare($connect, $insert_sql)) {
            // Bind các biến vào statement
            mysqli_stmt_bind_param($stmt, "siidds", $product_name, $category_id, $brand_id, $price, $stock_quantity, $image_path);
            // s: string, i: integer, d: double/float

            // Thực thi statement
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Sản phẩm mới đã được thêm thành công!";
                // Có thể redirect sau khi thêm thành công để tránh gửi lại form
                header("Location: products.php?success=1");
                exit();
            } else {
                $error_message = "Lỗi khi thêm sản phẩm vào database: " . mysqli_stmt_error($stmt);
            }

            // Đóng statement
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "Lỗi chuẩn bị truy vấn: " . mysqli_error($connect);
        }
    }
}
// --- KẾT THÚC CODE XỬ LÝ THÊM SẢN PHẨM MỚI ---

// --- CODE XỬ LÝ XÓA SẢN PHẨM ---
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $product_id_to_delete = (int)$_GET['delete'];

    // Lấy đường dẫn ảnh để xóa file vật lý (tùy chọn)
    $get_image_sql = "SELECT image_path FROM products WHERE product_id = ?";
    if ($stmt = mysqli_prepare($connect, $get_image_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $product_id_to_delete);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $image_to_delete);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Xóa file ảnh nếu tồn tại và không phải ảnh mặc định
        if (!empty($image_to_delete) && $image_to_delete != 'no-image.jpg') {
            $image_filepath = '../../uploads/products/' . $image_to_delete;
            if (file_exists($image_filepath)) {
                unlink($image_filepath); // Xóa file
            }
        }
    }


    // Chuẩn bị câu truy vấn DELETE
    $delete_sql = "DELETE FROM products WHERE product_id = ?";

    if ($stmt = mysqli_prepare($connect, $delete_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $product_id_to_delete);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Sản phẩm đã được xóa thành công!";
             // Redirect về trang sản phẩm sau khi xóa
            header("Location: products.php?success=delete");
            exit();
        } else {
            $error_message = "Lỗi khi xóa sản phẩm: " . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Lỗi chuẩn bị truy vấn xóa: " . mysqli_error($connect);
    }
}
// --- KẾT THÚC CODE XỬ LÝ XÓA SẢN PHẨM ---

// --- CODE XỬ LÝ LẤY DỮ LIỆU SẢN PHẨM ĐỂ SỬA ---
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $product_id_to_edit = (int)$_GET['edit'];

    $edit_sql = "SELECT * FROM products WHERE product_id = ?";
    if ($stmt = mysqli_prepare($connect, $edit_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $product_id_to_edit);
        mysqli_stmt_execute($stmt);
        $result_edit = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result_edit) == 1) {
            $edit_product_data = mysqli_fetch_assoc($result_edit);
        } else {
            $error_message = "Không tìm thấy sản phẩm cần sửa.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Lỗi chuẩn bị truy vấn lấy dữ liệu sửa: " . mysqli_error($connect);
    }
}
// --- KẾT THÚC CODE XỬ LÝ LẤY DỮ LIỆU SẢN PHẨM ĐỂ SỬA ---

// --- CODE XỬ LÝ CẬP NHẬT SẢN PHẨM ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    // Lấy dữ liệu từ form sửa sản phẩm
    $product_id = (int)$_POST['product_id'];
    $product_name = trim($_POST['product_name'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $cost_price = $_POST['cost_price'] ?? null;
    $price = $_POST['price'] ?? null;
    $stock_quantity = $_POST['stock_quantity'] ?? null;
    $existing_image_path = $_POST['existing_image_path'] ?? ''; // Lấy đường dẫn ảnh cũ

    // Kiểm tra dữ liệu đầu vào cơ bản
     if (empty($product_name) || empty($category_id) || !isset($cost_price) || !isset($price) || !isset($stock_quantity)) {
        $error_message = "Vui lòng điền đầy đủ thông tin sản phẩm.";
    } else {
        // Làm sạch dữ liệu
        $product_name = htmlspecialchars($product_name);
        $category_id = (int)$category_id;
        $cost_price = (float)$cost_price;
        $price = (float)$price;
        $stock_quantity = (int)$stock_quantity;

        $image_path = $existing_image_path; // Mặc định giữ ảnh cũ
        $upload_dir = '../../uploads/products/';

        // Xử lý upload hình ảnh mới nếu có
        if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] == UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['image_path']['tmp_name'];
            $file_name = $_FILES['image_path']['name'];
            $file_size = $_FILES['image_path']['size'];
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $new_file_name = md5(time() . $file_name) . '.' . $file_extension;
            $upload_path = $upload_dir . $new_file_name;

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_extension, $allowed_extensions) && $file_size <= 5 * 1024 * 1024) {
                // Xóa ảnh cũ nếu không phải ảnh mặc định và upload ảnh mới thành công
                if (!empty($existing_image_path) && $existing_image_path != 'no-image.jpg') {
                     $old_image_filepath = $upload_dir . $existing_image_path;
                     if (file_exists($old_image_filepath)) {
                         unlink($old_image_filepath);
                     }
                 }
                if (move_uploaded_file($file_tmp_path, $upload_path)) {
                    $image_path = $new_file_name;
                } else {
                    $error_message = "Lỗi khi upload hình ảnh mới.";
                }
            } else {
                $error_message = "File hình ảnh không hợp lệ hoặc quá lớn (tối đa 5MB).";
            }
        }


        // Chuẩn bị câu truy vấn UPDATE
        $update_sql = "UPDATE products SET product_name = ?, category_id = ?, cost_price = ?, price = ?, stock_quantity = ?, image_path = ? WHERE product_id = ?";

        if ($stmt = mysqli_prepare($connect, $update_sql)) {
            mysqli_stmt_bind_param($stmt, "sidddsi", $product_name, $category_id, $cost_price, $price, $stock_quantity, $image_path, $product_id);

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Sản phẩm đã được cập nhật thành công!";
                 // Redirect về trang sản phẩm sau khi cập nhật
                header("Location: products.php?success=update");
                exit();
            } else {
                $error_message = "Lỗi khi cập nhật sản phẩm: " . mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);
        } else {
            $error_message = "Lỗi chuẩn bị truy vấn cập nhật: " . mysqli_error($connect);
        }
    }
}
// --- KẾT THÚC CODE XỬ LÝ CẬP NHẬT SẢN PHẨM ---


// Lấy danh sách sản phẩm với thông tin danh mục
$sql = "SELECT p.*, c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        ORDER BY p.product_name ASC";
$result = mysqli_query($connect, $sql);

if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($connect));
}

// Lấy danh sách loại sản phẩm cho dropdown trong modal
$category_sql = "SELECT * FROM categories ORDER BY category_name ASC";
$category_result = mysqli_query($connect, $category_sql);
$categories = [];
if ($category_result && mysqli_num_rows($category_result) > 0) {
    while ($category = mysqli_fetch_assoc($category_result)) {
        $categories[] = $category;
    }
    mysqli_free_result($category_result);
}

?>
<?php
// ... existing PHP code ...

// --- CODE XỬ LÝ TÌM KIẾM, LỌC VÀ SẮP XẾP ---
$search_term = trim($_GET['search'] ?? '');
$category_filter = $_GET['category_filter'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'product_name'; // Default sort by name
$sort_order = $_GET['sort_order'] ?? 'ASC'; // Default sort order ascending

$where_conditions = [];
$query_params = [];
$param_types = '';

// Search by product name
if (!empty($search_term)) {
    $where_conditions[] = "p.product_name LIKE ?";
    $query_params[] = "%$search_term%";
    $param_types .= 's';
}

// Filter by category
if (!empty($category_filter)) {
    $where_conditions[] = "p.category_id = ?";
    $query_params[] = (int)$category_filter;
    $param_types .= 'i';
}

// Build WHERE clause
$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Build ORDER BY clause
$allowed_sort_columns = ['product_name', 'category_name', 'price', 'cost_price', 'stock_quantity'];
$allowed_sort_order = ['ASC', 'DESC'];

$order_by_column = in_array($sort_by, $allowed_sort_columns) ? $sort_by : 'product_name';
$order_by_direction = in_array(strtoupper($sort_order), $allowed_sort_order) ? strtoupper($sort_order) : 'ASC';

// Adjust sort column if sorting by category name (requires using alias from JOIN)
if ($order_by_column === 'category_name') {
     $order_by_clause = "ORDER BY c.category_name " . $order_by_direction;
} elseif ($order_by_column === 'price') {
     $order_by_clause = "ORDER BY p.price " . $order_by_direction;
} elseif ($order_by_column === 'cost_price') {
     $order_by_clause = "ORDER BY p.cost_price " . $order_by_direction;
} elseif ($order_by_column === 'stock_quantity') {
     $order_by_clause = "ORDER BY p.stock_quantity " . $order_by_direction;
}
else { // Default or product_name
    $order_by_clause = "ORDER BY p.product_name " . $order_by_direction;
}


// --- KẾT THÚC CODE XỬ LÝ TÌM KIẾM, LỌC VÀ SẮP XẾP ---


// Lấy danh sách sản phẩm với thông tin danh mục (áp dụng lọc và sắp xếp)
$sql = "SELECT p.*, c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        $where_clause
        $order_by_clause"; // Áp dụng ORDER BY sau WHERE


// Use prepared statement for the main query if there are conditions
if (!empty($query_params)) {
    if ($stmt = mysqli_prepare($connect, $sql)) {
        mysqli_stmt_bind_param($stmt, $param_types, ...$query_params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        die("Lỗi chuẩn bị truy vấn chính: " . mysqli_error($connect));
    }
} else {
     // If no search or filter, execute without prepared statement (or still use prepared for consistency)
     // For simplicity and consistency, let's use prepared statement even without params, though it's less efficient
      if ($stmt = mysqli_prepare($connect, $sql)) {
        // No binding needed here
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
         die("Lỗi chuẩn bị truy vấn chính (không lọc): " . mysqli_error($connect));
    }
}


if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($connect));
}

// Lấy danh sách loại sản phẩm cho dropdown trong modal VÀ bộ lọc
$category_sql = "SELECT * FROM categories ORDER BY category_name ASC";
$category_result = mysqli_query($connect, $category_sql);
$categories = [];
if ($category_result && mysqli_num_rows($category_result) > 0) {
    while ($category = mysqli_fetch_assoc($category_result)) {
        $categories[] = $category;
    }
    // Don't free $category_result yet, it's used below for filter dropdown
}

// ... rest of the PHP code ...

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include '../partials/head.php'; ?>
    <style>
        .product-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
        }
    </style>
</head>
<body id="page-top">

    <div id="wrapper">

        <?php include 'inventory_sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                <?php include 'inventory_topbar.php'; ?>
                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
                    </div>
                    <?php if(!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Filter and Sort Card -->
                        <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"></h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="products.php" class="row align-items-end">
                                <div class="col-md-4 form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="search" name="search" 
                                               placeholder="Nhập tên sản phẩm..." value="<?php echo htmlspecialchars($search_term); ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 form-group">
                                    <select class="form-control" id="category_filter" name="category_filter">
                                        <option value="">-- Tất cả loại --</option>
                                        <?php
                                        // Use the $categories array fetched earlier
                                        foreach ($categories as $category) {
                                            $selected = ($category_filter == $category['category_id']) ? 'selected' : '';
                                            echo '<option value="' . $category['category_id'] . '" ' . $selected . '>' . htmlspecialchars($category['category_name']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12 text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search mr-1"></i>
                                    </button>
                                     <a href="products.php" class="btn btn-secondary ml-2">
                                        <i class="fas fa-sync-alt mr-1"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
    <h6 class="m-0 font-weight-bold text-primary">Danh sách sản phẩm</h6>
    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addProductModal">
         <i class="fas fa-plus fa-sm text-white-50"></i> Thêm
    </button>
</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>HÌNH ẢNH</th>
                                            <th>TÊN SẢN PHẨM</th>
                                            <th>LOẠI</th>
                                            <th>GIÁ BÁN</th>
                                            <th>TỒN KHO</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($product = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <?php if (!empty($product['image_path'])): ?>
                                                            <img src="../../uploads/products/<?php echo htmlspecialchars($product['image_path']); ?>"
                                                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                                                 class="product-image">
                                                        <?php else: ?>
                                                            <img src="../../uploads/products/no-image.jpg"
                                                                 alt="No Image"
                                                                 class="product-image">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                    <td><?php echo number_format($product['price'], 0, ',', '.'); ?> đ</td>
                                                    <td>
                                                        <?php
                                                        $stock_status = '';
                                                        $stock_class = '';
                                                        if ($product['stock_quantity'] <= 0) {
                                                            $stock_status = 'Hết hàng';
                                                            $stock_class = 'badge-danger';
                                                        } elseif ($product['stock_quantity'] <= 10) {
                                                            $stock_status = 'Sắp hết';
                                                            $stock_class = 'badge-warning';
                                                        } else {
                                                            $stock_status = 'Còn hàng';
                                                            $stock_class = 'badge-success';
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $stock_class; ?>">
                                                            <?php echo $product['stock_quantity']; ?> - <?php echo $stock_status; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="?edit=<?php echo $product['product_id']; ?>" class="btn btn-info btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?delete=<?php echo $product['product_id']; ?>" class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Không có sản phẩm nào</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; EYEGLASSES 2023</span>
                    </div>
                </div>
            </footer>
            </div>
        </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include '../logout_modal.php'; ?>

    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Thêm sản phẩm mới</h5>

                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="product_name">Tên sản phẩm:</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" required>
                        </div>
                        <div class="form-group">
                            <label for="category_id">Loại sản phẩm:</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value="">-- Chọn loại --</option>
                                <?php
                                foreach ($categories as $category) {
                                    echo '<option value="' . $category['category_id'] . '">' . htmlspecialchars($category['category_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="brand_id">Thương hiệu:</label>
                            <select class="form-control" id="brand_id" name="brand_id" required>
                                <option value="">-- Chọn thương hiệu --</option>
                                <?php
                                foreach ($brands as $brand) {
                                    echo '<option value="' . $brand['brand_id'] . '">' . htmlspecialchars($brand['brand_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="price">Giá bán:</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="stock_quantity">Tồn kho:</label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" required>
                        </div>
                        <div class="form-group">
                            <label for="image_path">Hình ảnh:</label>
                            <input type="file" class="form-control-file" id="image_path" name="image_path" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Sửa sản phẩm</h5>

                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="update_product" value="1">
                        <input type="hidden" id="edit_product_id" name="product_id">
                        <input type="hidden" id="existing_image_path" name="existing_image_path">
                        <div class="form-group">
                            <label for="edit_product_name">Tên sản phẩm:</label>
                            <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_category_id">Loại sản phẩm:</label>
                            <select class="form-control" id="edit_category_id" name="category_id" required>
                                <option value="">-- Chọn loại --</option>
                                <?php
                                foreach ($categories as $category) {
                                    echo '<option value="' . $category['category_id'] . '">' . htmlspecialchars($category['category_name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_price">Giá bán:</label>
                            <input type="number" class="form-control" id="edit_price" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_stock_quantity">Tồn kho:</label>
                            <input type="number" class="form-control" id="edit_stock_quantity" name="stock_quantity" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_image_path">Hình ảnh:</label>
                             <?php if (!empty($edit_product_data['image_path'])): ?>
                                <img src="../../uploads/products/<?php echo htmlspecialchars($edit_product_data['image_path']); ?>"
                                     alt="Current Image" class="product-image mb-2" id="current_edit_image">
                            <?php else: ?>
                                <img src="../../uploads/products/no-image.jpg"
                                     alt="No Image" class="product-image mb-2" id="current_edit_image">
                            <?php endif; ?>
                            <input type="file" class="form-control-file" id="edit_image_path" name="image_path" accept="image/*">
                             <small class="form-text text-muted">Chọn ảnh mới nếu muốn thay đổi.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Cập nhật sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/jquery.easing@1.4.1/jquery.easing.min.js"></script>

    <script src="../../admin/js/sb-admin-2.min.js"></script>

    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
            "pageLength": 3,
                "lengthChange": false, // Allow changing number of entries
                "searching": false, 
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json" // Vietnamese localization
                }
        });

        // Hiển thị modal sửa sản phẩm khi có dữ liệu sản phẩm cần sửa
        <?php if ($edit_product_data): ?>
            $('#editProductModal').modal('show');
             // Điền dữ liệu vào form sửa
            $('#edit_product_id').val('<?php echo $edit_product_data['product_id']; ?>');
            $('#edit_product_name').val('<?php echo htmlspecialchars($edit_product_data['product_name']); ?>');
            $('#edit_category_id').val('<?php echo $edit_product_data['category_id']; ?>');
            $('#edit_cost_price').val('<?php echo $edit_product_data['cost_price']; ?>');
            $('#edit_price').val('<?php echo $edit_product_data['price']; ?>');
            $('#edit_stock_quantity').val('<?php echo $edit_product_data['stock_quantity']; ?>');
            $('#existing_image_path').val('<?php echo htmlspecialchars($edit_product_data['image_path']); ?>');

             // Cập nhật ảnh hiện tại trong modal
            var currentImageUrl = '<?php echo !empty($edit_product_data['image_path']) ? "../../uploads/products/" . htmlspecialchars($edit_product_data['image_path']) : "../../uploads/products/no-image.jpg"; ?>';
            $('#current_edit_image').attr('src', currentImageUrl);


        <?php endif; ?>
    });

    function exportToExcel() {
        window.location.href = 'export_products.php';
    }
    </script>

</body>
</html>