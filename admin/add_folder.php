<?php
include_once('../config/config.php');

$folder_name = $_POST['folder_name'];
$sql = "INSERT INTO folders (folder_name) VALUES ('$folder_name')";
$conn->query($sql);
header("Location: create-exam.php");
?>
