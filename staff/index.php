<?php
// Thiết lập thông số session
ini_set('session.cookie_lifetime', 86400); // 24h
ini_set('session.gc_maxlifetime', 86400); // 24h

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Khởi tạo session
session_start();

// Xử lý clear session nếu có yêu cầu
if (isset($_GET['clear']) && $_GET['clear'] == 1) {
    // Xóa tất cả dữ liệu session staff
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'staff_') === 0) {
            unset($_SESSION[$key]);
        }
    }
    
    // Xóa cookie staff
    setcookie('staff_remember', '', time() - 3600, '/');
    
    // Chuyển hướng về trang đăng nhập
    header("Location: index.php?success=Đã xóa toàn bộ session và cookie staff");
    exit;
}

// Reset session count để tránh lỗi vòng lặp
if (!isset($_SESSION['staff_redirect_count'])) {
    $_SESSION['staff_redirect_count'] = 0;
}

// Nếu quá số lần redirect, reset lại tất cả session staff
if ($_SESSION['staff_redirect_count'] >= 3) {
    // Xóa các session của staff
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'staff_') === 0) {
            unset($_SESSION[$key]);
        }
    }
    $_SESSION['staff_redirect_count'] = 0;
    
    // Thông báo lỗi
    $error = "Đã xảy ra lỗi chuyển hướng. Vui lòng đăng nhập lại.";
} else {
    // Kiểm tra nếu đã đăng nhập 
    if (isset($_SESSION['staff_user_id']) && isset($_SESSION['staff_role'])) {
        // Tăng bộ đếm redirect
        $_SESSION['staff_redirect_count']++;
        
        // Lưu thông tin role hiện tại để debug
        $current_role = $_SESSION['staff_role'];
        
        // Chuyển hướng đến trang dashboard tương ứng với vai trò
        if ($_SESSION['staff_role'] == 'sales') {
            header("Location: sales/dashboard.php");
            exit;
        } else if ($_SESSION['staff_role'] == 'inventory') {
            header("Location: inventory/dashboard.php");
            exit;
        } else {
            header("Location: dashboard/index.php");
            exit;
        }
    }
}

// Reset bộ đếm khi hiển thị form đăng nhập
$_SESSION['staff_redirect_count'] = 0;

// Xử lý đăng nhập tự động bằng cookie
if (isset($_COOKIE['staff_remember']) && !empty($_COOKIE['staff_remember'])) {
    // Yêu cầu file kết nối database
    require_once '../admin/root.php';
    
    // Kiểm tra xem kết nối có thành công không
    if (!$connect) {
        $connection_error = "Không thể kết nối đến cơ sở dữ liệu. Lỗi: " . mysqli_connect_error();
    } else {
        $token = $_COOKIE['staff_remember'];
        
        // Kiểm tra token trong bảng users cho staff
        $sql = "SELECT * FROM users WHERE token = ? AND role_id IN (3, 4) LIMIT 1";
        $stmt = mysqli_prepare($connect, $sql);
        if (!$stmt) {
            $connection_error = "Lỗi chuẩn bị truy vấn: " . mysqli_error($connect);
        } else {
            mysqli_stmt_bind_param($stmt, "s", $token);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $user = mysqli_fetch_assoc($result);
                $_SESSION['staff_user_id'] = $user['user_id'];
                $_SESSION['staff_name'] = $user['full_name'];
                
                // Lấy thông tin vai trò từ bảng roles
                $role_id = $user['role_id'];
                $sql_role = "SELECT role_name FROM roles WHERE role_id = ?";
                $stmt_role = mysqli_prepare($connect, $sql_role);
                if (!$stmt_role) {
                    $connection_error = "Lỗi chuẩn bị truy vấn vai trò: " . mysqli_error($connect);
                } else {
                    mysqli_stmt_bind_param($stmt_role, "i", $role_id);
                    mysqli_stmt_execute($stmt_role);
                    $result_role = mysqli_stmt_get_result($stmt_role);
                    $role_info = mysqli_fetch_assoc($result_role);
                    
                    $_SESSION['staff_role'] = $role_info['role_name'];
                    $_SESSION['staff_redirect_count'] = 0;
                    
                    // Chuyển hướng đến trang dashboard tương ứng
                    if ($_SESSION['staff_role'] == 'sales') {
                        header("Location: sales/dashboard.php");
                        exit;
                    } else if ($_SESSION['staff_role'] == 'inventory') {
                        header("Location: inventory/dashboard.php");
                        exit;
                    } else {
                        header("Location: dashboard/index.php");
                        exit;
                    }
                }
            }
        }
        
        // Nếu token không hợp lệ, xóa cookie
        setcookie('staff_remember', '', time() - 3600, '/');
    }
}

