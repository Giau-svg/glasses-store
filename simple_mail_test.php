<?php
// Hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load Composer's autoloader
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './sendmail/server/src/PHPMailer.php';
require './sendmail/server/src/Exception.php';
require './sendmail/server/src/SMTP.php';

// Form gửi
if (isset($_POST['submit'])) {
    $recipient = $_POST['email'];
    
    // Tạo PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Cấu hình SMTP
        $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Chi tiết về debug
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'shopdientu345@gmail.com';
        $mail->Password = $_POST['password']; // Lấy mật khẩu từ form
        $mail->SMTPSecure = $_POST['encryption']; // Có thể là PHPMailer::ENCRYPTION_STARTTLS hoặc PHPMailer::ENCRYPTION_SMTPS
        $mail->Port = $_POST['port']; // Port tương ứng (587 hoặc 465)
        
        // Người gửi và người nhận
        $mail->setFrom('shopdientu345@gmail.com', 'SHOPDIENTU');
        $mail->addAddress($recipient);
        
        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = 'Test Email từ SHOPDIENTU';
        $mail->Body = 'Đây là email kiểm tra. Nếu bạn nhận được email này, cấu hình đã hoạt động!';
        
        // Gửi email
        $mail->send();
        $success = "Email đã được gửi thành công!";
    } catch (Exception $e) {
        $error = "Không thể gửi email: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra Email Đơn Giản</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        label { display: block; margin-top: 10px; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { background: #4CAF50; color: white; border: none; padding: 10px 15px; margin-top: 15px; cursor: pointer; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-top: 15px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-top: 15px; }
        .note { background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-top: 15px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kiểm tra Email Đơn Giản</h1>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <label for="email">Email người nhận:</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            
            <label for="password">Mật khẩu ứng dụng Gmail (không có khoảng trắng):</label>
            <input type="text" id="password" name="password" required value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : 'sonpieqweirigwbt'; ?>">
            
            <label for="encryption">Loại mã hóa:</label>
            <select id="encryption" name="encryption">
                <option value="ENCRYPTION_STARTTLS" <?php echo (isset($_POST['encryption']) && $_POST['encryption'] == 'ENCRYPTION_STARTTLS') ? 'selected' : ''; ?>>TLS</option>
                <option value="ENCRYPTION_SMTPS" <?php echo (isset($_POST['encryption']) && $_POST['encryption'] == 'ENCRYPTION_SMTPS') ? 'selected' : ''; ?>>SSL</option>
            </select>
            
            <label for="port">Port:</label>
            <select id="port" name="port">
                <option value="587" <?php echo (isset($_POST['port']) && $_POST['port'] == '587') ? 'selected' : ''; ?>>587 (TLS)</option>
                <option value="465" <?php echo (isset($_POST['port']) && $_POST['port'] == '465') ? 'selected' : ''; ?>>465 (SSL)</option>
            </select>
            
            <button type="submit" name="submit">Gửi Email Kiểm Tra</button>
        </form>
        
        <div class="note">
            <strong>Lưu ý:</strong>
            <ol>
                <li>Đảm bảo bạn đã bật xác minh 2 bước trong tài khoản Google</li>
                <li>Mật khẩu ứng dụng phải là 16 ký tự được cấp bởi Google</li>
                <li>Nhập mật khẩu không có khoảng trắng giữa các nhóm</li>
            </ol>
        </div>
    </div>
</body>
</html> 