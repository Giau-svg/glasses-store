<?php
require 'admin/root.php';

// Check brands table
echo "<h2>Brands in Database:</h2>";
$sql_brands = "SELECT * FROM brands WHERE brand_name LIKE '%Oakley%'";
$result_brands = mysqli_query($connect, $sql_brands);

if (mysqli_num_rows($result_brands) > 0) {
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
    echo "<p>No Oakley brand found in the database.</p>";
}

// Get Oakley brand ID
$brand_id = 0;
mysqli_data_seek($result_brands, 0);
if ($row = mysqli_fetch_assoc($result_brands)) {
    $brand_id = $row['brand_id'];
}

// Check products table for Oakley products
echo "<h2>Oakley Products in Database:</h2>";
if ($brand_id > 0) {
    $sql_products = "SELECT p.*, b.brand_name, c.category_name 
                    FROM products p
                    JOIN brands b ON p.brand_id = b.brand_id
                    JOIN categories c ON p.category_id = c.category_id
                    WHERE p.brand_id = $brand_id";
    $result_products = mysqli_query($connect, $sql_products);
    
    if (mysqli_num_rows($result_products) > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Product Name</th><th>Category</th><th>Price</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result_products)) {
            echo "<tr>";
            echo "<td>" . $row['product_id'] . "</td>";
            echo "<td>" . $row['product_name'] . "</td>";
            echo "<td>" . $row['category_name'] . "</td>";
            echo "<td>" . number_format($row['price']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No Oakley products found in the database.</p>";
    }
} else {
    echo "<p>Can't check products because Oakley brand ID was not found.</p>";
}

// Test the query that view_brand.php uses
echo "<h2>Testing the query used in view_brand.php:</h2>";
$brand_name = "Oakley";
$sql = "SELECT products.*, brands.brand_name 
        FROM products 
        JOIN brands ON brands.brand_id = products.brand_id 
        WHERE brands.brand_name = '$brand_name'";
$result_test = mysqli_query($connect, $sql);

echo "<p>Query: $sql</p>";
echo "<p>Result count: " . mysqli_num_rows($result_test) . "</p>";

if (mysqli_num_rows($result_test) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Product Name</th><th>Brand Name</th><th>Category ID</th><th>Price</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result_test)) {
        echo "<tr>";
        echo "<td>" . $row['product_id'] . "</td>";
        echo "<td>" . $row['product_name'] . "</td>";
        echo "<td>" . $row['brand_name'] . "</td>";
        echo "<td>" . $row['category_id'] . "</td>";
        echo "<td>" . number_format($row['price']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>The view_brand.php query returned no results.</p>";
}
?> 