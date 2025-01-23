<?php
require_once '../../config/config.php';

function assignExamToStudents($exam_id) {
    global $conn;
    
    // Get exam details first
    $stmt = $conn->prepare("
        SELECT exam_id, student_type, student_year 
        FROM exams 
        WHERE exam_id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare exam details query: " . $conn->error);
    }

    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $exam = $stmt->get_result()->fetch_assoc();
    
    if (!$exam) {
        return false;
    }
    
    // Find eligible students - Using YEAR() function to extract year from registration_date
    $sql = "SELECT student_id 
            FROM students 
            WHERE student_type = ? 
            AND YEAR(registration_date) = ?";  // Changed to use YEAR() function
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $exam['student_type'], $exam['student_year']); // Changed back to "si" since student_year is integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Insert assignments
    while ($student = $result->fetch_assoc()) {
        $insert = $conn->prepare("
            INSERT INTO exam_assignments (exam_id, student_id, assigned_date) 
            VALUES (?, ?, NOW())
        ");
        $insert->bind_param("ii", $exam_id, $student['student_id']);
        $insert->execute();
    }
    
    return true;
} 