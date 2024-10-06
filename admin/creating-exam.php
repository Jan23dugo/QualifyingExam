<?php
// Project Plan: Exam Management System for Ladderized, Transferee, and Shiftee Students

// Features to be covered:
// 1. Admin Panel for managing exams (Create, Schedule, Monitor)
// 2. Student Panel for viewing reference numbers and exam schedules

// Below is a prototype of a basic backend functionality for the admin panel in PHP.

// Dependencies: PHP, MySQL

// Database Connection
include('config/config.php');

// SQL Commands to create the necessary tables
/*
CREATE TABLE Admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL
);

CREATE TABLE Exam (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_name VARCHAR(200) NOT NULL,
    exam_schedule DATETIME NOT NULL,
    max_attempts INT DEFAULT 1
);

CREATE TABLE StudentExamRecord (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(50) NOT NULL,
    exam_id INT NOT NULL,
    student_name VARCHAR(200) NOT NULL,
    status VARCHAR(50) DEFAULT 'Scheduled',
    FOREIGN KEY (exam_id) REFERENCES Exam(id)
);

CREATE TABLE ProgrammingQuestion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    question_text TEXT NOT NULL,
    difficulty VARCHAR(50),
    FOREIGN KEY (exam_id) REFERENCES Exam(id)
);
*/

// Admin Side Endpoints
// 1. Endpoint for Creating an Exam
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['action']) && $_GET['action'] == 'create_exam') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $exam_name = $data['exam_name'];
    $exam_schedule = $data['exam_schedule'];
    $max_attempts = isset($data['max_attempts']) ? $data['max_attempts'] : 1;
    
    $sql = "INSERT INTO Exam (exam_name, exam_schedule, max_attempts) VALUES ('$exam_name', '$exam_schedule', $max_attempts)";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Exam created successfully!"]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }
}

// 2. Endpoint for Adding a Programming Question
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['action']) && $_GET['action'] == 'add_question') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $exam_id = $data['exam_id'];
    $question_text = $data['question_text'];
    $difficulty = isset($data['difficulty']) ? $data['difficulty'] : 'Medium';
    
    $sql = "INSERT INTO ProgrammingQuestion (exam_id, question_text, difficulty) VALUES ($exam_id, '$question_text', '$difficulty')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Programming question added successfully!"]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }
}

// 3. Endpoint for Scheduling an Exam for Students
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['action']) && $_GET['action'] == 'schedule_exam') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $reference_number = $data['reference_number'];
    $student_name = $data['student_name'];
    $exam_id = $data['exam_id'];
    
    $sql = "INSERT INTO StudentExamRecord (reference_number, student_name, exam_id) VALUES ('$reference_number', '$student_name', $exam_id)";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "Student exam scheduled successfully!"]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }
}

// 4. Endpoint for Evaluating a Programming Question
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['action']) && $_GET['action'] == 'evaluate_code') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $student_code = $data['code'];
    $language_id = $data['language_id']; // Judge0 language ID (e.g., 54 for Python)
    
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.judge0.com/submissions/?base64_encoded=false&wait=true",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            "source_code" => $student_code,
            "language_id" => $language_id
        ]),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo json_encode(["error" => "cURL Error: $err"]);
    } else {
        echo $response;
    }
}

// 5. Endpoint for Monitoring Students Taking the Exam
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'monitor_exams') {
    $sql = "SELECT * FROM StudentExamRecord";
    $result = $conn->query($sql);

    $records = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $records[] = [
                "student_name" => $row['student_name'],
                "reference_number" => $row['reference_number'],
                "exam_id" => $row['exam_id'],
                "status" => $row['status']
            ];
        }
    }
    echo json_encode($records);
}

$conn->close();
?>