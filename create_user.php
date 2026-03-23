<?php
// Create a new user with the specified email and password
require 'admin/root.php';

$email = 'diamon@gmail.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$username = 'diamond';
$full_name = 'Diamond User';
$role_id = 2; // Customer role

// Check if user already exists
$check_sql = "SELECT * FROM users WHERE email = '$email'";
$check_result = mysqli_query($connect, $check_sql);

if (mysqli_num_rows($check_result) > 0) {
    echo "User with email $email already exists";
} else {
    // Insert new user
    $sql = "INSERT INTO users (username, email, password, full_name, role_id) 
            VALUES ('$username', '$email', '$password', '$full_name', $role_id)";
            
    if (mysqli_query($connect, $sql)) {
        echo "User created successfully with email: $email and password: admin123";
    } else {
        echo "Error: " . mysqli_error($connect);
    }
}

mysqli_close($connect);
?> 