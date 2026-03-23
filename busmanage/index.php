<?php
session_start();

require 'root.php';

// Không cần kiểm tra session và chuyển hướng ở đây nữa
// Xử lý đăng nhập đã được thực hiện trong process_login.php
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Quản Lý Doanh Nghiệp - EYEGLASSES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --background: #f8f9fc;
        --white: #fff;
        --text-gray-800: #5a5c69;
        --primary: #48bb78;
        --primary-dark: #38a169;
        --gray-light: #ddd;
        --gray-medium: #777;
        --danger: #e74a3b;
    }

    body {
        background-color: var(--background);
        font-family: 'Nunito', sans-serif;
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
        background: linear-gradient(to right, rgba(72, 187, 120, 0.8), rgba(56, 161, 105, 0.5)), 
                    url('public/img/glasses-login.jpg') center/cover no-repeat;
        color: var(--white);
        padding: 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .login-image h2 {
        font-size: 32px;
        margin-bottom: 20px;
        font-weight: 700;
    }

    .login-image p {
        font-size: 16px;
        opacity: 0.9;
        line-height: 1.6;
    }

    .login-form {
        flex: 1;
        background: var(--white);
        padding: 40px;
    }

    .form-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .form-header .logo {
        font-size: 30px;
        font-weight: 700;
        margin-bottom: 15px;
        display: inline-block;
    }

    .form-header .logo-highlight {
        color: var(--primary);
    }

    .form-header h2 {
        font-size: 24px;
        color: var(--text-gray-800);
        margin-bottom: 10px;
    }

    .form-header p {
        color: var(--gray-medium);
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
        color: var(--text-gray-800);
    }

    .form-control {
        width: 100%;
        height: 45px;
        padding: 10px 15px;
        border: 1px solid var(--gray-light);
        border-radius: 5px;
        font-size: 14px;
        transition: all 0.3s;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(72, 187, 120, 0.2);
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
        color: var(--gray-medium);
    }

    .btn-login {
        width: 100%;
        height: 45px;
        background: linear-gradient(to right, var(--primary), var(--primary-dark));
        border: none;
        border-radius: 5px;
        color: var(--white);
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-login:hover {
        background: linear-gradient(to right, var(--primary-dark), var(--primary));
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(72, 187, 120, 0.3);
    }

    .form-footer {
        margin-top: 25px;
        text-align: center;
        font-size: 14px;
        color: var(--gray-medium);
    }

    .form-footer a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
    }

    .form-footer a:hover {
        text-decoration: underline;
    }

    .alert {
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        font-size: 14px;
    }

    .alert-danger {
        background-color: rgba(231, 74, 59, 0.1);
        color: var(--danger);
        border: 1px solid rgba(231, 74, 59, 0.2);
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
            <h2>Khu vực quản lý doanh nghiệp</h2>
            <p>Đăng nhập để truy cập vào hệ thống quản lý doanh nghiệp của cửa hàng kính mắt EYEGLASSES. Khu vực này chỉ dành cho quản lý doanh nghiệp.</p>
        </div>
        <div class="login-form">
            <div class="form-header">
                <div class="logo">
                    <i class="fas fa-glasses" style="color: var(--primary);"></i> EYE<span class="logo-highlight">GLASSES</span>
                </div>
                <h2>Đăng nhập quản lý doanh nghiệp</h2>
                <p>Nhập thông tin đăng nhập để vào hệ thống</p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <form action="process_login.php" method="POST">
                <div class="form-group">
                    <label for="email">Tên đăng nhập</label>
                    <input type="text" name="email" id="email" class="form-control" placeholder="Nhập tên đăng nhập" required>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Nhập mật khẩu" required>
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