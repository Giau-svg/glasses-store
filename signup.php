<?php
session_start();

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
    <title>Đăng ký - EYEGLASSES</title>
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
            padding: 20px;
        }
        
        .signup-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        
        .signup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .signup-header h1 {
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
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn-signup {
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
        
        .btn-signup:hover {
            background: #ff8c00;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: #ffa500;
            text-decoration: none;
        }
        
        .login-link a:hover {
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
        
        .password-requirements {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i>
        Về trang chủ
    </a>
    
    <div class="signup-container">
        <div class="signup-header">
            <h1>Đăng ký tài khoản</h1>
            <p>Tạo tài khoản để mua sắm dễ dàng hơn</p>
        </div>
        
        <?php if(!empty($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form action="process_signup.php" method="post" onsubmit="return validateForm()">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Họ và tên</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="Nhập họ và tên">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Nhập địa chỉ email">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" required 
                           pattern="[0-9]{10}" placeholder="Nhập số điện thoại">
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" required 
                           minlength="6" placeholder="Nhập mật khẩu">
                    <div class="password-requirements">
                        Mật khẩu phải có ít nhất 6 ký tự
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Địa chỉ</label>
                <input type="text" id="address" name="address" required 
                       placeholder="Nhập địa chỉ">
            </div>
            
            <button type="submit" class="btn-signup">Đăng ký</button>
        </form>
        
        <div class="login-link">
            Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
        </div>
    </div>
    
    <script>
        function validateForm() {
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            
            // Kiểm tra số điện thoại
            if (!/^[0-9]{10}$/.test(phone)) {
                alert('Số điện thoại phải có 10 chữ số!');
                return false;
            }
            
            // Kiểm tra độ dài mật khẩu
            if (password.length < 6) {
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html> 