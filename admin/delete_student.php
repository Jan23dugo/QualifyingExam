<?php
require_once('../config/config.php');

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$reference_id = $data['reference_id'] ?? '';

if (!empty($reference_id)) {
    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM students WHERE reference_id = ?");
    $stmt->bind_param("s", $reference_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid reference ID']);
}

$conn->close();
?>
