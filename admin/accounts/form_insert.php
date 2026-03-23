<?php
require '../check_super_admin_login.php'; // Đã cho phép business manager
require '../root.php';

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Lấy danh sách vai trò (trừ vai trò 'customer')
$role_sql = "SELECT role_id, role_name FROM roles WHERE role_name != 'customer' ORDER BY role_name";
$role_result = mysqli_query($connect, $role_sql);
if (!$role_result) {
    die("Lỗi truy vấn vai trò: " . mysqli_error($connect));
}
$roles = [];
while ($role = mysqli_fetch_assoc($role_result)) {
    $roles[] = $role;
}

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role_id = (int)($_POST['role_id'] ?? 0);

    // Kiểm tra dữ liệu đầu vào
    $errors = [];
    if (empty($full_name)) $errors[] = "Họ tên không được để trống.";
    if (empty($username)) $errors[] = "Tài khoản không được để trống.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email không hợp lệ.";
    if (empty($password)) $errors[] = "Mật khẩu không được để trống.";
    if ($role_id === 0) $errors[] = "Vui lòng chọn vai trò.";

    // Kiểm tra username hoặc email đã tồn tại
    $check_sql = "SELECT user_id FROM users WHERE username = ? OR email = ?";
    $check_stmt = mysqli_prepare($connect, $check_sql);
    mysqli_stmt_bind_param($check_stmt, 'ss', $username, $email);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Tài khoản hoặc email đã tồn tại.";
    }

    if (empty($errors)) {
        // Băm mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Thêm tài khoản mới
        $insert_sql = "INSERT INTO users (full_name, username, email, password, phone, address, role_id, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $insert_stmt = mysqli_prepare($connect, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, 'ssssssi', $full_name, $username, $email, $hashed_password, $phone, $address, $role_id);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            header("Location: index.php?success=1&message=Thêm tài khoản thành công");
            exit;
        } else {
            $errors[] = "Lỗi khi thêm tài khoản: " . mysqli_error($connect);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Thêm Tài khoản - EYEGLASSES</title>
    
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../public/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php 
        $sidebar_file = (strpos($_SERVER['REQUEST_URI'], 'busmanage') !== false) ? '../partials/busmanage_sidebar.php' : '../partials/sidebar.php';
        include $sidebar_file;
        ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php 
                $topbar_file = (strpos($_SERVER['REQUEST_URI'], 'busmanage') !== false) ? '../partials/busmanage_topbar.php' : '../partials/topbar.php';
                include $topbar_file;
                ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Thêm Tài khoản</h1>

                    <!-- Hiển thị thông báo lỗi nếu có -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form thêm tài khoản -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin Tài khoản</h6>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="form-group">
                                    <label for="full_name">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="username">Tài khoản <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Số điện thoại</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="address">Địa chỉ</label>
                                    <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="role_id">Vai trò <span class="text-danger">*</span></label>
                                    <select class="form-control" id="role_id" name="role_id" required>
                                        <option value="">Chọn vai trò</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role['role_id']; ?>" <?php echo (isset($_POST['role_id']) && $_POST['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
                                                <?php 
                                                $display_name = $role['role_name'];
                                                switch($role['role_name']) {
                                                    case 'admin': $display_name = 'Admin'; break;
                                                    case 'sales': $display_name = 'Nhân viên bán hàng'; break;
                                                    case 'stock': $display_name = 'Nhân viên kho'; break;
                                                    case 'inventory': $display_name = 'Quản lý kho hàng'; break;
                                                }
                                                echo $display_name;
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Thêm
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include '../partials/footer.php'; ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="../public/vendor/jquery/jquery.min.js"></script>
    <script src="../public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../public/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../public/js/sb-admin-2.min.js"></script>
</body>
</html>