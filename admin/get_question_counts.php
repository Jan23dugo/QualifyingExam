<?php
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    
    $query = "SELECT question_type, COUNT(*) as count FROM question_bank";
    if (!empty($category)) {
        $query .= " WHERE category = ?";
    }
    $query .= " GROUP BY question_type";
    
    $stmt = $conn->prepare($query);
    if (!empty($category)) {
        $stmt->bind_param('s', $category);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $counts = [
        'multiple_choice' => 0,
        'true_false' => 0,
        'programming' => 0
    ];
    
    while ($row = $result->fetch_assoc()) {
        $counts[$row['question_type']] = (int)$row['count'];
    }
    
    echo json_encode([
        'success' => true,
        'counts' => $counts
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'counts' => []
    ]);
}
?> 