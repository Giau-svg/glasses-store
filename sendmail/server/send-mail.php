<?php
// su dung thu vien khi require vao
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'src/PHPMailer.php';
require 'src/Exception.php';
require 'src/SMTP.php';

function mySendMail($email, $title, $name, $content)
{
    $mail = new PHPMailer(true);
    try {
        //Server settings
        // Bật debug để xem chi tiết lỗi
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output

        $mail->isSMTP();                                           //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                      //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                  //Enable SMTP authentication
        $mail->Username   = 'shopdientu345@gmail.com';             //SMTP username
        
        // Mật khẩu không có khoảng trắng
        $mail->Password   = 'sonpieqweirigwbt';                    //SMTP password

        // Cấu hình 1: Sử dụng TLS (port 587)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        // Sử dụng TLS
        $mail->Port       = 587;                                   // Port cho TLS
        
        /* Cấu hình 2: Sử dụng SSL (port 465) - nếu TLS không hoạt động, hãy comment cấu hình 1 và bỏ comment dưới đây
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           // Sử dụng SSL
        $mail->Port       = 465;                                   // Port cho SSL
        */
        
        $mail->CharSet = "UTF-8";

        //Recipients
        $mail->setFrom('shopdientu345@gmail.com', 'SHOPDIENTU');
        $mail->addAddress($email, $name);                          // Người nhận
        
        //Content
        $mail->isHTML(true);                                       //Set email format to HTML
        $mail->Subject = $title;
        $mail->Body    = $content;
        
        // Xử lý lỗi xác thực
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        throw new Exception("Không thể gửi email: " . $mail->ErrorInfo);
    }
}

