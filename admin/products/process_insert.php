<?php
require '../check_admin_login.php';
require '../root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra nếu form đã được gửi đi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $product_name = trim(mysqli_real_escape_string($connect, $_POST['product_name'] ?? ''));
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $cost_price = isset($_POST['cost_price']) ? (float)$_POST['cost_price'] : $price * 0.7; // Mặc định là 70% giá bán nếu không có
    $description = isset($_POST['description']) ? trim(mysqli_real_escape_string($connect, $_POST['description'])) : '';
    $stock_quantity = isset($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : 0;
    $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $manufacturer_id = isset($_POST['manufacturer_id']) && !empty($_POST['manufacturer_id']) ? (int)$_POST['manufacturer_id'] : null;
    $brand_id = isset($_POST['brand_id']) && !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    
    // Kiểm tra các trường bắt buộc
    if (empty($product_name)) {
        header('Location: form_insert.php?error=Tên sản phẩm không được để trống');
        exit;
    }

    if ($price <= 0) {
        header('Location: form_insert.php?error=Giá sản phẩm phải lớn hơn 0');
        exit;
    }

    if ($stock_quantity < 0) {
        header('Location: form_insert.php?error=Số lượng sản phẩm không thể âm');
        exit;
    }

    // Tạo thư mục uploads nếu chưa tồn tại
    $upload_dir = "uploads/";
    $target_dir = $upload_dir;
    
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            header('Location: form_insert.php?error=Không thể tạo thư mục uploads. Vui lòng kiểm tra quyền của thư mục.');
            exit;
        }
    }
    
    // Xử lý upload ảnh (nếu có)
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        // Kiểm tra loại file
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            header('Location: form_insert.php?error=Chỉ chấp nhận file ảnh có định dạng JPEG, PNG, GIF.');
            exit;
        }
        
        // Kiểm tra kích thước file
        if ($_FILES['image']['size'] > $max_size) {
            header('Location: form_insert.php?error=Kích thước file không được vượt quá 2MB');
            exit;
        }
        
        // Tạo tên file duy nhất
        $image = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image;
        
        // Upload file
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $php_error = error_get_last();
            header('Location: form_insert.php?error=Có lỗi xảy ra khi upload file: ' . ($php_error ? $php_error['message'] : 'Unknown error'));
            exit;
        }
    } else {
        // Nếu không có ảnh hoặc có lỗi khi upload
        if ($_FILES['image']['error'] != UPLOAD_ERR_OK && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
            $error_message = 'Có lỗi khi upload file: ';
            switch ($_FILES['image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $error_message .= 'File vượt quá kích thước cho phép trong php.ini.';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $error_message .= 'File vượt quá kích thước cho phép trong form.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_message .= 'File chỉ được tải lên một phần.';
                    break;
                default:
                    $error_message .= 'Lỗi không xác định.';
            }
            header('Location: form_insert.php?error=' . urlencode($error_message));
            exit;
        }
    }
    
    // Chuẩn bị câu truy vấn SQL
    // Kiểm tra NULL cho các trường không bắt buộc
    $fields = "product_name, price, cost_price, stock_quantity";
    $values = "?, ?, ?, ?";
    $types = "sddi";
    $params = array($product_name, $price, $cost_price, $stock_quantity);
    
    if (!empty($description)) {
        $fields .= ", description";
        $values .= ", ?";
        $types .= "s";
        $params[] = $description;
    }
    
    if (!is_null($category_id)) {
        $fields .= ", category_id";
        $values .= ", ?";
        $types .= "i";
        $params[] = $category_id;
    }
    
    if (!is_null($manufacturer_id)) {
        $fields .= ", manufacturer_id";
        $values .= ", ?";
        $types .= "i";
        $params[] = $manufacturer_id;
    }
    
    if (!is_null($brand_id)) {
        $fields .= ", brand_id";
        $values .= ", ?";
        $types .= "i";
        $params[] = $brand_id;
    }
    
    if (!is_null($image)) {
        $fields .= ", image_path";
        $values .= ", ?";
        $types .= "s";
        $params[] = $image;
    }
    
    // Thêm sản phẩm vào CSDL
    $sql = "INSERT INTO products ($fields) VALUES ($values)";
    
    $stmt = mysqli_prepare($connect, $sql);
    if (!$stmt) {
        header('Location: form_insert.php?error=Lỗi chuẩn bị câu truy vấn: ' . mysqli_error($connect));
        exit;
    }
    
    // Gắn các tham số động
    $bind_params = array(&$types);
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    
    call_user_func_array(array($stmt, 'bind_param'), $bind_params);
    
    // Thực thi câu truy vấn
    if (mysqli_stmt_execute($stmt)) {
        // Thành công
        header('Location: index.php?success=Đã thêm sản phẩm thành công');
        exit;
    } else {
        // Lỗi
        header('Location: form_insert.php?error=Lỗi khi thêm sản phẩm: ' . mysqli_stmt_error($stmt));
        exit;
    }
    
    mysqli_stmt_close($stmt);
} else {
    // Nếu không phải là POST request, chuyển hướng về trang form
    header('Location: form_insert.php');
    exit;
}
?>
