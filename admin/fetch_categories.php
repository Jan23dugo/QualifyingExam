<?php
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    $query = "SELECT DISTINCT category FROM question_bank WHERE category IS NOT NULL ORDER BY category";
    $result = $conn->query($query);
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'categories' => []
    ]);
}
?> 