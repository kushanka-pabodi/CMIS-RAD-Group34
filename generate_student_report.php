<?php
// generate_student_report.php

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

// Get the Student ID from GET parameters
if (!isset($_GET['stuid']) || empty(trim($_GET['stuid']))) {
    die("Invalid Student ID.");
}

$stuid = trim($_GET['stuid']);

// Fetch student details
$stmt_student = $conn->prepare("SELECT stuid, name, cid FROM student WHERE stuid = ?");
if ($stmt_student === false) {
    die("Error preparing the statement: " . $conn->error);
}
$stmt_student->bind_param("s", $stuid);
$stmt_student->execute();
$result_student = $stmt_student->get_result();

if ($result_student->num_rows === 0) {
    die("Student not found.");
}

$student = $result_student->fetch_assoc();

// Fetch course name
$stmt_course = $conn->prepare("SELECT name FROM course WHERE cid = ?");
if ($stmt_course === false) {
    die("Error preparing the statement: " . $conn->error);
}
$stmt_course->bind_param("i", $student['cid']);
$stmt_course->execute();
$result_course = $stmt_course->get_result();
$course = $result_course->fetch_assoc();

// Fetch marks for the student
$stmt_marks = $conn->prepare("SELECT mark, mark_status, year FROM mark WHERE stuid = ?");
if ($stmt_marks === false) {
    die("Error preparing the statement: " . $conn->error);
}
$stmt_marks->bind_param("s", $stuid);
$stmt_marks->execute();
$result_marks = $stmt_marks->get_result();

// Initialize FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Set font for header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Student Report', 0, 1, 'C');

// Add current date
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Date: ' . date("Y-m-d"), 0, 1, 'C');

$pdf->Ln(10); // Line break

// Student Details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Student ID:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 10, $student['stuid'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Name:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 10, $student['name'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Course:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 10, $course['name'], 0, 1);

$pdf->Ln(10); // Line break

// Marks Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255);
$pdf->Cell(40, 10, 'Year', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Mark', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Status', 1, 1, 'C', true);

// Marks Data
$pdf->SetFont('Arial', '', 12);

if ($result_marks->num_rows > 0) {
    while ($mark = $result_marks->fetch_assoc()) {
        $year = htmlspecialchars($mark['year']);
        $mark_value = htmlspecialchars($mark['mark']);
        $status = htmlspecialchars($mark['mark_status']);
        
        $pdf->Cell(40, 10, $year, 1, 0, 'C');
        $pdf->Cell(40, 10, $mark_value, 1, 0, 'C');
        $pdf->Cell(50, 10, $status, 1, 1, 'C');
    }
} else {
    $pdf->Cell(130, 10, 'No marks data available.', 1, 1, 'C');
}

// Output the PDF
$pdf->Output('D', 'Student_Report_' . $stuid . '_' . date("Ymd_His") . '.pdf');

// Close statements and connection
$stmt_student->close();
$stmt_course->close();
$stmt_marks->close();
$conn->close();
?>
