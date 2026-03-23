<?php
// Hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Chèn file gửi email
require './sendmail/server/send-mail.php';

// Kiểm tra xem form đã được gửi chưa
if(isset($_POST['send_test'])) {
    $recipient_email = $_POST['test_email'];
    
    try {
        // Gọi hàm mySendMail để thử gửi một email
        mySendMail(
            $recipient_email, 
            'Test email từ SHOPDIENTU', 
            'Người nhận', 
            'Đây là email kiểm tra chức năng gửi mail. Nếu bạn nhận được email này, cấu hình email của bạn hoạt động tốt!'
        );
        $success = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra gửi Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #eb1f27;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #d01b22;
        }
        .message {
            margin: 20px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .steps {
            margin-top: 20px;
            background-color: #e9f5ff;
            padding: 15px;
            border-radius: 5px;
        }
        .steps h3 {
            margin-top: 0;
        }
        .steps ol {
            margin-left: 20px;
            padding-left: 0;
        }
        .steps li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kiểm Tra Gửi Email</h1>
        
        <?php if(isset($success)): ?>
            <div class="message success">
                <p>Email đã được gửi thành công! Vui lòng kiểm tra hộp thư của bạn (cả thư rác).</p>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="message error">
                <p>Lỗi khi gửi email: <?php echo $error; ?></p>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <label for="test_email">Nhập email để kiểm tra:</label>
            <input type="email" id="test_email" name="test_email" required 
                   placeholder="example@gmail.com">
            
            <button type="submit" name="send_test">Gửi Email Kiểm Tra</button>
        </form>
        
        <div class="steps">
            <h3>Hướng dẫn cài đặt email:</h3>
            <ol>
                <li>Mở file <strong>./sendmail/server/send-mail.php</strong></li>
                <li>Điền email của bạn vào biến <code>$mail->Username</code></li>
                <li>Điền mật khẩu ứng dụng vào biến <code>$mail->Password</code></li>
                <li>Cập nhật email trong <code>$mail->setFrom()</code></li>
                <li>Lưu file và thử lại</li>
            </ol>
            <p>
                <a href="app_password_guide.php" style="color: #eb1f27; font-weight: bold; text-decoration: underline;">
                    Xem hướng dẫn chi tiết tạo mật khẩu ứng dụng Gmail
                </a>
            </p>
        </div>
    </div>
</body>
</html> 