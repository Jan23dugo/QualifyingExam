<?php
require_once('../config/config.php');
session_start();

// Set header for JSON response
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate input
if (!isset($_POST['folder_name']) || empty(trim($_POST['folder_name']))) {
    echo json_encode(['success' => false, 'message' => 'Folder name is required']);
    exit;
}

try {
    // Sanitize inputs
    $folder_name = trim($_POST['folder_name']);
    $parent_folder_id = isset($_POST['parent_folder_id']) && !empty($_POST['parent_folder_id']) 
        ? (int)$_POST['parent_folder_id'] 
        : null;
    
    // Validate folder name length
    if (strlen($folder_name) > 255) {
        throw new Exception('Folder name is too long');
    }
    
    // If parent_folder_id is provided, verify it exists
    if ($parent_folder_id !== null) {
        $check_parent = $conn->prepare("SELECT folder_id FROM folders WHERE folder_id = ?");
        $check_parent->bind_param("i", $parent_folder_id);
        $check_parent->execute();
        if (!$check_parent->get_result()->num_rows) {
            throw new Exception('Parent folder does not exist');
        }
    }
    
    // Prepare the insert statement
    $stmt = $conn->prepare("INSERT INTO folders (folder_name, parent_folder_id) VALUES (?, ?)");
    $stmt->bind_param("si", $folder_name, $parent_folder_id);
    
    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $new_folder_id = $stmt->insert_id;
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Folder created successfully',
        'folder_id' => $new_folder_id,
        'redirect' => 'create-exam.php' . ($parent_folder_id ? "?folder_id=" . $parent_folder_id : "")
    ]);

} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit;
?>
