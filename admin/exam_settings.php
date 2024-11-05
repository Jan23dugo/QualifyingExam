<?php
include_once __DIR__ . '/../config/config.php';

$exam_id = $_GET['exam_id'] ?? null;
if (!$exam_id) {
    die("Exam ID is required.");
}

// Fetch exam details and settings
$stmt = $conn->prepare("
    SELECT e.*, es.*
    FROM exams e
    LEFT JOIN exam_settings es ON e.exam_id = es.exam_id
    WHERE e.exam_id = ?
");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();
$exam = $result->fetch_assoc();

// If settings don't exist, create default settings
if (!isset($exam['randomize_questions'])) {
    $conn->query("INSERT INTO exam_settings (exam_id) VALUES ($exam_id)");
    $exam = array_merge($exam, [
        'randomize_questions' => 0,
        'randomize_options' => 0,
        'allow_view_after' => 0,
        'time_limit' => null,
        'passing_score' => null,
        'show_results_immediately' => 0,
        'allow_retake' => 0,
        'max_attempts' => 1
    ]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Settings - <?php echo htmlspecialchars($exam['title'] ?? ''); ?></title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <style>
        .settings-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }

        .settings-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .setting-group {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .setting-group:last-child {
            border-bottom: none;
        }

        .form-switch {
            padding-left: 2.5em;
        }

        .setting-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .setting-description {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Exam Settings</h2>
            <a href="test2.php?exam_id=<?php echo $exam_id; ?>" class="btn btn-secondary">Back to Editor</a>
        </div>

        <form id="settingsForm" method="POST">
            <div class="settings-card">
                <h4 class="mb-4">Question Settings</h4>
                
                <div class="setting-group">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="randomizeQuestions" 
                               name="randomize_questions" <?php echo $exam['randomize_questions'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="randomizeQuestions">
                            <div class="setting-title">Randomize Question Order</div>
                            <div class="setting-description">Questions will be presented in random order for each student</div>
                        </label>
                    </div>
                </div>

                <div class="setting-group">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="randomizeOptions" 
                               name="randomize_options" <?php echo $exam['randomize_options'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="randomizeOptions">
                            <div class="setting-title">Randomize Answer Options</div>
                            <div class="setting-description">Multiple choice options will be presented in random order</div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <h4 class="mb-4">Time and Attempts</h4>
                
                <div class="setting-group">
                    <div class="setting-title">Time Limit</div>
                    <div class="setting-description">Set the time limit for completing the exam</div>
                    <div class="input-group" style="max-width: 200px;">
                        <input type="number" class="form-control" name="time_limit" 
                               value="<?php echo $exam['time_limit']; ?>" min="0">
                        <span class="input-group-text">minutes</span>
                    </div>
                </div>

                <div class="setting-group">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="allowRetake" 
                               name="allow_retake" <?php echo $exam['allow_retake'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="allowRetake">
                            <div class="setting-title">Allow Retakes</div>
                            <div class="setting-description">Students can retake the exam if they fail</div>
                        </label>
                    </div>
                    <div class="mt-2" id="maxAttemptsGroup" style="<?php echo $exam['allow_retake'] ? '' : 'display: none;'; ?>">
                        <label>Maximum Attempts:</label>
                        <input type="number" class="form-control" name="max_attempts" 
                               value="<?php echo $exam['max_attempts']; ?>" min="1" style="max-width: 100px;">
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <h4 class="mb-4">Results and Feedback</h4>
                
                <div class="setting-group">
                    <div class="setting-title">Passing Score</div>
                    <div class="setting-description">Minimum score required to pass the exam</div>
                    <div class="input-group" style="max-width: 200px;">
                        <input type="number" class="form-control" name="passing_score" 
                               value="<?php echo $exam['passing_score']; ?>" min="0" max="100">
                        <span class="input-group-text">%</span>
                    </div>
                </div>

                <div class="setting-group">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="showResults" 
                               name="show_results_immediately" <?php echo $exam['show_results_immediately'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="showResults">
                            <div class="setting-title">Show Results Immediately</div>
                            <div class="setting-description">Students can see their results right after submission</div>
                        </label>
                    </div>
                </div>

                <div class="setting-group">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="allowViewAfter" 
                               name="allow_view_after" <?php echo $exam['allow_view_after'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="allowViewAfter">
                            <div class="setting-title">Allow View After Completion</div>
                            <div class="setting-description">Students can review their answers after completing the exam</div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allowRetakeCheckbox = document.getElementById('allowRetake');
            const maxAttemptsGroup = document.getElementById('maxAttemptsGroup');

            allowRetakeCheckbox.addEventListener('change', function() {
                maxAttemptsGroup.style.display = this.checked ? 'block' : 'none';
            });

            document.getElementById('settingsForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('exam_id', <?php echo $exam_id; ?>);

                fetch('save_exam_settings.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Settings saved successfully!');
                    } else {
                        alert('Error saving settings: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving settings.');
                });
            });
        });
    </script>
</body>
</html> 