<?php
require_once '../config/config.php';
require_once 'judge0_handler.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['code']) || !isset($input['language_id'])) {
        throw new Exception('Missing required parameters');
    }
    
    $judge0 = new Judge0Handler();
    $result = $judge0->submitCode($input['code'], $input['language_id']);
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} 