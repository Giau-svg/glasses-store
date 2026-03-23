<?php
if (!isset($current_category_id)) $current_category_id = '';
if (!isset($category_links)) $category_links = [
    1 => 'sunglasses',
    2 => 'eyeglasses',
    3 => 'lens',
    4 => 'contact-lens',
    5 => 'fashion-glasses'
];
?>
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

<style>
.sidebar__category {
    margin-top: 20px;
    padding: 20px;
    background: linear-gradient(145deg, #f8f9fa, #e9ecef);
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.4);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.sidebar__category:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

.sidebar__header {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e63946;
}

.category-icon {
    font-size: 24px;
    margin-right: 10px;
    color: #e63946;
}

.sidebar__title {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin: 0;
    background: linear-gradient(to right, #e63946, #457b9d);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-fill-color: transparent;
}

.sidebar__list {
    list-style: none;
    padding: 0;
}

.sidebar__item {
    margin-bottom: 12px;
    transition: transform 0.2s ease;
}

.sidebar__item:hover {
    transform: translateX(5px);
}

.sidebar__item.active {
    transform: translateX(5px);
}

.sidebar__link {
    display: flex;
    align-items: center;
    padding: 14px 15px;
    font-size: 16px;
    font-weight: 500;
    color: #444;
    text-decoration: none;
    transition: all 0.3s ease;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    border-left: 4px solid transparent;
}

.sidebar__link:hover {
    color: #e63946;
    border-left: 4px solid #e63946;
    background-color: #f8f9fa;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}

.sidebar__link.active {
    color: #e63946;
    border-left: 4px solid #e63946;
    background-color: #f8f9fa;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    font-weight: bold;
}

.sidebar__icon {
    margin-right: 10px;
    font-size: 12px;
    transition: transform 0.2s ease;
    color: #457b9d;
}

.sidebar__link:hover .sidebar__icon,
.sidebar__link.active .sidebar__icon {
    transform: translateX(3px);
}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
