<?php
require_once '../../config/config.php';
header('Content-Type: application/json');

try {
    $category = $_GET['category'] ?? '';
    $types = isset($_GET['types']) ? explode(',', $_GET['types']) : [];
    
    $counts = [
        'multiple_choice' => 0,
        'true_false' => 0,
        'programming' => 0
    ];
    
    foreach ($types as $type) {
        $sql = "SELECT COUNT(*) as count FROM question_bank WHERE question_type = ?";
        $params = [$type];
        
        if (!empty($category)) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $counts[$type] = (int)$row['count'];
    }
    
    echo json_encode([
        'success' => true,
        'counts' => $counts
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 