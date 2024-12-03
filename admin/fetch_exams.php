<?php
require_once '../config/config.php';

$folder_id = isset($_GET['folder_id']) ? $_GET['folder_id'] : null;

if ($folder_id) {
    $stmt = $conn->prepare("SELECT * FROM exams WHERE folder_id = ?");
    $stmt->bind_param("i", $folder_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $exams = array();
    while ($row = $result->fetch_assoc()) {
        $exams[] = $row;
    }
    
    echo json_encode(['success' => true, 'exams' => $exams]);
} else {
    echo json_encode(['success' => false, 'message' => 'No folder ID provided']);
}
?>
