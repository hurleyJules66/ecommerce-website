<?php
require('lib/fpdf.php');
include '../includes/db.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(190, 10, 'Product Inventory Export', 0, 1, 'C');
$pdf->Ln(5);

// Table header
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 10, 'ID', 1);
$pdf->Cell(40, 10, 'Name', 1);
$pdf->Cell(40, 10, 'Price', 1);
$pdf->Cell(20, 10, 'Stock', 1);
$pdf->Cell(80, 10, 'Description', 1);
$pdf->Ln();

// Fetch and add data
$pdf->SetFont('Arial', '', 9);
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(10, 8, $row['id'], 1);
    $pdf->Cell(40, 8, $row['name'], 1);
    $pdf->Cell(40, 8, '$' . number_format($row['price'], 2), 1);
    $pdf->Cell(20, 8, $row['stock'], 1);
    $pdf->Cell(80, 8, substr($row['description'], 0, 50), 1);
    $pdf->Ln();
}

$pdf->Output('D', 'products_export.pdf');
exit;
