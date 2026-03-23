<?php
session_start();

require 'root.php';

// Kiểm tra kết nối database
if (!$connect) {
    $_SESSION['error'] = "Không thể kết nối đến cơ sở dữ liệu.";
    header("Location: index.php");
    exit();
}

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['email'];
    $password_input = $_POST['password'];

    // Sử dụng prepared statement để tránh SQL Injection
    $stmt = $connect->prepare("SELECT user_id, role_id, password FROM users WHERE username = ?");
    if (!$stmt) {
        $_SESSION['error'] = "Lỗi truy vấn: " . $connect->error;
        header("Location: index.php");
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stored_password = $user['password'];

        // Kiểm tra mật khẩu
        $password_md5 = md5($password_input);
        if (password_verify($password_input, $stored_password) || $password_md5 === $stored_password) {
            // Nếu mật khẩu trong DB là MD5 và khớp, hoặc nếu là password_hash và khớp
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role_id'] = $user['role_id'];

            // Chuyển hướng dựa trên role_id
            if ($user['role_id'] == 5) {
                header("Location: business_dashboard.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        }
    }

    // Nếu không tìm thấy hoặc mật khẩu sai
    $_SESSION['error'] = "Sai tên đăng nhập hoặc mật khẩu.";
    header("Location: index.php");
    exit();

    $stmt->close();
} else {
    // Nếu không phải POST, quay lại trang đăng nhập
    header("Location: index.php");
    exit();
}

$connect->close();
?>