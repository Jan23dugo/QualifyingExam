<?php
// save_question.php

include_once __DIR__ . '/../config/config.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question = $_POST['question'];
    $choices = [];
    $correctAnswers = isset($_POST['correct_answer']) ? $_POST['correct_answer'] : [];

    // Collect choices
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'choice') === 0) {
            $choices[$key] = $value;
        }
    }

    // Prepare SQL insert query
    $sql = "INSERT INTO questions (question_text) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $question);

    if ($stmt->execute()) {
        $questionId = $stmt->insert_id; // Get the ID of the inserted question

        // Insert choices into the database
        foreach ($choices as $key => $value) {
            $isCorrect = in_array($key, $correctAnswers) ? 1 : 0;
            $choiceSql = "INSERT INTO choices (question_id, choice_text, is_correct) VALUES (?, ?, ?)";
            $choiceStmt = $conn->prepare($choiceSql);
            $choiceStmt->bind_param("isi", $questionId, $value, $isCorrect);
            $choiceStmt->execute();
        }

        echo "Question saved successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    
    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
