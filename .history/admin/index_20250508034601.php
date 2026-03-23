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

// Reset session count để tránh lỗi vòng lặp
if (isset($_SESSION['admin_redirect_count']) && $_SESSION['admin_redirect_count'] >= 5) {
    // Xóa các session của admin
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'admin_') === 0) {
            unset($_SESSION[$key]);
        }
    }
    $_SESSION['admin_redirect_count'] = 0;
}

if (!isset($_SESSION['admin_redirect_count'])) {
    $_SESSION['admin_redirect_count'] = 0;
}

// Xử lý clear session nếu có yêu cầu
if (isset($_GET['clear']) && $_GET['clear'] == 1) {
    // Xóa tất cả dữ liệu session admin
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'admin_') === 0) {
            unset($_SESSION[$key]);
        }
    }
    
    // Xóa cookie admin
    setcookie('admin_remember', '', time() - 3600, '/');
    
    // Chuyển hướng về trang đăng nhập
    header("Location: index.php?success=Đã xóa toàn bộ session và cookie admin");
    exit;
}

// Kiểm tra nếu đã đăng nhập (chỉ chuyển hướng nếu chưa quá ngưỡng redirect)
if (isset($_SESSION['admin_redirect_count']) && $_SESSION['admin_redirect_count'] < 5) {
    if (isset($_SESSION['admin_user_id']) && isset($_SESSION['admin_level'])) {
        // Tăng bộ đếm redirect
        $_SESSION['admin_redirect_count']++;
        // Admin đã đăng nhập, chuyển hướng đến trang dashboard
        header("Location: dashboard/index.php");
        exit;
    }
}

// Reset bộ đếm khi hiển thị form đăng nhập
$_SESSION['admin_redirect_count'] = 0;

// Xử lý đăng nhập tự động bằng cookie nếu chưa đạt ngưỡng redirect
if (isset($_COOKIE['admin_remember']) && !empty($_COOKIE['admin_remember'])) {
    // Yêu cầu file kết nối database
    require 'root.php';
    
    // Kiểm tra xem kết nối có thành công không
    if (!$connect) {
        $connection_error = "Không thể kết nối đến cơ sở dữ liệu. Lỗi: " . mysqli_connect_error();
    } else {
        $token = $_COOKIE['admin_remember'];
        
        // Kiểm tra token trong bảng users cho admin
        $sql = "SELECT * FROM users WHERE token = ? AND role_id = 1 LIMIT 1";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['admin_user_id'] = $user['user_id'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = 'admin';
            $_SESSION['admin_level'] = 1;
            $_SESSION['admin_redirect_count'] = 0;
            
            header("Location: dashboard/index.php");
            exit;
        }
        
        // Nếu token không hợp lệ, xóa cookie
        setcookie('admin_remember', '', time() - 3600, '/');
    }
}

// Hiển thị lỗi nếu có
$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - EYEGLASSES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --beige: #d2bc9b;        /* Tông màu gỗ nhạt */
            --warm-wood: #c8b6a6;    /* Tông màu gỗ ấm */
            --cream-white: #f5f5f0;  /* Trắng kem */
            --light-gold: #d4af37;   /* Vàng nhạt */
            --black: #212529;        /* Đen */
            --dark-wood: #9c8c7c;    /* Gỗ đậm */
            --admin-dark: #4e73df;   /* Màu admin */
            --admin-light: #6b85e3;  /* Màu admin nhạt */
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
            background: linear-gradient(to right, rgba(78, 115, 223, 0.8), rgba(107, 133, 227, 0.5)), 
                        url('public/img/glasses-login.jpg') center/cover no-repeat;
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
            color: var(--admin-dark);
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
            border-color: var(--admin-dark);
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.2);
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
            background: linear-gradient(to right, var(--admin-dark), var(--admin-light));
            border: none;
            border-radius: 5px;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: linear-gradient(to right, #3b5bbd, var(--admin-dark));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
        }
        
        .form-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: #777;
        }
        
        .form-footer a {
            color: var(--admin-dark);
            text-decoration: none;
            font-weight: 600;
        }
        
        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        .alert-info {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
            border: 1px solid rgba(23, 162, 184, 0.2);
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-image {
                padding: 30px;
                min-height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <h2>Khu vực quản trị</h2>
            <p>Đăng nhập để truy cập vào hệ thống quản trị của cửa hàng kính mắt EYEGLASSES. Khu vực này chỉ dành cho quản trị viên và người có quyền truy cập.</p>
        </div>
        <div class="login-form">
            <div class="form-header">
                <div class="logo">
                    <i class="fas fa-glasses" style="color: var(--admin-dark);"></i> EYE<span class="logo-highlight">GLASSES</span>
                </div>
                <h2>Đăng nhập quản trị</h2>
                <p>Nhập thông tin đăng nhập để vào hệ thống</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form action="process_login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email hoặc tên đăng nhập</label>
                    <input type="text" name="email" id="email" class="form-control" placeholder="Nhập email hoặc tên đăng nhập">
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Nhập mật khẩu">
                </div>
                
                <div class="form-check">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                    <label for="remember" class="form-check-label">Ghi nhớ đăng nhập</label>
                </div>
                
                <button type="submit" class="btn-login">Đăng nhập</button>
                
                <div class="form-footer">
                    <p>Quên mật khẩu? <a href="forgot-password.php">Khôi phục mật khẩu</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>