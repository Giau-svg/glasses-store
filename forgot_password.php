<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="./public/css/rss.css" />
    <link rel="stylesheet" href="./public/css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <style>
        .h1 {
            flex: 4;
            color: #ffff;
        }
        
        .logo::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            z-index: -1;
            filter: blur(8px);
            animation: glow 3s infinite alternate;
        }

        @keyframes glow {
            0% {
                opacity: 0.5;
                filter: blur(8px);
            }
            100% {
                opacity: 0.8;
                filter: blur(12px);
            }
        }

        @keyframes glow {
            0% {
                opacity: 0.5;
                filter: blur(8px);
            }
            100% {
                opacity: 0.8;
                filter: blur(12px);
            }
        }
        
        .container {
            padding-top: 80px;
            padding-bottom: 40px;
        }
        
        .forgot-container {
            display: flex;
            max-width: 900px;
            margin: 0 auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
            background-color: #fff;
        }
        
        .forgot-image {
            flex: 1;
            background: url('./public/img/login-bg.jpg') center/cover no-repeat;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #fff;
            padding: 30px;
        }
        
        .forgot-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
        }
        
        .forgot-image h2 {
            position: relative;
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .forgot-image p {
            position: relative;
            font-size: 16px;
            text-align: center;
            line-height: 1.6;
        }
        
        .forgot-form {
            flex: 1;
            padding: 40px;
            background-color: #fff;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: 600;
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
            color: #333;
        }
        
        .form-control {
            width: 100%;
            height: 45px;
            padding: 0 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #eb1f27;
            box-shadow: 0 0 0 3px rgba(235, 31, 39, 0.1);
        }
        
        .btn-reset {
            display: block;
            width: 100%;
            height: 45px;
            background-color: #eb1f27;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
            text-transform: uppercase;
        }
        
        .btn-reset:hover {
            background-color: #d01b22;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        
        .form-footer a {
            color: #eb1f27;
            text-decoration: none;
            font-weight: 500;
        }
        
        .text-status {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
        }
        
        .text-error {
            background-color: #fee;
            color: #e3342f;
            border: 1px solid #fdd;
        }
        
        .text-success {
            background-color: #dff;
            color: #38c172;
            border: 1px solid #dff;
        }
        
        @media (max-width: 768px) {
            .forgot-container {
                flex-direction: column;
                max-width: 95%;
            }
            
            .forgot-image {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- dau -->
        <div class="header header-fixed">
            <div class="header-container">
                <header class="header-top">
                <div class="logo" style="position: relative;">
                        <a href="index.php" style="font-size: 32px; color: white; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                            <i class="fas fa-bolt" style="color:rgb(255, 0, 0); margin-right: 5px;"></i>SHOP<span style="color:rgb(255, 0, 0);">DIENTU</span>
                        </a>
                    </div>
                    <div class="h1" style="text-align: center; flex: 1;">
                        <h1 style="color: white; font-size: 24px; margin: 0;">Quên Mật Khẩu</h1>
                    </div>
                </header>
            </div>
        </div>
        <div class="container">
            <div class="grid_full-width">
                <div class="forgot-container">
                    <div class="forgot-image">
                        <h2>Lấy lại mật khẩu</h2>
                        <p>Vui lòng nhập email đã đăng ký để nhận liên kết đặt lại mật khẩu của bạn. Chúng tôi sẽ gửi email xác nhận cho bạn.</p>
                    </div>
                    <div class="forgot-form">
                        <div class="form-header">
                            <h2>Quên mật khẩu?</h2>
                            <p>Nhập email của bạn dưới đây để nhận liên kết đặt lại mật khẩu.</p>
                            <div style="font-size: 12px; margin-top: 10px; color: #777; background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                                <p style="margin-bottom: 5px;"><i class="fas fa-info-circle"></i> <strong>Lưu ý:</strong> Email sẽ được gửi từ hệ thống SHOPDIENTU.</p>
                                <p style="margin-bottom: 0;">
                                    <a href="test_email.php" style="color: #eb1f27; text-decoration: underline;">
                                        Kiểm tra email
                                    </a> | 
                                    <a href="app_password_guide.php" style="color: #eb1f27; text-decoration: underline;">
                                        Hướng dẫn thiết lập email
                                    </a>
                                </p>
                            </div>
                        </div>
                        
                        <?php if (isset($_SESSION['error'])) { ?>
                            <div class="text-status text-error">
                                <?php
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php } ?>

                        <?php if (isset($_SESSION['success'])) { ?>
                            <div class="text-status text-success">
                                <?php
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                                ?>
                            </div>
                        <?php } ?>
                        
                        <form action="process_forgot_password.php" method="post">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Nhập email của bạn" required>
                            </div>
                            
                            <button type="submit" class="btn-reset">
                                Gửi Yêu Cầu
                            </button>
                            
                            <div class="form-footer">
                                <p>Đã nhớ mật khẩu? <a href="login.php">Đăng nhập</a></p>
                                <p>Chưa có tài khoản? <a href="signup.php">Đăng ký</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php include './partials/footer.php' ?>
        </div>
    </div>
</body>

</html>