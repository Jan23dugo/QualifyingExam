<?php
include('../../config/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && isset($_POST['category'])) {
        $category = $conn->real_escape_string($_POST['category']);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete all questions in the category
            $sql = "DELETE FROM question_bank WHERE category = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $category);
            $stmt->execute();
            
            $conn->commit();
            echo json_encode([
                'status' => 'success',
                'message' => "Category '$category' has been successfully deleted.",
                'messageId' => uniqid('msg_')
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'status' => 'error', 
                'message' => 'Failed to delete category'
            ]);
        }
    }
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid request'
    ]);
} 