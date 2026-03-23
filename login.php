<?php 
session_start();

// Kiểm tra cookie đăng nhập của khách hàng
if(isset($_COOKIE['customer_remember'])) {
    require 'admin/root.php';
    $token = $_COOKIE['customer_remember'];
    $sql = "SELECT * FROM users WHERE token = ? AND role_id = 2 LIMIT 1";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) === 1) {
        $each = mysqli_fetch_array($result);
        $_SESSION['customer_id'] = $each['user_id'];
        $_SESSION['customer_name'] = $each['full_name'];
        header('location:index.php');
        exit;
    }
}

// Nếu đã đăng nhập thì chuyển về trang chủ
if(isset($_SESSION['customer_id'])) {
    header('location:index.php');
    exit;
}

// Lấy thông báo lỗi nếu có
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - EYEGLASSES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            border-color: #ffa500;
            outline: none;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me input {
            margin-right: 10px;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #ffa500;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-login:hover {
            background: #ff8c00;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .signup-link a {
            color: #ffa500;
            text-decoration: none;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            background: #ffe6e6;
            color: #ff0000;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .back-to-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #666;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-to-home:hover {
            color: #ffa500;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i>
        Về trang chủ
    </a>
    
    <div class="login-container">
        <div class="login-header">
            <h1>Đăng nhập</h1>
            <p>Chào mừng bạn quay trở lại!</p>
        </div>
        
        <?php if(!empty($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form action="process_customer_login.php" method="post">
            <div class="form-group">
                <label for="identifier">Email hoặc tên đăng nhập</label>
                <input type="text" id="identifier" name="identifier" required 
                       placeholder="Nhập email hoặc tên đăng nhập">
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Nhập mật khẩu">
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ghi nhớ đăng nhập</label>
            </div>
            
            <button type="submit" class="btn-login">Đăng nhập</button>
        </form>
        
        <div class="signup-link">
            Chưa có tài khoản? <a href="signup.php">Đăng ký ngay</a>
        </div>
    </div>
</body>
</html> 