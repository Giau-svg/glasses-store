<?php
$content = file_get_contents("admin/categories/delete.php");
$content = str_replace("pure", "eyeglasses_shop", $content);
file_put_contents("admin/categories/delete.php", $content);
echo "Updated successfully";
