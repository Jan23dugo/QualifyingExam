<?php
require_once '../../config/config.php';

function assignExamToStudents($exam_id) {
    global $conn;
    
    // Get exam details
    $stmt = $conn->prepare("
        SELECT student_type, year 
        FROM exams 
        WHERE exam_id = ?
    ");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $exam = $stmt->get_result()->fetch_assoc();
    
    if (!$exam) {
        return false;
    }
    
    // Convert student_type to is_tech value (0 for non-tech, 1 for tech)
    $is_tech = ($exam['student_type'] === 'tech') ? 1 : 0;
    
    // Find eligible students based on is_tech and registration year
    $student_query = "
        SELECT student_id 
        FROM students 
        WHERE is_tech = ?
        AND (? IS NULL OR YEAR(registration_date) = ?)
    ";
    
    $stmt = $conn->prepare($student_query);
    $stmt->bind_param("iii", $is_tech, $exam['year'], $exam['year']);
    $stmt->execute();
    $eligible_students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Assign exam to eligible students
    $assign_stmt = $conn->prepare("
        INSERT INTO exam_assignments (exam_id, student_id) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE exam_id = exam_id
    ");
    
    foreach ($eligible_students as $student) {
        $assign_stmt->bind_param("ii", $exam_id, $student['student_id']);
        $assign_stmt->execute();
    }
    
    return true;
} 