<?php
include_once('../config/config.php');

$exam_id = $_POST['exam_id'];
$folder_id = $_POST['folder_id'];

$sql = "INSERT INTO exams (exam_name, description, duration, schedule_date, folder_id)
        SELECT exam_name, description, duration, schedule_date, ? FROM exams WHERE exam_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $folder_id, $exam_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Exam copied successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to copy exam."]);
}
?>
