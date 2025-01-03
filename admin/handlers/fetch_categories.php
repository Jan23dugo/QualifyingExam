<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

try {
    // Get categories with their question counts
    $sql = "SELECT category, COUNT(*) as question_count 
            FROM question_bank 
            WHERE category IS NOT NULL AND category != '' 
            GROUP BY category 
            ORDER BY category";
    $result = $conn->query($sql);
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'name' => $row['category'],
            'count' => (int)$row['question_count']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 