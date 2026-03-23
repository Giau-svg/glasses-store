<?php
require '../check_super_admin_login.php';
require '../root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Debug log
function debug_log($message) {
    error_log("[DEBUG] " . $message);
}

// Kiểm tra quyền super admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    debug_log("Không có quyền super admin");
    header('Location: ../index.php');
    exit;
}

// Kiểm tra ID
if (!isset($_GET['id'])) {
    debug_log("Không có ID được truyền vào");
    $_SESSION['error'] = "Thiếu thông tin cần thiết!";
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];
debug_log("Đang xử lý xóa user ID: " . $id);

// Kiểm tra user có tồn tại không
$check_sql = "SELECT user_id, username FROM users WHERE user_id = $id";
$check_result = mysqli_query($connect, $check_sql);

if (!$check_result) {
    debug_log("Lỗi truy vấn kiểm tra: " . mysqli_error($connect));
    $_SESSION['error'] = "Lỗi truy vấn: " . mysqli_error($connect);
    header('Location: index.php');
    exit;
}

if (mysqli_num_rows($check_result) === 0) {
    debug_log("Không tìm thấy user với ID: " . $id);
    $_SESSION['error'] = "Không tìm thấy tài khoản!";
    header('Location: index.php');
    exit;
}

$user = mysqli_fetch_assoc($check_result);
debug_log("Đang xóa user: " . $user['username']);

// Kiểm tra xem có phải là super admin không
if ($user['role_id'] == 1) { // Giả sử role_id 1 là super admin
    debug_log("Không thể xóa tài khoản super admin");
    $_SESSION['error'] = "Không thể xóa tài khoản super admin!";
    header('Location: index.php');
    exit;
}

// Thực hiện xóa tài khoản
$sql = "DELETE FROM users WHERE user_id = $id";
debug_log("SQL xóa: " . $sql);
$result = mysqli_query($connect, $sql);

if ($result) {
    debug_log("Xóa thành công user ID: " . $id);
    $_SESSION['success'] = "Đã xóa tài khoản thành công!";
} else {
    debug_log("Lỗi khi xóa: " . mysqli_error($connect));
    $_SESSION['error'] = "Có lỗi xảy ra khi xóa tài khoản: " . mysqli_error($connect);
}

// Chuyển hướng về trang danh sách
header('Location: index.php');
exit; 