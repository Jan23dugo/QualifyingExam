<?php
include_once('../config/config.php');

$exam_id = $_POST['exam_id'];
$folder_id = $_POST['folder_id'];

$sql = "UPDATE exams SET folder_id = ? WHERE exam_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $folder_id, $exam_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Exam moved successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to move exam."]);
}
?>
