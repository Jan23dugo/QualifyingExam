<?php
include_once('../config/config.php');

$folder_name = $_POST['folder_name'];
$parent_folder_id = isset($_POST['parent_folder_id']) ? $_POST['parent_folder_id'] : null;

if ($parent_folder_id) {
    $stmt = $conn->prepare("INSERT INTO folders (folder_name, parent_folder_id) VALUES (?, ?)");
    $stmt->bind_param("si", $folder_name, $parent_folder_id);
} else {
    $stmt = $conn->prepare("INSERT INTO folders (folder_name, parent_folder_id) VALUES (?, NULL)");
    $stmt->bind_param("s", $folder_name);
}

if (!$stmt->execute()) {
    die("Error creating folder: " . $conn->error);
}

header("Location: create-exam.php" . ($parent_folder_id ? "?folder_id=" . $parent_folder_id : ""));
exit();
?>
