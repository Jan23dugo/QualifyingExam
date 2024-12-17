<?php
include_once __DIR__ . '/../config/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$exam_id = $_GET['exam_id'] ?? null;
if (!$exam_id) {
    die("Exam ID is required.");
}

// Add debug logging
function debug_log($message) {
    error_log(print_r($message, true));
}

// Fetch exam details
$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam = $stmt->get_result()->fetch_assoc();

if (!$exam) {
    die("Exam not found.");
}

// Debug log the data
debug_log("Exam data: " . print_r($exam, true));

// Fetch sections and questions
$stmt = $conn->prepare("SELECT * FROM sections WHERE exam_id = ? ORDER BY section_order");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$sections_result = $stmt->get_result();

$sections = [];
while ($section = $sections_result->fetch_assoc()) {
    // Fetch questions for this section
    $questions_stmt = $conn->prepare("
        SELECT * FROM questions 
        WHERE section_id = ? 
        ORDER BY question_order
    ");
    $questions_stmt->bind_param("i", $section['section_id']);
    $questions_stmt->execute();
    $questions_result = $questions_stmt->get_result();
    
    $questions = [];
    while ($question = $questions_result->fetch_assoc()) {
        // Add question type specific data
        if ($question['question_type'] === 'multiple_choice') {
            $options_stmt = $conn->prepare("
                SELECT 
                    option_id,
                    question_id,
                    option_text,
                    is_correct
                FROM multiple_choice_options 
                WHERE question_id = ?
                ORDER BY option_id
            ");
            $options_stmt->bind_param("i", $question['question_id']);
            $options_stmt->execute();
            $options_result = $options_stmt->get_result();
            $question['options'] = $options_result->fetch_all(MYSQLI_ASSOC);
            debug_log("Multiple choice options: " . print_r($question['options'], true));
        } else if ($question['question_type'] === 'programming') {
            // Get programming language
            $lang_stmt = $conn->prepare("SELECT programming_language FROM questions WHERE question_id = ?");
            $lang_stmt->bind_param("i", $question['question_id']);
            $lang_stmt->execute();
            $lang_result = $lang_stmt->get_result()->fetch_assoc();
            $question['programming_language'] = $lang_result['programming_language'] ?? 'python';

            // Get test cases
            $test_cases_stmt = $conn->prepare("
                SELECT 
                    test_case_id,
                    question_id,
                    test_input,
                    expected_output,
                    is_hidden,
                    description
                FROM test_cases 
                WHERE question_id = ? 
                ORDER BY test_case_id
            ");
            $test_cases_stmt->bind_param("i", $question['question_id']);
            $test_cases_stmt->execute();
            $question['test_cases'] = $test_cases_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            debug_log("Programming test cases: " . print_r($question['test_cases'], true));
        }
        $questions[] = $question;
    }
    
    $section['questions'] = $questions;
    $sections[] = $section;
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
        /* Main container styling */
        .exam-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
        }

        /* Section styling */
        .section {
            background: linear-gradient(to right bottom, #ffffff, #fafafa);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid #e8e8e8;
        }

        /* Question styling */
        .question {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            border: 1px solid #eaeaea;
            transition: all 0.3s ease;
        }

        .question:hover {
            background: linear-gradient(to right, #ffffff, #fafafa);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            border-color: #d8d8d8;
        }

        /* Programming section styling */
        .programming-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
            border: 1px solid #e9ecef;
        }

        .test-case {
            background: linear-gradient(to right, #ffffff, #fafafa);
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .test-case.hidden-case {
            background-color: rgba(255, 193, 7, 0.05);
            border: 1px solid rgba(255, 193, 7, 0.2);
        }

        .test-case.hidden-case .hidden-label {
            color: #856404;
            background-color: #fff3cd;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.875rem;
            margin-bottom: 8px;
            display: inline-block;
        }

        /* Code editor styling */
        .code-editor {
            width: 100%;
            min-height: 200px;
            font-family: monospace;
            padding: 15px;
            border: 1px solid #e2e2e2;
            border-radius: 6px;
            margin-top: 10px;
            background-color: #ffffff;
            transition: all 0.3s ease;
        }

        .code-editor:focus {
            border-color: #6200ea;
            box-shadow: 0 0 0 3px rgba(98, 0, 234, 0.1);
            outline: none;
        }

        /* Navigation buttons */
        .nav-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .nav-buttons .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-buttons .btn-primary {
            background: linear-gradient(45deg, #6200ea, #7c4dff);
            border: none;
        }

        .nav-buttons .btn-primary:hover {
            background: linear-gradient(45deg, #5000d6, #6e3fff);
            transform: translateY(-1px);
        }

        /* Add some depth to the page */
        body {
            background-color: #f0f2f5;
            padding: 20px;
        }

        /* Option styling */
        .option {
            margin: 10px 0;
            padding: 8px 12px;
            border-radius: 4px;
            background-color: #fff;
            border: 1px solid #e0e0e0;
            transition: all 0.2s ease;
        }

        .option:hover {
            background-color: #f8f9fa;
            border-color: #d0d0d0;
        }

        .option input[type="radio"] {
            margin-right: 10px;
        }

        /* Programming section improvements */
        .programming-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
            border: 1px solid #e9ecef;
        }

        .badge {
            font-size: 0.9em;
            padding: 6px 12px;
        }

        .code-editor {
            font-family: 'Consolas', 'Monaco', monospace;
            background-color: #282c34;
            color: #abb2bf;
            padding: 15px;
            border-radius: 6px;
            border: none;
            resize: vertical;
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
                        <div class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></div>

                        <?php if ($question['question_type'] === 'multiple_choice'): ?>
                            <div class="options">
                                <?php foreach ($question['options'] as $option): ?>
                                    <div class="option">
                                        <input type="radio" 
                                               name="q_<?php echo $question['question_id']; ?>" 
                                               id="opt_<?php echo $option['option_id']; ?>"
                                               >
                                        <label for="opt_<?php echo $option['option_id']; ?>">
                                            <?php echo htmlspecialchars($option['option_text']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($question['question_type'] === 'true_false'): ?>
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

                        <?php elseif ($question['question_type'] === 'programming'): ?>
                            <div class="programming-section">
                                <div class="mb-3">
                                    <strong>Programming Language:</strong> 
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($question['programming_language']); ?></span>
                                </div>
                                <textarea class="code-editor" placeholder="Write your code here..."></textarea>
                                
                                <h5 class="mt-3">Test Cases:</h5>
                                <?php if (!empty($question['test_cases'])): ?>
                                    <?php foreach ($question['test_cases'] as $test_case): ?>
                                        <div class="test-case <?php echo $test_case['is_hidden'] ? 'hidden-case' : ''; ?>">
                                            <?php if ($test_case['is_hidden']): ?>
                                                <span class="hidden-label">Hidden Test Case</span>
                                                <?php if ($test_case['description']): ?>
                                                    <div class="description mb-2"><?php echo htmlspecialchars($test_case['description']); ?></div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <div><strong>Input:</strong> <?php echo htmlspecialchars($test_case['test_input']); ?></div>
                                            <div><strong>Expected Output:</strong> <?php echo htmlspecialchars($test_case['expected_output']); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="alert alert-info">No test cases available for this question.</div>
                                <?php endif; ?>
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
