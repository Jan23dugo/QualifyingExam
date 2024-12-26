<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../config/config.php';

try {
    // Add error logging
    error_log("Fetching categories from database");
    
    // Check connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Changed the query to use question_bank table instead of questions
    $query = "SELECT DISTINCT category FROM question_bank WHERE category IS NOT NULL AND category != '' ORDER BY category";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    
    error_log("Found " . count($categories) . " categories");
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);

} catch (Exception $e) {
    error_log("Error in fetch_categories.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close(); 