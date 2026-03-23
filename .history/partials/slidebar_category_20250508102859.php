<div class="content__sidebar">
    <div class="sidebar__category">
        <div class="sidebar__header">
            <i class="fas fa-list category-icon"></i>
            <h3 class="sidebar__title">Danh mục sản phẩm</h3>
        </div>
        <ul class="sidebar__list">
            <?php
            $sql = "SELECT category_id, category_name FROM categories ORDER BY category_id ASC";
            $result_categories = mysqli_query($connect, $sql);
            while($category = mysqli_fetch_assoc($result_categories)) {
                $category_link = $category_links[$category['category_id']] ?? '';
                $is_active = ($current_category_id == $category['category_id']) ? 'active' : '';
                echo '<li class="sidebar__item '.$is_active.'">
                        <a href="'.$category_link.'" class="sidebar__link '.$is_active.'">
                            <i class="fas fa-angle-right sidebar__icon"></i>
                            '.$category['category_name'].'
                        </a>
                    </li>';
            }
            ?>
        </ul>
    </div>
</div>
