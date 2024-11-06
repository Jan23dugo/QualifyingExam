<?php
ob_start(); // Start output buffering
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug output
$output = ob_get_contents();
if (!empty($output)) {
    file_put_contents(__DIR__ . '/debug.log', $output);
}
ob_clean();

// Check for any output or errors
if (headers_sent($filename, $linenum)) {
    echo "Headers already sent in $filename on line $linenum\n";
    exit;
}

require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
require_once __DIR__ . '/../config/config.php';

try {
    // Get exam_id from query string
    $exam_id = $_GET['exam_id'] ?? null;

    if (!$exam_id) {
        throw new Exception("Exam ID is required.");
    }

    // Fetch exam details
    $stmt = $conn->prepare("SELECT exam_name FROM exams WHERE exam_id = ?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $exam = $stmt->get_result()->fetch_assoc();

    // Fetch exam results
    $results_query = "
        SELECT 
            s.student_id,
            s.first_name,
            s.last_name,
            s.reference_id,
            CASE 
                WHEN s.is_tech = 1 THEN 'Tech Track'
                ELSE 'Non-Tech Track'
            END as track_name,
            er.score,
            er.total_points,
            er.completion_time,
            er.status
        FROM exam_results er
        JOIN students s ON er.student_id = s.student_id
        WHERE er.exam_id = ?
        ORDER BY er.score DESC";

    $results_stmt = $conn->prepare($results_query);
    $results_stmt->bind_param("i", $exam_id);
    $results_stmt->execute();
    $results = $results_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Clear any output that might have been generated
    ob_clean();

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Exam System');
    $pdf->SetTitle('Exam Results Report');

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Add title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'Exam Results: ' . $exam['exam_name'], 0, 1, 'C');
    $pdf->Ln(10);

    // Add table header
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(40, 7, 'Student ID', 1);
    $pdf->Cell(60, 7, 'Name', 1);
    $pdf->Cell(30, 7, 'Track', 1);
    $pdf->Cell(30, 7, 'Score', 1);
    $pdf->Cell(30, 7, 'Percentage', 1);
    $pdf->Ln();

    // Add table data
    $pdf->SetFont('helvetica', '', 12);
    foreach ($results as $result) {
        $percentage = ($result['score'] / $result['total_points']) * 100;
        
        $pdf->Cell(40, 7, $result['student_id'], 1);
        $pdf->Cell(60, 7, $result['first_name'] . ' ' . $result['last_name'], 1);
        $pdf->Cell(30, 7, $result['track_name'], 1);
        $pdf->Cell(30, 7, $result['score'] . '/' . $result['total_points'], 1);
        $pdf->Cell(30, 7, number_format($percentage, 1) . '%', 1);
        $pdf->Ln();
    }

    // Output the PDF
    ob_end_clean(); // Clean output buffer before sending the file
    $pdf->Output('exam_results_' . $exam_id . '.pdf', 'D');
    exit;
} catch (Exception $e) {
    ob_end_clean();
    echo "Error: " . $e->getMessage();
}