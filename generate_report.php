<?php
// generate_report.php

// Include the database connection
require_once 'db_connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['login_error'] = "Please log in to access the dashboard.";
    header("Location: admin.php?loginError=1");
    exit();
}

// Include FPDF library
require('fpdf/fpdf.php');

// Create a new PDF instance
$pdf = new FPDF();
$pdf->AddPage();

// Set font for the header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Student Marks Report', 0, 1, 'C');

// Add current date and time
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Generated on: ' . date("Y-m-d H:i:s"), 0, 1, 'C');

$pdf->Ln(10); // Add a line break

// Define table headers
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(25, 10, 'Mark ID', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Student ID', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Student Name', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Course Name', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Year', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Completed', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Mark', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Status', 1, 1, 'C', true);

// Fetch marks data from the database
$sql = "SELECT mark.mid, mark.stuid, student.name AS student_name, course.name AS course_name, mark.year, mark.mark_status, mark.mark
        FROM mark
        JOIN student ON mark.stuid = student.stuid
        JOIN course ON mark.cid = course.cid
        ORDER BY mark.mid ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Set font for table content
    $pdf->SetFont('Arial', '', 12);
    // Loop through each row
    while ($row = $result->fetch_assoc()) {
        $completed = ($row['mark_status'] === 'Pass') ? 'Yes' : 'No';

        $pdf->Cell(25, 10, $row['mid'], 1, 0, 'C');
        $pdf->Cell(30, 10, $row['stuid'], 1, 0, 'C');
        $pdf->Cell(40, 10, $row['student_name'], 1, 0, 'C');
        $pdf->Cell(35, 10, $row['course_name'], 1, 0, 'C');
        $pdf->Cell(20, 10, $row['year'], 1, 0, 'C');
        $pdf->Cell(25, 10, $completed, 1, 0, 'C');
        $pdf->Cell(25, 10, $row['mark'], 1, 0, 'C');
        $pdf->Cell(20, 10, $row['mark_status'], 1, 1, 'C');
    }
} else {
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'No marks data available.', 1, 1, 'C');
}

// Output the PDF
$pdf->Output('D', 'Student_Marks_Report_' . date("Ymd_His") . '.pdf');

// Close the database connection
$conn->close();
?>
