<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

try {
    // Check if required data is present
    if (!isset($_POST['folder_id']) || !isset($_POST['folder_name'])) {
        throw new Exception('Missing required data');
    }

    $folderId = $_POST['folder_id'];
    $folderName = trim($_POST['folder_name']);

    // Validate folder name
    if (empty($folderName)) {
        throw new Exception('Folder name cannot be empty');
    }

    // Check if folder exists
    $checkStmt = $conn->prepare("SELECT folder_id FROM folders WHERE folder_id = ?");
    $checkStmt->bind_param("i", $folderId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Folder not found');
    }

    // Check if new name already exists in the same level
    $checkNameStmt = $conn->prepare("SELECT folder_id FROM folders WHERE folder_name = ? AND folder_id != ? AND (parent_folder_id IS NULL OR parent_folder_id = (SELECT parent_folder_id FROM folders WHERE folder_id = ?))");
    $checkNameStmt->bind_param("sii", $folderName, $folderId, $folderId);
    $checkNameStmt->execute();
    
    if ($checkNameStmt->get_result()->num_rows > 0) {
        throw new Exception('A folder with this name already exists at this level');
    }

    // Update folder name
    $updateStmt = $conn->prepare("UPDATE folders SET folder_name = ? WHERE folder_id = ?");
    $updateStmt->bind_param("si", $folderName, $folderId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Failed to update folder');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Folder updated successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close(); 