<?php
require_once '../../config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($_POST['folder_name'])) {
            throw new Exception('Folder name is required');
        }

        $folder_name = trim($_POST['folder_name']);
        $parent_folder_id = !empty($_POST['parent_folder_id']) ? (int)$_POST['parent_folder_id'] : null;
        
        // Start transaction
        $conn->begin_transaction();
        
        // Insert the new folder
        if ($parent_folder_id) {
            $stmt = $conn->prepare("INSERT INTO folders (folder_name, parent_folder_id) VALUES (?, ?)");
            $stmt->bind_param("si", $folder_name, $parent_folder_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO folders (folder_name) VALUES (?)");
            $stmt->bind_param("s", $folder_name);
        }
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create folder: ' . $conn->error);
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Folder created successfully',
            'folder_id' => $conn->insert_id
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error creating folder: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close(); 