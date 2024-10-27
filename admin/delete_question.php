<?php
include_once('../config/config.php');

$question_id = $_GET['question_id'];
$exam_id = $_GET['exam_id'];

$sql = "DELETE FROM questions WHERE question_id = $question_id";
$conn->query($sql);

header("Location: test2.php?exam_id=$exam_id");
