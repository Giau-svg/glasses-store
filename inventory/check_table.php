<?php
require_once '../../admin/root.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get table structure
$sql = "SHOW COLUMNS FROM stock_receipts";
$result = mysqli_query($connect, $sql);

if (!$result) {
    die("Error: " . mysqli_error($connect));
}

echo "<h2>Cấu trúc bảng stock_receipts</h2>";
echo "<table border='1'>";
echo "<tr><th>Tên cột</th><th>Kiểu dữ liệu</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Get sample data for the first row
$sql = "SELECT * FROM stock_receipts LIMIT 1";
$result = mysqli_query($connect, $sql);

if (!$result) {
    die("Error: " . mysqli_error($connect));
}

if (mysqli_num_rows($result) > 0) {
    echo "<h2>Mẫu dữ liệu</h2>";
    echo "<table border='1'>";
    
    $row = mysqli_fetch_assoc($result);
    echo "<tr>";
    foreach ($row as $key => $value) {
        echo "<th>" . $key . "</th>";
    }
    echo "</tr>";
    
    echo "<tr>";
    foreach ($row as $value) {
        echo "<td>" . $value . "</td>";
    }
    echo "</tr>";
    
    echo "</table>";
} else {
    echo "<p>Không có dữ liệu trong bảng</p>";
}
?> 