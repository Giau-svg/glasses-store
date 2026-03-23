<?php
// Đường dẫn tương đối đến check_business_manager_login.php và root.php
require '../busmanage/check_business_manager_login.php';
require '../busmanage/root.php';

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra kết nối database
if (!$connect) {
    die("Lỗi kết nối database: " . mysqli_connect_error());
}

// Xác định hành động (add, edit, delete)
$action = isset($_GET['action']) ? $_GET['action'] : 'add';

// Lấy danh sách vai trò để hiển thị trong form
$stmt_roles = $connect->prepare("SELECT role_id, role_name FROM roles");
$stmt_roles->execute();
$roles = $stmt_roles->get_result();

// Xử lý xóa
if ($action == 'delete' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $stmt = $connect->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: index.php');
    exit();
}

// Xử lý thêm/sửa
$user = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $stmt = $connect->prepare("SELECT user_id, username, email, role_id FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$user) {
        header('Location: index.php');
        exit();
    }
}

// Xử lý form (thêm hoặc sửa)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role_id = (int)$_POST['role_id'];

    // Kiểm tra dữ liệu
    $errors = [];
    if (empty($username)) $errors[] = "Tên người dùng không được để trống.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email không hợp lệ.";
    if ($action == 'add' && empty($password)) $errors[] = "Mật khẩu không được để trống khi thêm tài khoản.";
    if (empty($role_id)) $errors[] = "Vui lòng chọn vai trò.";

    if (empty($errors)) {
        if ($action == 'add') {
            // Thêm tài khoản mới
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $connect->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $username, $email, $hashed_password, $role_id);
        } else {
            // Sửa tài khoản
            if (!empty($password)) {
                // Cập nhật cả mật khẩu
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $connect->prepare("UPDATE users SET username = ?, email = ?, password = ?, role_id = ? WHERE user_id = ?");
                $stmt->bind_param("ssssi", $username, $email, $hashed_password, $role_id, $user['user_id']);
            } else {
                // Không cập nhật mật khẩu
                $stmt = $connect->prepare("UPDATE users SET username = ?, email = ?, role_id = ? WHERE user_id = ?");
                $stmt->bind_param("ssii", $username, $email, $role_id, $user['user_id']);
            }
        }
        $stmt->execute();
        $stmt->close();
        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $action == 'add' ? 'Thêm Tài Khoản' : 'Sửa Tài Khoản'; ?> - EYEGLASSES</title>
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Đường dẫn tương đối đến busmanage_sidebar.php -->
        <?php include '../partials/busmanage_sidebar.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Đường dẫn tương đối đến busmanage_topbar.php -->
                <?php include '../partials/busmanage_topbar.php'; ?>
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800"><?php echo $action == 'add' ? 'Thêm Tài Khoản' : 'Sửa Tài Khoản'; ?></h1>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo $error; ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?php echo $action == 'add' ? 'Thêm Tài Khoản Mới' : 'Sửa Thông Tin Tài Khoản'; ?></h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-group">
                                    <label for="username">Tên Người Dùng</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($user['username']) ? htmlspecialchars($user['username']) : ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Mật Khẩu <?php echo $action == 'edit' ? '(Để trống nếu không thay đổi)' : ''; ?></label>
                                    <input type="password" class="form-control" id="password" name="password" <?php echo $action == 'add' ? 'required' : ''; ?>>
                                </div>
                                <div class="form-group">
                                    <label for="role_id">Vai Trò</label>
                                    <select class="form-control" id="role_id" name="role_id" required>
                                        <option value="">Chọn vai trò</option>
                                        <?php while ($role = $roles->fetch_assoc()): ?>
                                            <option value="<?php echo $role['role_id']; ?>" <?php echo isset($user['role_id']) && $user['role_id'] == $role['role_id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($role['role_name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary"><?php echo $action == 'add' ? 'Thêm' : 'Cập nhật'; ?></button>
                                <a href="index.php" class="btn btn-secondary">Hủy</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright © EYEGLASSES 2025</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <script src="../public/vendor/jquery/jquery.min.js"></script>
    <script src="../public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery.easing@1.4.1/jquery.easing.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>
</body>
</html>
<?php
$stmt_roles->close();
?>