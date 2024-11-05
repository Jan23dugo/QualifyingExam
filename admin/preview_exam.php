<?php
include_once __DIR__ . '/../config/config.php';

$exam_id = $_GET['exam_id'] ?? null;
if (!$exam_id) {
    die("Exam ID is required.");
}

// Fetch exam details
$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

// Fetch sections and questions
$stmt = $conn->prepare("
    SELECT 
        es.*, 
        q.question_id, 
        q.question_text, 
        q.question_type,
        q.points
    FROM exam_sections es
    LEFT JOIN questions q ON es.section_id = q.section_id
    WHERE es.exam_id = ?
    ORDER BY es.section_order, q.question_order
");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();

$sections = [];
while ($row = $result->fetch_assoc()) {
    $section_id = $row['section_id'];
    if (!isset($sections[$section_id])) {
        $sections[$section_id] = [
            'section_id' => $section_id,
            'title' => $row['section_title'],
            'description' => $row['section_description'],
            'questions' => []
        ];
    }
    if ($row['question_id']) {
        $question = [
            'question_id' => $row['question_id'],
            'text' => $row['question_text'],
            'type' => $row['question_type'],
            'points' => $row['points']
        ];

        // Fetch options for multiple choice and true/false questions
        if (in_array($row['question_type'], ['multiple_choice', 'true_false'])) {
            $opt_stmt = $conn->prepare("SELECT * FROM multiple_choice_options WHERE question_id = ? ORDER BY option_order");
            $opt_stmt->bind_param("i", $row['question_id']);
            $opt_stmt->execute();
            $question['options'] = $opt_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        // Fetch test cases for programming questions
        if ($row['question_type'] === 'programming') {
            $test_stmt = $conn->prepare("SELECT * FROM test_cases WHERE question_id = ? ORDER BY test_case_order");
            $test_stmt->bind_param("i", $row['question_id']);
            $test_stmt->execute();
            $question['test_cases'] = $test_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Fetch programming language
            $lang_stmt = $conn->prepare("SELECT language_name FROM programming_languages WHERE question_id = ?");
            $lang_stmt->bind_param("i", $row['question_id']);
            $lang_stmt->execute();
            $lang_result = $lang_stmt->get_result()->fetch_assoc();
            $question['programming_language'] = $lang_result['language_name'] ?? null;
        }

        $sections[$section_id]['questions'][] = $question;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Exam - <?php echo htmlspecialchars($exam['title'] ?? ''); ?></title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <style>
        .exam-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .question {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .points {
            color: #6200ea;
            font-weight: bold;
        }

        .options {
            margin-top: 10px;
        }

        .option {
            margin: 5px 0;
        }

        .programming-section {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }

        .test-case {
            background: white;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }

        .nav-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }

        .code-editor {
            width: 100%;
            min-height: 200px;
            font-family: monospace;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="nav-buttons">
        <a href="test2.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-secondary">Back to Editor</a>
        <button class="btn btn-primary" onclick="window.print()">Print Preview</button>
    </div>

    <div class="exam-container">
        <h1 class="text-center mb-4"><?php echo htmlspecialchars($exam['title'] ?? ''); ?></h1>
        
        <?php foreach ($sections as $section): ?>
            <div class="section">
                <h3><?php echo htmlspecialchars($section['title']); ?></h3>
                <?php if ($section['description']): ?>
                    <p class="text-muted"><?php echo htmlspecialchars($section['description']); ?></p>
                <?php endif; ?>

                <?php foreach ($section['questions'] as $index => $question): ?>
                    <div class="question">
                        <div class="question-header">
                            <div class="question-number">Question <?php echo $index + 1; ?></div>
                            <div class="points"><?php echo $question['points']; ?> points</div>
                        </div>
                        <div class="question-text"><?php echo htmlspecialchars($question['text']); ?></div>

                        <?php if ($question['type'] === 'multiple_choice'): ?>
                            <div class="options">
                                <?php foreach ($question['options'] as $option): ?>
                                    <div class="option">
                                        <input type="radio" name="q_<?php echo $question['question_id']; ?>" 
                                               id="opt_<?php echo $option['option_id']; ?>">
                                        <label for="opt_<?php echo $option['option_id']; ?>">
                                            <?php echo htmlspecialchars($option['option_text']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($question['type'] === 'true_false'): ?>
                            <div class="options">
                                <div class="option">
                                    <input type="radio" name="q_<?php echo $question['question_id']; ?>" id="true_<?php echo $question['question_id']; ?>">
                                    <label for="true_<?php echo $question['question_id']; ?>">True</label>
                                </div>
                                <div class="option">
                                    <input type="radio" name="q_<?php echo $question['question_id']; ?>" id="false_<?php echo $question['question_id']; ?>">
                                    <label for="false_<?php echo $question['question_id']; ?>">False</label>
                                </div>
                            </div>

                        <?php elseif ($question['type'] === 'programming'): ?>
                            <div class="programming-section">
                                <div>Programming Language: <?php echo htmlspecialchars($question['programming_language']); ?></div>
                                <textarea class="code-editor" placeholder="Write your code here..."></textarea>
                                
                                <h5 class="mt-3">Test Cases:</h5>
                                <?php foreach ($question['test_cases'] as $test_case): ?>
                                    <div class="test-case">
                                        <div><strong>Input:</strong> <?php echo htmlspecialchars($test_case['input_data']); ?></div>
                                        <div><strong>Expected Output:</strong> <?php echo htmlspecialchars($test_case['expected_output']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
