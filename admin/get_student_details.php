<?php
require_once('../config/config.php');

if (isset($_GET['ref'])) {
    $ref_id = $_GET['ref'];
    
    $stmt = $conn->prepare("SELECT * FROM students WHERE reference_id = ?");
    $stmt->bind_param("s", $ref_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo json_encode($student);
    } else {
        echo json_encode(null);
    }
    
    $stmt->close();
} else {
    echo json_encode(['error' => 'No reference ID provided']);
}

$conn->close(); 