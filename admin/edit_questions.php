<?php
include_once('../config/config.php');
$question_id = $_GET['question_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_text = $_POST['question_text'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_answer = $_POST['correct_answer'];

    $sql = "UPDATE questions SET question_text='$question_text', option_a='$option_a', option_b='$option_b', 
            option_c='$option_c', option_d='$option_d', correct_answer='$correct_answer' WHERE question_id=$question_id";
    $conn->query($sql);

    header("Location: test2.php?exam_id=" . $_GET['exam_id']);
}

// Fetch the question to edit
$sql = "SELECT * FROM questions WHERE question_id = $question_id";
$question = $conn->query($sql)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question</title>
</head>
<body>
<h2>Edit Question</h2>
<form action="" method="POST">
    <div>
        <label>Question Text:</label>
        <input type="text" name="question_text" value="<?php echo $question['question_text']; ?>" required>
    </div>
    <div>
        <label>Option A:</label>
        <input type="text" name="option_a" value="<?php echo $question['option_a']; ?>" required>
    </div>
    <div>
        <label>Option B:</label>
        <input type="text" name="option_b" value="<?php echo $question['option_b']; ?>" required>
    </div>
    <div>
        <label>Option C:</label>
        <input type="text" name="option_c" value="<?php echo $question['option_c']; ?>" required>
    </div>
    <div>
        <label>Option D:</label>
        <input type="text" name="option_d" value="<?php echo $question['option_d']; ?>" required>
    </div>
    <div>
        <label>Correct Answer (A, B, C, D):</label>
        <input type="text" name="correct_answer" value="<?php echo $question['correct_answer']; ?>" required>
    </div>
    <div>
        <button type="submit">Update Question</button>
    </div>
</form>
</body>
</html>