// Hiển thị lỗi nếu có
$error = isset($_GET['error']) ? $_GET['error'] : (isset($error) ? $error : '');
$success = isset($_GET['success']) ? $_GET['success'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';

// Xác định role hiện tại để debug
$current_role = isset($_SESSION['staff_role']) ? $_SESSION['staff_role'] : 'Chưa đăng nhập';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Nhân viên - EYEGLASSES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --wood-beige: #c9a769;     /* Màu chính - gỗ beige */
            --light-gold: #e6c78c;     /* Màu gold nhạt */
            --cream-white: #f5f5f0;    /* Trắng kem */
            --black: #212529;          /* Đen */
        }
        
        body {
            background-color: var(--cream-white);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 900px;
            display: flex;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .login-image {
            flex: 1;
            background: linear-gradient(to right, rgba(201, 167, 105, 0.8), rgba(230, 199, 140, 0.5)), 
                        url('../admin/public/img/glasses-login.jpg') center/cover no-repeat;
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-image h2 {
            font-size: 32px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .login-image p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .login-form {
            flex: 1;
            background: white;
            padding: 40px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header .logo {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 15px;
            display: inline-block;
        }
        
        .form-header .logo-highlight {
            color: var(--wood-beige);
        }
        
        .form-header h2 {
            font-size: 24px;
            color: var(--black);
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: #777;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--black);
        }
        
        .form-control {
            width: 100%;
            height: 45px;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--wood-beige);
            box-shadow: 0 0 0 3px rgba(201, 167, 105, 0.2);
        }
        
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .form-check-input {
            margin-right: 8px;
        }
        
        .form-check-label {
            font-size: 14px;
            color: #666;
        }
        
        .btn-login {
            width: 100%;
            height: 45px;
            background: linear-gradient(to right, var(--wood-beige), var(--light-gold));
            border: none;
            border-radius: 5px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: linear-gradient(to right, var(--wood-beige), var(--wood-beige));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(201, 167, 105, 0.3);
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .forgot-password a {
            color: var(--wood-beige);
            text-decoration: none;
            font-weight: 500;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .alert {
            margin-bottom: 20px;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            .login-image {
                min-height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <h2>Khu vực Nhân viên</h2>
            <p>Đăng nhập để truy cập vào hệ thống quản lý của EYEGLASSES. Bạn sẽ có thể quản lý đơn hàng, sản phẩm, nhà cung cấp và các báo cáo quan trọng.</p>
        </div>
        <div class="login-form">
            <div class="form-header">
                <span class="logo">EYE<span class="logo-highlight">GLASSES</span></span>
                <h2>Đăng nhập Nhân viên</h2>
                <p>Vui lòng đăng nhập để tiếp tục</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-info">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form action="process_login.php" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Nhập email của bạn" required>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Nhập mật khẩu của bạn" required>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Ghi nhớ đăng nhập
                    </label>
                </div>
                <button type="submit" class="btn-login">Đăng nhập</button>
            </form>
            
            <div class="forgot-password">
                <a href="../forgot_password.php">Quên mật khẩu?</a>
            </div>
            
            <div class="text-center mt-4">
                <p>Cần trở về trang chủ? <a href="../index.php" style="color: var(--wood-beige);">Về trang chủ</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 