<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center">
        <?php 
        // Lấy và giữ nguyên các tham số tìm kiếm hiện tại
        $params = $_GET;
        
        // Hàm tạo URL với tham số được cập nhật
        function getPageUrl($page, $params) {
            $params['page'] = $page;
            return 'index.php?' . http_build_query($params);
        }
        
        if ($current_page > 1 && $total_page > 1) { 
        ?>
            <li class="page-item">
                <a class="page-link" href="<?php echo getPageUrl($current_page - 1, $params); ?>">Trước</a>
            </li>
        <?php } ?>
        
        <?php for ($i = 1; $i <= $total_page; $i++) { ?>
            <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                <?php if ($i == $current_page) { ?>
                    <span class="page-link"><?php echo $i ?></span>
                <?php } else { ?>
                    <a class="page-link" href="<?php echo getPageUrl($i, $params); ?>"><?php echo $i ?></a>
                <?php } ?>
            </li>
        <?php } ?>
        
        <?php if ($current_page < $total_page && $total_page > 1) { ?>
            <li class="page-item">
                <a class="page-link" href="<?php echo getPageUrl($current_page + 1, $params); ?>">Sau</a>
            </li>
        <?php } ?>
    </ul>
</nav>