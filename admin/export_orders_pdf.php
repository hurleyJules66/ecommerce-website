<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

require('lib/fpdf.php');
include '../includes/db.php';

// PDF setup
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Customer Orders Report',0,1,'C');
$pdf->Ln(5);

// Table Header
$pdf->SetFont('Arial','B',11);
$pdf->Cell(15, 10, 'ID', 1);
$pdf->Cell(35, 10, 'Name', 1);
$pdf->Cell(40, 10, 'Email', 1);
$pdf->Cell(30, 10, 'Phone', 1);
$pdf->Cell(25, 10, 'Total', 1);
$pdf->Cell(30, 10, 'Status', 1);
$pdf->Ln();

// Fetch Orders
$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");

$pdf->SetFont('Arial','',10);
while ($row = $orders->fetch_assoc()) {
    $pdf->Cell(15, 10, $row['id'], 1);
    $pdf->Cell(35, 10, substr($row['customer_name'], 0, 18), 1);
    $pdf->Cell(40, 10, substr($row['customer_email'], 0, 25), 1);
    $pdf->Cell(30, 10, $row['customer_mobile'], 1);
    $pdf->Cell(25, 10, '$' . number_format($row['total'], 2), 1);
    $pdf->Cell(30, 10, ucfirst($row['status']), 1);
    $pdf->Ln();
}

$pdf->Output('D', 'orders_export.pdf');
exit;
