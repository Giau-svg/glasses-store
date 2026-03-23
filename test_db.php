<?php
// Include the database connection
require 'admin/root.php';

// Get brands
echo "<h2>Brands</h2>";
$sql = "SELECT * FROM brands ORDER BY brand_id ASC";
$result_brands = mysqli_query($connect, $sql);

if ($result_brands) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Brand Name</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result_brands)) {
        echo "<tr>";
        echo "<td>" . $row['brand_id'] . "</td>";
        echo "<td>" . $row['brand_name'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Error: " . mysqli_error($connect);
}

// Get categories
echo "<h2>Categories</h2>";
$sql = "SELECT * FROM categories ORDER BY id ASC";
$result_categories = mysqli_query($connect, $sql);

if ($result_categories) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Category Name</th><th>Description</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result_categories)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['description'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "Error: " . mysqli_error($connect);
} 