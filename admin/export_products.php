<?php
include '../includes/db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=products_export.csv');

$output = fopen("php://output", "w");

// Column headers
fputcsv($output, ['ID', 'Name', 'Description', 'Price', 'Stock', 'Image']);

// Fetch products
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['description'],
        number_format($row['price'], 2),
        $row['stock'],
        $row['image']
    ]);
}

fclose($output);
exit;
