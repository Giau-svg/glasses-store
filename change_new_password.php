<?php
require 'admin/root.php';
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Thêm kiểm tra và hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sql = "SELECT * from forgot_password
    where `token` = '$token'";
$result = mysqli_query($connect, $sql);
if (!(mysqli_num_rows($result) === 1)) {
    header('location:login.php');
    exit;
}
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu mới</title>
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
        
        .container {
            padding-top: 80px;
            padding-bottom: 40px;
        }
        
        .reset-container {
            display: flex;
            max-width: 900px;
            margin: 0 auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
            background-color: #fff;
        }
        
        .reset-image {
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
        
        .reset-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
        }
        
        .reset-image h2 {
            position: relative;
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .reset-image p {
            position: relative;
            font-size: 16px;
            text-align: center;
            line-height: 1.6;
        }
        
        .reset-form {
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
            background-color: #e3f8e5;
            color: #38c172;
            border: 1px solid #d3f0d5;
        }
        
        label.error {
            color: #e3342f;
            font-size: 13px;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .reset-container {
                flex-direction: column;
                max-width: 100%;
                margin: 0 15px;
            }
            
            .reset-image {
                padding: 40px 20px;
            }
            
            .reset-form {
                padding: 30px 20px;
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
                    <div class="logo">
                        <a href="index.php">
                            <img src="./public/img/logo.png" alt="" class="img">
                        </a>
                    </div>
                    <div class="h1">
                        <h1>Đổi mật khẩu</h1>
                    </div>
                </header>
            </div>
        </div>
        <div class="container">
            <div class="reset-container">
                <div class="reset-image">
                    <h2>Cài đặt mật khẩu mới</h2>
                    <p>Nhập mật khẩu mới của bạn để tiếp tục mua sắm trên SHOPDIENTU.</p>
                </div>
                <div class="reset-form">
                    <div class="form-header">
                        <h2>Đổi mật khẩu mới</h2>
                        <p>Nhập mật khẩu mới và an toàn để bảo vệ tài khoản của bạn</p>
                    </div>
                    
                    <?php if(isset($_SESSION['error'])): ?>
                    <div class="text-status text-error">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['success'])): ?>
                    <div class="text-status text-success">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                    <?php endif; ?>
                    
                    <form id="form_change_password" action="process_change_password.php" method="post">
                        <input type="hidden" name="token" value="<?php echo $token ?>">
                        
                        <div class="form-group">
                            <label for="password">Mật khẩu mới</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn-reset">
                            Đổi mật khẩu
                        </button>
                    </form>
                    
                    <div class="form-footer">
                        <a href="login.php">Trở về trang đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- chan -->
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#form_change_password").validate({
                rules: {
                    password: {
                        required: true,
                        minlength: 6
                    },
                    confirm_password: {
                        required: true,
                        equalTo: "#password"
                    }
                },
                messages: {
                    password: {
                        required: "Vui lòng nhập mật khẩu mới",
                        minlength: "Mật khẩu phải có ít nhất 6 ký tự"
                    },
                    confirm_password: {
                        required: "Vui lòng xác nhận mật khẩu",
                        equalTo: "Mật khẩu xác nhận không khớp"
                    }
                }
            });
        });
    </script>
</body>
</html>