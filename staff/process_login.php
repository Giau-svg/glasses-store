<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

require '../admin/root.php';
session_start();

// Reset số lần redirect ngay khi bắt đầu xử lý đăng nhập
$_SESSION['staff_redirect_count'] = 0;

// Kiểm tra dữ liệu từ form
if (empty($_POST['email']) || empty($_POST['password'])) {
    header('location:index.php?error=Vui lòng điền đầy đủ thông tin đăng nhập');
    exit;
}

$email = mysqli_real_escape_string($connect, $_POST['email']);
$password = $_POST['password'];

// Đăng nhập nhanh với mật khẩu và email cố định cho nhân viên bán hàng
if ($email == 'sales@opticvision.com' && $password == 'sales123') {
    $role = 'sales';
    $role_id = 3; // Role ID cho Sales
    
    // Kiểm tra xem tài khoản đã tồn tại chưa
    $sql_check = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt_check = mysqli_prepare($connect, $sql_check);
    if (!$stmt_check) {
        header('location:index.php?error=Lỗi hệ thống: ' . mysqli_error($connect));
        exit;
    }
    
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) == 0) {
        // Tạo tài khoản mới nếu chưa tồn tại
        $hashed_password = md5($password); // Sử dụng md5 thay vì password_hash để tương thích với truy vấn hiện có
        $full_name = 'Nhân viên bán hàng';
        $username = 'sales';
        
        $sql_insert = "INSERT INTO users (username, email, password, full_name, role_id) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($connect, $sql_insert);
        if (!$stmt_insert) {
            header('location:index.php?error=Lỗi hệ thống: ' . mysqli_error($connect));
            exit;
        }
        
        mysqli_stmt_bind_param($stmt_insert, "ssssi", $username, $email, $hashed_password, $full_name, $role_id);
        mysqli_stmt_execute($stmt_insert);
        
        if (mysqli_stmt_affected_rows($stmt_insert) <= 0) {
            header('location:index.php?error=Không thể tạo tài khoản: ' . mysqli_error($connect));
            exit;
        }
        
        $user_id = mysqli_insert_id($connect);
    } else {
        $user = mysqli_fetch_assoc($result_check);
        $user_id = $user['user_id'];
        $full_name = $user['full_name'];
    }
    
    // Xóa session staff cũ để tránh xung đột, nhưng giữ nguyên các session khác
    // Lưu các biến session không phải staff
    $temp_admin = isset($_SESSION['admin_user_id']) ? $_SESSION['admin_user_id'] : null;
    $temp_admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : null;
    $temp_admin_role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : null;
    
    $temp_customer = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null;
    $temp_customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : null;
    
    // Xóa các session staff
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'staff_') === 0) {
            unset($_SESSION[$key]);
        }
    }
    
    // Khôi phục các session khác
    if ($temp_admin) $_SESSION['admin_user_id'] = $temp_admin;
    if ($temp_admin_name) $_SESSION['admin_name'] = $temp_admin_name;
    if ($temp_admin_role) $_SESSION['admin_role'] = $temp_admin_role;
    
    if ($temp_customer) $_SESSION['customer_id'] = $temp_customer;
    if ($temp_customer_name) $_SESSION['customer_name'] = $temp_customer_name;
    
    // Thiết lập các biến session cho staff
    $_SESSION['staff_user_id'] = $user_id;
    $_SESSION['staff_name'] = $full_name;
    $_SESSION['staff_role'] = $role;
    $_SESSION['staff_redirect_count'] = 0;
    
    // Ghi log đăng nhập
    $activity = 'Đăng nhập hệ thống';
    $details = 'Đăng nhập với email ' . $email;
    
    // Chuyển hướng đến dashboard nhân viên bán hàng
    header('location:sales/dashboard.php');
    exit;
} 
// Đăng nhập nhanh với mật khẩu và email cố định cho nhân viên quản lý kho
else if ($email == 'inventory@opticvision.com' && $password == 'inventory123') {
    $role = 'inventory';
    $role_id = 4; // Role ID cho Inventory
    
    // Kiểm tra xem tài khoản đã tồn tại chưa
    $sql_check = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt_check = mysqli_prepare($connect, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) == 0) {
        // Tạo tài khoản mới nếu chưa tồn tại
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $full_name = 'Nhân viên quản lý kho';
        $username = 'inventory';
        
        $sql_insert = "INSERT INTO users (username, email, password, full_name, role_id) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($connect, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "ssssi", $username, $email, $hashed_password, $full_name, $role_id);
        mysqli_stmt_execute($stmt_insert);
        
        $user_id = mysqli_insert_id($connect);
    } else {
        $user = mysqli_fetch_assoc($result_check);
        $user_id = $user['user_id'];
        $full_name = $user['full_name'];
    }
    
    // Xóa session staff cũ để tránh xung đột, nhưng giữ nguyên các session khác
    // Lưu các biến session không phải staff
    $temp_admin = isset($_SESSION['admin_user_id']) ? $_SESSION['admin_user_id'] : null;
    $temp_admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : null;
    $temp_admin_role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : null;
    
    $temp_customer = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null;
    $temp_customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : null;
    
    // Xóa các session staff
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'staff_') === 0) {
            unset($_SESSION[$key]);
        }
    }
    
    // Khôi phục các session khác
    if ($temp_admin) $_SESSION['admin_user_id'] = $temp_admin;
    if ($temp_admin_name) $_SESSION['admin_name'] = $temp_admin_name;
    if ($temp_admin_role) $_SESSION['admin_role'] = $temp_admin_role;
    
    if ($temp_customer) $_SESSION['customer_id'] = $temp_customer;
    if ($temp_customer_name) $_SESSION['customer_name'] = $temp_customer_name;
    
    // Thiết lập các biến session cho staff
    $_SESSION['staff_user_id'] = $user_id;
    $_SESSION['staff_name'] = $full_name;
    $_SESSION['staff_role'] = $role;
    $_SESSION['staff_redirect_count'] = 0;
    
    // Ghi log đăng nhập
    $activity = 'Đăng nhập hệ thống';
    $details = 'Đăng nhập với email ' . $email;
    
    // Chuyển hướng đến dashboard nhân viên quản lý kho
    header('location:inventory/dashboard.php');
    exit;
}

