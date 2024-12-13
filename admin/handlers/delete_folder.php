<?php
require_once '../../config/config.php';
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $folder_id = isset($_POST['folder_id']) ? (int)$_POST['folder_id'] : 0;
    
    if (!$folder_id) {
        die(json_encode(['success' => false, 'message' => 'Invalid folder ID']));
    }

    try {
        // First check if folder exists
        $check_stmt = $conn->prepare("SELECT folder_id FROM folders WHERE folder_id = ?");
        $check_stmt->bind_param("i", $folder_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            die(json_encode(['success' => false, 'message' => 'Folder not found']));
        }

        // Start transaction
        $conn->begin_transaction();

        // Get all subfolders
        function getAllSubfolders($conn, $folder_id) {
            $folders = [$folder_id];
            $stmt = $conn->prepare("SELECT folder_id FROM folders WHERE parent_folder_id = ?");
            $stmt->bind_param("i", $folder_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $folders = array_merge($folders, getAllSubfolders($conn, $row['folder_id']));
            }
            
            return $folders;
        }

        $folders_to_delete = getAllSubfolders($conn, $folder_id);
        
        if (!empty($folders_to_delete)) {
            // Update exams in these folders to have no folder (move to root)
            $folder_list = implode(',', $folders_to_delete);
            $update_result = $conn->query("UPDATE exams SET folder_id = NULL WHERE folder_id IN ($folder_list)");
            
            if ($update_result === false) {
                throw new Exception("Error updating exams: " . $conn->error);
            }

            // Delete the folders
            $delete_result = $conn->query("DELETE FROM folders WHERE folder_id IN ($folder_list)");
            
            if ($delete_result === false) {
                throw new Exception("Error deleting folders: " . $conn->error);
            }
        }

        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Error in delete_folder.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
} 