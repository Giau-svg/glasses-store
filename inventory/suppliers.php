<?php
session_start();
require_once '../check_inventory_login.php';
require_once '../../admin/root.php';

// Bật hiển thị lỗi cho mục đích debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Thiết lập trang hiện tại
$active_page = 'suppliers';
$page_title = 'Quản lý nhà cung cấp';

// Khởi tạo thông báo
$success_message = '';
$error_message = '';

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Xóa thông báo khỏi session sau khi lấy
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Xóa thông báo khỏi session sau khi lấy
}

// Kiểm tra kết nối database
if (!isset($connect)) {
    die("Không thể kết nối đến database");
}

// --- CODE XỬ LÝ TÌM KIẾM ---
// Lấy giá trị tìm kiếm từ URL (sử dụng phương thức GET của form)
$search_term = trim($_GET['search'] ?? '');

$where_conditions = []; // Mảng chứa các điều kiện cho mệnh đề WHERE
$query_params = []; // Mảng chứa các tham số cho Prepared Statement
$param_types = ''; // Chuỗi chứa kiểu dữ liệu của các tham số

// Nếu có từ khóa tìm kiếm, thêm điều kiện vào mảng
if (!empty($search_term)) {
    // Thêm điều kiện tìm kiếm theo tên nhà cung cấp HOẶC số điện thoại
    // Sử dụng LIKE %...% để tìm kiếm chuỗi con
    $where_conditions[] = "(supplier_name LIKE ? OR phone LIKE ?)";
    // Thêm tham số vào mảng, bao gồm ký tự % cho LIKE
    $query_params[] = "%" . $search_term . "%";
    $query_params[] = "%" . $search_term . "%";
    // Chỉ định kiểu dữ liệu là string cho cả hai tham số
    $param_types .= 'ss';
}

// Xây dựng mệnh đề WHERE hoàn chỉnh từ mảng các điều kiện
$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// --- END: ADD THIS PHP CODE BLOCK ---

// Xử lý xóa nhà cung cấp
if (isset($_GET['delete'])) {
    $supplier_id = $_GET['delete'];
    
    // Kiểm tra xem nhà cung cấp đã được sử dụng chưa
    $check_sql = "SELECT COUNT(*) as count FROM stock_receipts WHERE supplier_id = ?";
    $check_stmt = mysqli_prepare($connect, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $supplier_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $usage_count = mysqli_fetch_assoc($check_result)['count'];
    
    if ($usage_count > 0) {
        $error_message = "Không thể xóa nhà cung cấp này vì đã được sử dụng trong phiếu nhập kho!";
    } else {
        $delete_sql = "DELETE FROM suppliers WHERE supplier_id = ?";
        $delete_stmt = mysqli_prepare($connect, $delete_sql);
        mysqli_stmt_bind_param($delete_stmt, "i", $supplier_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            $success_message = "Xóa nhà cung cấp thành công!";
        } else {
            $error_message = "Lỗi khi xóa nhà cung cấp: " . mysqli_error($connect);
        }
    }
}

// Xử lý thêm/cập nhật nhà cung cấp
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_id = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : '';
    $supplier_name = $_POST['supplier_name'];
    $contact_name = $_POST['contact_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    if (empty($supplier_id)) {
        // Thêm mới nhà cung cấp
        $sql = "INSERT INTO suppliers (supplier_name, contact_name, email, phone, address)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $supplier_name, $contact_name, $email, $phone, $address);

        if (mysqli_stmt_execute($stmt)) {
            // Thêm nhà cung cấp thành công! Lưu vào session và chuyển hướng
            $_SESSION['success_message'] = "Thêm nhà cung cấp thành công!";
            header('Location: suppliers.php'); // Chuyển hướng về trang danh sách
            exit(); // Dừng script sau khi chuyển hướng
        } else {
            // Lỗi khi thêm nhà cung cấp. Lưu vào session và chuyển hướng
            $_SESSION['error_message'] = "Lỗi khi thêm nhà cung cấp: " . mysqli_error($connect);
            header('Location: suppliers.php'); // Chuyển hướng về trang danh sách (với thông báo lỗi)
            exit(); // Dừng script sau khi chuyển hướng
        }
    } else {
        // Cập nhật nhà cung cấp
        $sql = "UPDATE suppliers
                SET supplier_name = ?, contact_name = ?, email = ?, phone = ?, address = ?
                WHERE supplier_id = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "sssssi", $supplier_name, $contact_name, $email, $phone, $address, $supplier_id);

        if (mysqli_stmt_execute($stmt)) {
            // Cập nhật nhà cung cấp thành công! Lưu vào session và chuyển hướng
            $_SESSION['success_message'] = "Cập nhật nhà cung cấp thành công!";
            header('Location: suppliers.php'); // Chuyển hướng về trang danh sách
            exit(); // Dừng script sau khi chuyển hướng
        } else {
            // Lỗi khi cập nhật nhà cung cấp. Lưu vào session và chuyển hướng
            $_SESSION['error_message'] = "Lỗi khi cập nhật nhà cung cấp: " . mysqli_error($connect);
            header('Location: suppliers.php'); // Chuyển hướng về trang danh sách (với thông báo lỗi)
            exit(); // Dừng script sau khi chuyển hướng
        }
    }
}

