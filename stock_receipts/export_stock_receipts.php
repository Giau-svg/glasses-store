<?php
require '../busmanage/check_business_manager_login.php';
require '../busmanage/root.php';

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kiểm tra kết nối database
if (!$connect) {
    die("Lỗi kết nối database: " . mysqli_connect_error());
}
mysqli_set_charset($connect, "utf8");

// Lấy giá trị bộ lọc
$supplier_id = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$amount_status = isset($_GET['amount_status']) ? $_GET['amount_status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Xây dựng điều kiện WHERE cho bộ lọc
$where_clause = "WHERE 1=1";
if (!empty($supplier_id)) {
    $where_clause .= " AND sr.supplier_id = " . mysqli_real_escape_string($connect, $supplier_id);
}
if (!empty($start_date)) {
    $where_clause .= " AND DATE(sr.receipt_date) >= '" . mysqli_real_escape_string($connect, $start_date) . "'";
}
if (!empty($end_date)) {
    $where_clause .= " AND DATE(sr.receipt_date) <= '" . mysqli_real_escape_string($connect, $end_date) . "'";
}
if (!empty($search)) {
    $where_clause .= " AND (sr.receipt_id LIKE '%" . mysqli_real_escape_string($connect, $search) . "%' OR s.supplier_name LIKE '%" . mysqli_real_escape_string($connect, $search) . "%')";
}

$high_amount_threshold = 10000000;
switch ($amount_status) {
    case 'high_amount':
        $where_clause .= " AND sr.total_amount >= $high_amount_threshold";
        break;
    case 'normal_amount':
        $where_clause .= " AND sr.total_amount < $high_amount_threshold";
        break;
}

// Truy vấn để lấy danh sách phiếu nhập
$query = "
    SELECT sr.receipt_id, sr.receipt_date, sr.total_amount, s.supplier_name 
    FROM stock_receipts sr 
    JOIN suppliers s ON sr.supplier_id = s.supplier_id 
    $where_clause
    ORDER BY sr.receipt_date DESC
";

$result = mysqli_query($connect, $query);
if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($connect));
}

// Thiết lập header cho tải xuống CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="stock_receipts_export_' . date('Ymd_His') . '.csv"');

// Thêm BOM cho UTF-8 để đảm bảo mã hóa đúng trong Excel
echo "\xEF\xBB\xBF";

// Tạo file CSV
$output = fopen('php://output', 'w');

// Ghi tiêu đề CSV
fputcsv($output, ['Mã Phiếu', 'Ngày Nhập', 'Nhà Cung Cấp', 'Tổng Tiền'], ';');

// Ghi dữ liệu
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['receipt_id'],
        date('d/m/Y H:i', strtotime($row['receipt_date'])),
        $row['supplier_name'],
        number_format($row['total_amount'], 0, ',', '.') . ' đ'
    ], ';');
}

fclose($output);
mysqli_close($connect);
exit();
?>