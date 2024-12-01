<?php
include_once '../config/config.php'; // Include database connection

$folder_id = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 0;

$response['exams'] = [];

if ($folder_id > 0) {
    // Fetch exams associated with the folder
    $exam_query = $conn->prepare("SELECT * FROM exams WHERE folder_id = ?");
    $exam_query->bind_param("i", $folder_id);
    $exam_query->execute();
    $result = $exam_query->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['exams'][] = $row;
    }
}

// Return JSON response=
header('Content-Type: application/json');
echo json_encode($response);
?>
