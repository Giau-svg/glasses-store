<?php
    require '../root.php';
    require '../check_admin_login.php';

    $search = trim($_GET['search'] ?? null);

    $sqlPt = "SELECT count(id) as total FROM manufacturers
    WHERE
    name LIKE '%$search%'";
    
    $arrayNum = mysqli_query($connect, $sqlPt);
    $row = mysqli_fetch_assoc($arrayNum);
    $total_records = $row['total'];

    $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
    $limit = 5;

    $total_page = ceil($total_records / $limit);

    if ($current_page > $total_page) {
        $current_page = $total_page;
    } else if ($current_page < 1) {
        $current_page = 1;
    }

    $start = ($current_page - 1) * $limit;

    $sql = "SELECT * FROM `manufacturers`
        WHERE
        name LIKE '%$search%'
        ORDER BY id ASC
        LIMIT $limit offset $start";
    $result = mysqli_query($connect, $sql);
    if(empty($result)) {
        header('location:../partials/404.php');
    }
    
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Quản lý nhà sản xuất</title>

    <!-- Custom fonts for this template-->
    <link href="../public/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../public/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php include "../partials/sidebar.php"; ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php include "../partials/topbar.php"; ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <h1 class="h3 mb-2 text-gray-800">Quản lý nhà sản xuất</h1>
                    <p class="mb-4">Quản lý tất cả nhà sản xuất trong hệ thống.</p>

                    <!-- Search Form -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Tìm kiếm nhà sản xuất</h6>
                        </div>
                        <div class="card-body">
                            <form action="" method="GET" class="form-inline">
                                <div class="form-group mx-sm-3 mb-2">
                                    <label for="search" class="sr-only">Tìm kiếm</label>
                                    <input type="text" class="form-control" id="search" name="search" placeholder="Tìm kiếm nhà sản xuất..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary mb-2">Tìm kiếm</button>
                                <?php if (!empty($search)) : ?>
                                    <a href="index.php" class="btn btn-secondary mb-2 ml-2">Đặt lại</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>

                    <!-- Manufacturers Table -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Danh sách nhà sản xuất</h6>
                            <a href="form_insert.php" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Thêm nhà sản xuất mới
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên nhà sản xuất</th>
                                            <th>Mô tả</th>
                                            <th>Ngày tạo</th>
                                            <th>Cập nhật</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0) : ?>
                                            <?php while ($each = mysqli_fetch_assoc($result)) : ?>
                                                <tr>
                                                    <td><?php echo $each['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($each['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($each['description']); ?></td>
                                                    <td><?php echo $each['created_at']; ?></td>
                                                    <td><?php echo $each['updated_at']; ?></td>
                                                    <td>
                                                        <a href="form_update.php?id=<?php echo $each['id']; ?>" class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i> Sửa
                                                        </a>
                                                        <a href="delete.php?id=<?php echo $each['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa nhà sản xuất này không?');">
                                                            <i class="fas fa-trash"></i> Xóa
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Không tìm thấy nhà sản xuất nào</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_page > 1) : ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <!-- Nút về trang đầu tiên -->
                                        <?php if ($current_page > 1) : ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="First">
                                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                                    <span class="sr-only">Trang đầu</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">&laquo;&laquo;</span>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Nút trang trước -->
                                        <?php if ($current_page > 1) : ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo ($current_page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                    <span class="sr-only">Trước</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">&laquo;</span>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Các số trang -->
                                        <?php 
                                        // Hiển thị tối đa 5 trang
                                        $start_page = max(1, $current_page - 2);
                                        $end_page = min($total_page, $start_page + 4);
                                        
                                        if ($start_page > 1) : ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">1</a>
                                            </li>
                                            <?php if ($start_page > 2): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php for ($i = $start_page; $i <= $end_page; $i++) : ?>
                                            <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($end_page < $total_page): ?>
                                            <?php if ($end_page < $total_page - 1): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            <?php endif; ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $total_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                    <?php echo $total_page; ?>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Nút trang sau -->
                                        <?php if ($current_page < $total_page) : ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo ($current_page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                    <span class="sr-only">Sau</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">&raquo;</span>
                                            </li>
                                        <?php endif; ?>

                                        <!-- Nút đến trang cuối -->
                                        <?php if ($current_page < $total_page) : ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $total_page; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Last">
                                                    <span aria-hidden="true">&raquo;&raquo;</span>
                                                    <span class="sr-only">Trang cuối</span>
                                                </a>
                                            </li>
                                        <?php else: ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">&raquo;&raquo;</span>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php include "../partials/footer.php"; ?>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="../public/vendor/jquery/jquery.min.js"></script>
    <script src="../public/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../public/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../public/js/sb-admin-2.min.js"></script>

    <script>
        function Del(name) {
            return confirm("Bạn có chắc muốn xóa nhà sản xuất: " + name + " ?");
        }
    </script>

    <script>
        $(document).ready(function() {
            // Thêm class active cho menu sidebar
            $("#manufacturersNav").addClass("active");
        });
    </script>

    <style>
        .pagination {
            margin-top: 20px;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .pagination .page-link {
            color: #4e73df;
            padding: 0.5rem 0.75rem;
            line-height: 1.25;
            border: 1px solid #dddfeb;
            margin-left: -1px;
        }
        
        .pagination .page-link:hover {
            background-color: #eaecf4;
            border-color: #dddfeb;
        }
        
        .pagination .page-item.disabled .page-link {
            color: #858796;
            pointer-events: none;
            background-color: #fff;
            border-color: #dddfeb;
        }
        
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
</body>
</html>