// Lấy thông tin nhà cung cấp cần chỉnh sửa
$edit_supplier = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_sql = "SELECT * FROM suppliers WHERE supplier_id = ?";
    $edit_stmt = mysqli_prepare($connect, $edit_sql);
    mysqli_stmt_bind_param($edit_stmt, "i", $edit_id);
    mysqli_stmt_execute($edit_stmt);
    $edit_result = mysqli_stmt_get_result($edit_stmt);
    $edit_supplier = mysqli_fetch_assoc($edit_result);
}


// --- START: REPLACE THE EXISTING CODE THAT FETCHES THE SUPPLIER LIST ---
// --- Lấy danh sách nhà cung cấp (áp dụng tìm kiếm) ---
// Truy vấn SQL để lấy danh sách nhà cung cấp, áp dụng mệnh đề WHERE (nếu có) và sắp xếp
$sql = "SELECT * FROM suppliers $where_clause ORDER BY supplier_name ASC"; // Áp dụng mệnh đề WHERE vào truy vấn chính

// Sử dụng prepared statement để thực thi truy vấn nếu có tham số trong mệnh đề WHERE
if (!empty($query_params)) {
    if ($stmt = mysqli_prepare($connect, $sql)) {
        // Bind các tham số đã chuẩn bị vào statement
        mysqli_stmt_bind_param($stmt, $param_types, ...$query_params);

        // Thực thi statement
        mysqli_stmt_execute($stmt);

        // Lấy kết quả từ statement đã thực thi
        $result = mysqli_stmt_get_result($stmt);

        // Đóng statement (Tùy chọn: nếu không sử dụng $stmt sau khi lấy $result)
        // mysqli_stmt_close($stmt);

    } else {
        // Xử lý lỗi nếu chuẩn bị truy vấn thất bại
        die("Lỗi chuẩn bị truy vấn danh sách nhà cung cấp: " . mysqli_error($connect));
    }
} else {
    // Nếu không có tham số tìm kiếm (mệnh đề WHERE rỗng hoặc không chứa tham số),
    // thực thi truy vấn mà không cần bind param.
    // Có thể vẫn sử dụng prepared statement để nhất quán, chỉ bỏ qua phần bind param.
     if ($stmt = mysqli_prepare($connect, $sql)) {
        // Không cần bind param vì không có điều kiện WHERE với tham số
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
     } else {
         die("Lỗi chuẩn bị truy vấn danh sách nhà cung cấp (không tìm kiếm): " . mysqli_error($connect));
     }
}

