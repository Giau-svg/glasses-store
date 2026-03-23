<?php
    require '../check_admin_login.php';
    require '../root.php';

    $search = trim($_GET['search'] ?? '');

    $sqlPt = "SELECT count(category_id) as total FROM categories
    WHERE
    category_name LIKE '%$search%'";

    $arrayNum = mysqli_query($connect, $sqlPt);
    $row = mysqli_fetch_assoc($arrayNum);
    $total_records = $row['total'];

    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 5;

    $total_page = ceil($total_records / $limit);
    
    // Đảm bảo trang hiện tại là hợp lệ
    if ($total_page <= 0) {
        $total_page = 1;
    }
    if ($current_page > $total_page) {
        $current_page = $total_page;
    } else if ($current_page < 1) {
        $current_page = 1;
    }

    // Đảm bảo $start không bao giờ âm
    $start = max(0, ($current_page - 1) * $limit);


    $sql = "SELECT * FROM categories
        WHERE
        category_name LIKE '%$search%'
        ORDER BY category_id DESC
        LIMIT $limit OFFSET $start";
    $result = mysqli_query($connect, $sql);
    if(empty($result)) {
        header('location:../partials/404.php');
    }

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    include '../partials/head_view.php';
    ?>
    <title>Quản lý danh mục</title>
</head>

<body id="page-top">
    <?php
    include '../partials/header_view.php';
    ?>
    <div class="container-fluid">
        <?php
            require '../menu.php';
        ?>
        <h5 class="text-left mt-3">Quản lý danh mục sản phẩm</h5>
        <div class="row p-2">
            <a class="btn btn-primary ml-2" href="form_insert.php"> Thêm </a>
            <a class="btn btn-primary ml-2" href="index.php"> Xem tất cả </a>
            <form class="input-group ml-auto" style="width: 50%;" method="GET" action="">
                <input class="form-control" type="search" placeholder="Tìm kiếm tên danh mục..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <div class="row mt-3">
            <div class="col">
                <div class="table-responsive">
                    <table class="table table-bordered mt-1 align-middle" id="dataTable" width="100%" cellspacing="0">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Hình ảnh</th>
                                <th>Tên danh mục</th>
                                <th>Loại (Type)</th>
                                <th>Mô tả</th>
                                <th>Ngày tạo</th>
                                <th>Cập nhật</th>
                                <th>Sửa</th>
                                <th>Xóa</th>
                            </tr>
                        </thead>
                        <tbody class="thead-light">
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php foreach ($result as $each) : ?>
                                    <tr class="text-dark">
                                        <td class="text-primary"> <?php echo $each['category_id']; ?></td>
                                        <td>
                                            <?php if(!empty($each['image'])): ?>
                                                <img src="/Pure/admin/categoris/server/uploads/<?php echo $each['image']; ?>" alt="<?php echo htmlspecialchars($each['category_name']); ?>" class="img-thumbnail" style="max-height: 60px;">
                                            <?php else: ?>
                                                <span class="text-muted">Không có ảnh</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-primary"> <?php echo htmlspecialchars($each['category_name']); ?></td>
                                        <td class="text-success"> 
                                            <?php 
                                                if (!empty($each['type'])) {
                                                    echo htmlspecialchars($each['type']);
                                                } else {
                                                    echo '<span class="text-muted">Chưa có loại</span>';
                                                }
                                            ?>
                                        </td>
                                        <td> 
                                            <?php 
                                                if (!empty($each['description'])) {
                                                    echo htmlspecialchars($each['description']);
                                                } else {
                                                    echo '<span class="text-muted">Không có mô tả</span>';
                                                }
                                            ?>
                                        </td>
                                        <td class="text-primary"> <?php echo $each['created_at']; ?></td>
                                        <td class="text-primary"> <?php echo $each['updated_at']; ?></td>
                                        <td>
                                            <a class="btn btn-info" href="form_update.php?id=<?php echo $each['category_id']; ?>">Sửa</a>
                                        </td>
                                        <td>
                                            <a onclick="return Del('<?php echo htmlspecialchars($each['category_name']); ?>')" class="btn btn-danger" href="delete.php?id=<?php echo $each['category_id']; ?>">Xóa</a>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Không tìm thấy danh mục nào</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php
                        include '../partials/pagination.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    include '../partials/footer_view.php';
    include '../partials/js_link.php';
    ?>

    <script>
        function Del(name) {
            return confirm("Bạn có chắc muốn xóa danh mục: " + name + " ?")
        }
    </script>
</body>

</html>