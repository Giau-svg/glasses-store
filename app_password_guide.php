<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hướng dẫn tạo mật khẩu ứng dụng Gmail</title>
    <link rel="stylesheet" href="./public/css/rss.css" />
    <link rel="stylesheet" href="./public/css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <style>
        .container {
            padding-top: 80px;
            padding-bottom: 40px;
        }
        
        .guide-container {
            max-width: 900px;
            margin: 0 auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
            background-color: #fff;
            padding: 30px;
        }
        
        .guide-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        
        .guide-header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .guide-header p {
            color: #777;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .step {
            margin-bottom: 30px;
            border-bottom: 1px solid #f5f5f5;
            padding-bottom: 20px;
        }
        
        .step:last-child {
            border-bottom: none;
        }
        
        .step h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background-color: #eb1f27;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .step p {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 15px;
        }
        
        .step img {
            max-width: 100%;
            height: auto;
            border: 1px solid #eee;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin: 10px 0;
        }
        
        .note {
            background-color: #fff8e1;
            padding: 15px;
            border-left: 4px solid #ffb300;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
        }
        
        .note h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .note p {
            font-size: 14px;
            color: #555;
            margin: 0;
        }
        
        code {
            background-color: #f5f5f5;
            padding: 2px 5px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
        }
        
        .btn {
            display: inline-block;
            background-color: #eb1f27;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #d01b22;
        }
        
        .footer-guide {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- header -->
        <div class="header header-fixed">
            <div class="header-container">
                <header class="header-top">
                    <div class="logo">
                        <a href="index.php">
                            <img src="./public/img/logo.png" alt="" class="img">
                        </a>
                    </div>
                    <div class="h1">
                        <h1>Hướng dẫn mật khẩu ứng dụng Gmail</h1>
                    </div>
                </header>
            </div>
        </div>
        
        <div class="container">
            <div class="guide-container">
                <div class="guide-header">
                    <h1>Hướng dẫn tạo mật khẩu ứng dụng Gmail</h1>
                    <p>Để sử dụng Gmail gửi email từ ứng dụng bên thứ ba, bạn cần tạo mật khẩu ứng dụng thay vì sử dụng mật khẩu Gmail thông thường. Hướng dẫn này sẽ giúp bạn tạo mật khẩu ứng dụng cho Gmail.</p>
                </div>
                
                <div class="step">
                    <h2><span class="step-number">1</span> Bật xác minh 2 bước cho tài khoản Google</h2>
                    <p>Trước khi có thể tạo mật khẩu ứng dụng, bạn cần bật xác minh 2 bước cho tài khoản Google của mình:</p>
                    <ol>
                        <li>Truy cập <a href="https://myaccount.google.com/security" target="_blank">https://myaccount.google.com/security</a></li>
                        <li>Chọn "Xác minh 2 bước" trong phần "Đăng nhập vào Google"</li>
                        <li>Làm theo các bước hướng dẫn để bật xác minh 2 bước</li>
                    </ol>
                    <img src="https://storage.googleapis.com/support-kms-prod/ZU9BzqQ9SUwPRmNQ9pM6bOL9qi9OeFVdSScH" alt="Bật xác minh 2 bước">
                </div>
                
                <div class="step">
                    <h2><span class="step-number">2</span> Tạo mật khẩu ứng dụng</h2>
                    <p>Sau khi đã bật xác minh 2 bước, hãy làm theo các bước sau để tạo mật khẩu ứng dụng:</p>
                    <ol>
                        <li>Truy cập <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a> (bạn có thể được yêu cầu nhập lại mật khẩu Google)</li>
                        <li>Ở dưới "Chọn ứng dụng", chọn "Khác (Tên tùy chỉnh)"</li>
                        <li>Nhập tên cho ứng dụng (Ví dụ: "SHOPDIENTU")</li>
                        <li>Nhấp vào "Tạo"</li>
                        <li>Google sẽ tạo và hiển thị mật khẩu ứng dụng (một dãy 16 ký tự)</li>
                        <li>Sao chép mật khẩu này và lưu lại để sử dụng trong ứng dụng của bạn</li>
                        <li>Nhấp vào "Xong"</li>
                    </ol>
                    <img src="https://storage.googleapis.com/support-kms-prod/vOxy8YmkIpiDnRJsXGjcWX55UELCzA21ZMdt" alt="Tạo mật khẩu ứng dụng">
                </div>
                
                <div class="step">
                    <h2><span class="step-number">3</span> Cập nhật cài đặt trong ứng dụng SHOPDIENTU</h2>
                    <p>Sau khi đã có mật khẩu ứng dụng, bạn cần cập nhật cài đặt email trong ứng dụng:</p>
                    <ol>
                        <li>Mở file <code>./sendmail/server/send-mail.php</code> trong ứng dụng của bạn</li>
                        <li>Tìm dòng code thiết lập thông tin đăng nhập Gmail:</li>
                        <pre><code>$mail->Username   = 'your-email@gmail.com';   // Gmail email của bạn
$mail->Password   = 'your-app-password';     // Mật khẩu ứng dụng cho Gmail
$mail->setFrom('your-email@gmail.com', 'SHOPDIENTU');  // Người gửi</code></pre>
                        <li>Thay thế <code>'your-email@gmail.com'</code> bằng địa chỉ Gmail của bạn</li>
                        <li>Thay thế <code>'your-app-password'</code> bằng mật khẩu ứng dụng bạn vừa tạo</li>
                        <li>Lưu file</li>
                    </ol>
                </div>
                
                <div class="note">
                    <h3>Lưu ý quan trọng</h3>
                    <p><strong>Bảo mật:</strong> Không chia sẻ mật khẩu ứng dụng với người khác. Nó cấp quyền truy cập vào tài khoản Gmail của bạn.</p>
                    <p><strong>Quyền truy cập:</strong> Mật khẩu ứng dụng cấp quyền truy cập đầy đủ vào tài khoản Gmail của bạn từ ứng dụng được chỉ định.</p>
                    <p><strong>Quản lý mật khẩu:</strong> Bạn có thể xem và thu hồi các mật khẩu ứng dụng bất cứ lúc nào bằng cách truy cập <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a>.</p>
                </div>
                
                <div class="step">
                    <h2><span class="step-number">4</span> Kiểm tra chức năng gửi email</h2>
                    <p>Sau khi cập nhật cài đặt, bạn nên kiểm tra chức năng gửi email:</p>
                    <ol>
                        <li>Truy cập trang kiểm tra email của ứng dụng</li>
                        <li>Nhập địa chỉ email để gửi thử</li>
                        <li>Nếu thành công, bạn sẽ nhận được email kiểm tra</li>
                        <li>Nếu không thành công, kiểm tra lại các cài đặt và mật khẩu ứng dụng</li>
                    </ol>
                    <a href="test_email.php" class="btn">Kiểm tra gửi email</a>
                </div>
                
                <div class="footer-guide">
                    <p>Nếu bạn gặp vấn đề hoặc cần hỗ trợ thêm, vui lòng liên hệ quản trị viên.</p>
                    <a href="index.php" class="btn">Trở về trang chủ</a>
                </div>
            </div>
        </div>
        
        <!-- footer -->
        <div class="footer">
            <div class="grid_full-width">
                <div class="grid">
                    <div class="row">
                        <div class="col col-4 col-mobi">
                            <div class="logo logo-bottom ml-mobi">
                                <img src="./public/img/logo2.png" alt="" class="img">
                            </div>
                            <div class="footer__text ml-mobi">
                                <p>Vietpro Academy thành lập năm 2009. Chúng
                                    tôi đào tạo chuyên sâu trong 2 lĩnh vực là Lập
                                    trình Website & Mobile nhằm cung cấp cho thị
                                    trường CNTT Việt Nam những lập trình viên
                                    thực sự chất lượng, có khả năng làm việc độc
                                    lập, cũng như Team Work ở mọi môi trường đòi
                                    hỏi sự chuyên nghiệp cao. </p>
                            </div>
                        </div>
                        <div class="col col-4 col-mobi">
                            <div class="footer__about">
                                <h3>Địa chỉ</h3>
                            </div>
                            <div class="footer__text">
                                <p>
                                    B8A Võ Văn Dũng - Hoàng Cầu Đống Đa -
                                    Hà Nội
                                </p>
                                <p>
                                    Số 25 Ngõ 178/71 - Tây Sơn Đống Đa - Hà
                                    Nội
                                </p>
                            </div>
                        </div>
                        <div class="col col-4 col-mobi">
                            <div class="footer__about">
                                <h3>Dịch vụ</h3>
                            </div>
                            <div class="footer__text">
                                <p>
                                    Bảo hành rơi vỡ, ngấm nước Care Diamond
                                </p>
                                <p>
                                    Bảo hành Care X60 rơi vỡ ngấm nước vẫn Đổi
                                    mới
                                </p>
                            </div>
                        </div>
                        <div class="col col-4 col-mobi">
                            <div class="footer__about">
                                <h3>Hotline</h3>
                            </div>
                            <div class="footer__text">
                                <p>
                                    Phone Sale: (+84) 0988 550 553
                                </p>
                                <p>
                                    Email: vietpro.edu.vn@gmail.com
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 