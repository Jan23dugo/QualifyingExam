<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

try {
    // Debug log
    error_log("get_categories.php called");
    
    // Query to get distinct categories from the question_bank table
    $sql = "SELECT DISTINCT category FROM question_bank WHERE category IS NOT NULL AND category != '' ORDER BY category";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    // Debug log
    error_log("Query executed successfully");
    
    $categories = [];
    while($row = $result->fetch_assoc()) {
        if (!empty($row['category'])) {
            $categories[] = $row['category'];
        }
    }
    
    // Debug log
    error_log("Found categories: " . json_encode($categories));
    
    // Send response
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_categories.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => $e->getMessage()
    ]);
}

$conn->close(); 