// Kiểm tra tài khoản trong bảng users (chỉ cho phép vai trò nhân viên)
$sql = "SELECT u.*, r.role_name FROM users u 
        JOIN roles r ON u.role_id = r.role_id 
        WHERE u.email = ? AND r.role_name IN ('sales', 'inventory') LIMIT 1";
$stmt = mysqli_prepare($connect, $sql);
if (!$stmt) {
    header('location:index.php?error=Lỗi hệ thống: ' . mysqli_error($connect));
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);
    
    // Kiểm tra mật khẩu - hỗ trợ cả hai phương thức băm (md5 và password_hash)
    if (password_verify($password, $user['password']) || md5($password) === $user['password']) {
        // Xóa session staff cũ để tránh xung đột, nhưng giữ nguyên các session khác
        // Lưu các biến session không phải staff
        $temp_admin = isset($_SESSION['admin_user_id']) ? $_SESSION['admin_user_id'] : null;
        $temp_admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : null;
        $temp_admin_role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : null;
        
        $temp_customer = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null;
        $temp_customer_name = isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : null;
        
        // Xóa các session staff
        foreach ($_SESSION as $key => $value) {
            if (strpos($key, 'staff_') === 0) {
                unset($_SESSION[$key]);
            }
        }
        
        // Khôi phục các session khác
        if ($temp_admin) $_SESSION['admin_user_id'] = $temp_admin;
        if ($temp_admin_name) $_SESSION['admin_name'] = $temp_admin_name;
        if ($temp_admin_role) $_SESSION['admin_role'] = $temp_admin_role;
        
        if ($temp_customer) $_SESSION['customer_id'] = $temp_customer;
        if ($temp_customer_name) $_SESSION['customer_name'] = $temp_customer_name;
        
        // Thiết lập các biến session cho staff
        $_SESSION['staff_user_id'] = $user['user_id'];
        $_SESSION['staff_name'] = $user['full_name'];
        $_SESSION['staff_role'] = $user['role_name'];
        $_SESSION['staff_redirect_count'] = 0;
        
        // Ghi nhớ đăng nhập
        if (isset($_POST['remember'])) {
            $token = md5(time() . $email);
            $sql_update_token = "UPDATE users SET token = ? WHERE user_id = ?";
            $stmt_token = mysqli_prepare($connect, $sql_update_token);
            mysqli_stmt_bind_param($stmt_token, "si", $token, $user['user_id']);
            mysqli_stmt_execute($stmt_token);
            
            setcookie('staff_remember', $token, time() + 86400 * 30);
        }
        
        // Ghi log đăng nhập
        $activity = 'Đăng nhập hệ thống';
        $details = 'Đăng nhập thành công vào hệ thống';
        
        // Chuyển hướng đến trang dashboard phù hợp
        if ($user['role_name'] == 'sales') {
            header('location:sales/dashboard.php');
        } else if ($user['role_name'] == 'inventory') {
            // Đảm bảo role đã được thiết lập đúng
            $_SESSION['staff_role'] = 'inventory';
            
            // Debug thông tin
            error_log("Redirecting to inventory dashboard with role: " . $_SESSION['staff_role']);
            
            // Chuyển hướng đến dashboard kho
            header('location:inventory/dashboard.php');
        } else {
            header('location:dashboard/index.php');
        }
        exit;
    } else {
        header('location:index.php?error=Mật khẩu không chính xác');
        exit;
    }
} else {
    // Kiểm tra nếu đây là tài khoản admin, chuyển hướng đến trang đăng nhập của admin
    $sql_admin = "SELECT * FROM users WHERE email = ? AND role_id = 1 LIMIT 1";
    $stmt_admin = mysqli_prepare($connect, $sql_admin);
    mysqli_stmt_bind_param($stmt_admin, "s", $email);
    mysqli_stmt_execute($stmt_admin);
    $result_admin = mysqli_stmt_get_result($stmt_admin);
    
    if (mysqli_num_rows($result_admin) === 1) {
        header('location:../admin/index.php?message=Vui lòng đăng nhập tại khu vực Admin');
        exit;
    }
    
    // Nếu không phải nhân viên hoặc admin
    header('location:index.php?error=Email không tồn tại hoặc bạn không có quyền truy cập vào khu vực này');
    exit;
}

mysqli_close($connect); 