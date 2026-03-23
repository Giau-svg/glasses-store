<?php
// Update user role to admin
require 'admin/root.php';

$email = 'diamon@gmail.com';

// Update user role
$sql = "UPDATE users SET role_id = 1 WHERE email = '$email'";
            
if (mysqli_query($connect, $sql)) {
    echo "User role updated successfully for $email. Now they have admin access.";
} else {
    echo "Error: " . mysqli_error($connect);
}

mysqli_close($connect);
?> 