// Kiểm tra xem truy vấn chính có thành công hay không
if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($connect));
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include '../partials/head.php'; ?>
</head>
<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include 'inventory_sidebar.php'; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include 'inventory_topbar.php'; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title; ?></h1>
                        
                    </div>

                    <?php if(!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"></h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="suppliers.php" class="form-inline">
                                <div class="form-group mr-2">
                                    <label for="search" class="sr-only">Tìm kiếm</label>
                                    <input type="text" class="form-control" id="search" name="search"
                                           placeholder="Tên nhà cung cấp, SĐT..."
                                           value="<?php echo htmlspecialchars($search_term); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i>
                                </button>
                                <a href="suppliers.php" class="btn btn-secondary ml-2">
                                    <i class="fas fa-sync-alt mr-1"></i> Reset
                                </a>
                            </form>
                        </div>
                    </div>
                    <!-- Danh sách nhà cung cấp -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách nhà cung cấp</h6>
                            <button type="button" id="addSupplierBtn" class=" btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#supplierModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Thêm
                        </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên nhà cung cấp</th>
                                            <th>Người liên hệ</th>
                                            <th>Email</th>
                                            <th>Điện thoại</th>
                                            <th>Địa chỉ</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($supplier = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td><?php echo $supplier['supplier_id']; ?></td>
                                                    <td><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($supplier['contact_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                                                    <td><?php echo htmlspecialchars($supplier['address']); ?></td>
                                                    <td>
                                                        <a href="?edit=<?php echo $supplier['supplier_id']; ?>" class="btn btn-info btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?delete=<?php echo $supplier['supplier_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa nhà cung cấp này?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Không có nhà cung cấp nào</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; EYEGLASSES 2023</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <?php include '../logout_modal.php'; ?>

    <!-- Supplier Modal-->
    <div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-labelledby="supplierModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="suppliers.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="supplierModalLabel">
                            <?php echo $edit_supplier ? 'Chỉnh sửa nhà cung cấp' : 'Thêm nhà cung cấp mới'; ?>
                        </h5>

                    </div>
                    <div class="modal-body">
                        <?php if ($edit_supplier): ?>
                            <input type="hidden" name="supplier_id" value="<?php echo $edit_supplier['supplier_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="supplier_name">Tên nhà cung cấp <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="supplier_name" name="supplier_name" 
                                   value="<?php echo $edit_supplier ? htmlspecialchars($edit_supplier['supplier_name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_name">Người liên hệ</label>
                            <input type="text" class="form-control" id="contact_name" name="contact_name"
                                   value="<?php echo $edit_supplier ? htmlspecialchars($edit_supplier['contact_name']) : ''; ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo $edit_supplier ? htmlspecialchars($edit_supplier['email']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="phone" name="phone"
                                           value="<?php echo $edit_supplier ? htmlspecialchars($edit_supplier['phone']) : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Địa chỉ</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo $edit_supplier ? htmlspecialchars($edit_supplier['address']) : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $edit_supplier ? 'Cập nhật' : 'Thêm mới'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="https://cdn.jsdelivr.net/npm/jquery.easing@1.4.1/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../../admin/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
    $(document).ready(function() {
        // Khởi tạo DataTables
        $('#dataTable').DataTable({
            "pageLength": 3,
            "searching": false,
            "lengthChange": false
        });

        // Auto open modal if edit parameter is present (Giữ nguyên phần này nếu bạn muốn)
        <?php if ($edit_supplier): ?>
            // Khi modal tự động mở ở chế độ chỉnh sửa, PHP đã điền dữ liệu và thiết lập tiêu đề.
            // Chúng ta không cần làm gì thêm ở đây.
            $('#supplierModal').modal('show');
        <?php endif; ?>

        // Lắng nghe sự kiện khi modal sắp hiển thị
        $('#supplierModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Nút hoặc yếu tố đã kích hoạt modal

            // Kiểm tra xem modal có được kích hoạt bởi nút "Thêm" hay không (dựa vào ID)
            if (button.attr('id') === 'addSupplierBtn') {
                console.log("Modal được mở bởi nút 'Thêm'. Đặt về chế độ thêm mới.");
                // Thiết lập tiêu đề và nút submit cho chế độ Thêm mới
                $('#supplierModalLabel').text('Thêm nhà cung cấp mới');
                $('#supplierModal .modal-footer button[type="submit"]').text('Thêm mới');

                // Xóa trắng các trường nhập liệu trong form
                $('#supplierModal form')[0].reset(); // Sử dụng reset() để xóa form

                // Đảm bảo trường input ẩn 'supplier_id' không tồn tại hoặc có giá trị rỗng
                // Điều này quan trọng để backend biết đây là thao tác thêm mới
                var supplierIdInput = $('#supplierModal input[name="supplier_id"]');
                if (supplierIdInput.length > 0) {
                    supplierIdInput.remove(); // Xóa hoàn toàn trường input ẩn supplier_id
                    // Hoặc chỉ set giá trị rỗng: supplierIdInput.val('');
                }

            } else {
                 console.log("Modal được mở cho chế độ Chỉnh sửa (hoặc do script).");
            }
        });

        // Tùy chọn: Lắng nghe sự kiện khi modal đóng để làm sạch form và URL (khuyến khích)
         $('#supplierModal').on('hidden.bs.modal', function () {
             console.log("Modal đã đóng. Đang dọn dẹp.");
             // Reset form về trạng thái ban đầu (rỗng)
              $('#supplierModal form')[0].reset();

             // Đảm bảo trường input ẩn supplier_id bị xóa sau khi đóng modal chỉnh sửa
              $('#supplierModal input[name="supplier_id"]').remove();

             // Reset lại tiêu đề và nút submit về trạng thái "Thêm mới"
             $('#supplierModalLabel').text('Thêm nhà cung cấp mới');
             $('#supplierModal .modal-footer button[type="submit"]').text('Thêm mới');

              // Dọn dẹp tham số 'edit' trên URL nếu có sau khi đóng modal (tránh tải lại trang ở chế độ edit)
              if (window.location.search.includes('edit=')) {
                  var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search.replace(/&?edit=[^&]*/, '').replace(/^\?$/, '');
                  window.history.replaceState({}, document.title, newUrl);
              }
         });
    });
    </script>
</body>
</html>