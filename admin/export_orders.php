<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

include '../includes/db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=orders_export.csv');

$output = fopen('php://output', 'w');

// Updated CSV Headers to include Phone
fputcsv($output, ['Order ID', 'Customer Name', 'Email', 'Phone', 'Total', 'Status', 'Created At']);

// Fetch Orders with phone number
$orders_sql = "SELECT id, customer_name, customer_email, customer_mobile, total, status, created_at FROM orders ORDER BY created_at DESC";
$result = $conn->query($orders_sql);

// Output rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['customer_name'],
        $row['customer_email'],
        $row['customer_mobile'],
        $row['total'],
        $row['status'],
        $row['created_at']
    ]);
}

fclose($output);
exit;
