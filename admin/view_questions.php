<?php
include('../config/config.php');
//this  fileis  forviewingquestions
// Add this helper function
function include_test_cases($question_id, $conn) {
    // Get visible test cases
    $testCasesSql = "SELECT test_input, expected_output, is_hidden 
                     FROM question_bank_test_cases 
                     WHERE question_id = ? 
                     ORDER BY id";
    $testCasesStmt = $conn->prepare($testCasesSql);
    $testCasesStmt->bind_param("i", $question_id);
    $testCasesStmt->execute();
    $testCasesResult = $testCasesStmt->get_result();
    
    echo '<div class="mt-3">';
    echo '<p><strong>Test Cases:</strong></p>';
    
    if ($testCasesResult->num_rows > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>Input</th><th>Expected Output</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        while ($testCase = $testCasesResult->fetch_assoc()) {
            if (!$testCase['is_hidden']) {
                echo '<tr>';
                echo '<td><pre>' . htmlspecialchars($testCase['test_input']) . '</pre></td>';
                echo '<td><pre>' . htmlspecialchars($testCase['expected_output']) . '</pre></td>';
                echo '<td>Visible</td>';
                echo '</tr>';
            }
        }
        echo '</tbody></table>';
        echo '</div>';
    } else {
        echo '<p class="text-muted">No test cases available.</p>';
    }
    
    // Get count of hidden test cases
    $hiddenCasesSql = "SELECT COUNT(*) as count 
                       FROM question_bank_test_cases 
                       WHERE question_id = ? AND is_hidden = 1";
    $hiddenCasesStmt = $conn->prepare($hiddenCasesSql);
    $hiddenCasesStmt->bind_param("i", $question_id);
    $hiddenCasesStmt->execute();
    $hiddenCount = $hiddenCasesStmt->get_result()->fetch_assoc()['count'];
    
    if ($hiddenCount > 0) {
        echo '<p class="text-muted mt-2"><i class="fas fa-lock"></i> ' . $hiddenCount . ' hidden test case(s)</p>';
    }
    
    echo '</div>'; // End test cases div
}

// Get category from URL and validate
if (!isset($_GET['category'])) {
    header('Location: question_bank.php');
    exit;
}
$category = $_GET['category'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo htmlspecialchars($category); ?> Questions - Brand</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <style>
        /* Common Modal Styles */
        .modal-dialog {
            max-width: 500px;
        }
        
        .modal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .modal .modal-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 12px 12px 0 0;
            padding: 1rem 1.5rem;
        }
        
        .modal .modal-title {
            color: #1f2937;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .modal .modal-body {
            padding: 1.5rem;
            color: #4b5563;
            font-size: 0.95rem;
        }
        
        .modal .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 1rem 1.5rem;
            background-color: #f8fafc;
            border-radius: 0 0 12px 12px;
        }
        
        .modal .btn {
            padding: 0.5rem 1.25rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .modal .btn-secondary {
            background-color: #f3f4f6;
            border-color: #e5e7eb;
            color: #4b5563;
        }
        
        .modal .btn-secondary:hover {
            background-color: #e5e7eb;
            border-color: #d1d5db;
            color: #374151;
        }
        
        .modal .btn-danger {
            background-color: #ef4444;
            border-color: #ef4444;
        }
        
        .modal .btn-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
        }
        
        .modal .btn-primary {
            background-color: #6200ea;
            border-color: #6200ea;
        }
        
        .modal .btn-primary:hover {
            background-color: #5000c9;
            border-color: #5000c9;
        }
        
        /* Modal Icons */
        .modal .modal-body i {
            font-size: 24px;
            margin-right: 1rem;
        }
        
        .modal .warning-icon {
            color: #f59e0b;
        }
        
        .modal .delete-icon {
            color: #ef4444;
        }
        
        .modal .info-icon {
            color: #6200ea;
        }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Include Sidebar -->
        <?php include 'sidebar.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Include Topbar -->
                <?php include 'topbar.php'; ?>

                <!-- Main Content -->
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="text-dark mb-0"><?php echo htmlspecialchars($category); ?> Questions</h3>
                        <a href="question_bank.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Categories
                        </a>
                    </div>

                    <!-- Filter Section -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <select class="form-control" id="typeFilter">
                                        <option value="">All Question Types</option>
                                        <option value="multiple_choice">Multiple Choice</option>
                                        <option value="true_false">True/False</option>
                                        <option value="essay">Essay</option>
                                        <option value="programming">Programming</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search questions...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Questions List -->
                    <div class="card shadow">
                        <div class="card-body">
                            <?php
                            // Replace the existing SQL query with this simplified version
                            $sql = "SELECT DISTINCT qb.*, qbp.programming_language
                                    FROM question_bank qb
                                    LEFT JOIN question_bank_programming qbp ON qb.question_id = qbp.question_id
                                    WHERE qb.category = ?
                                    ORDER BY qb.question_id";
                            
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("s", $category);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    ?>
                                    <div class="question-card mb-4 p-3 border rounded question-item" 
                                         data-type="<?php echo htmlspecialchars($row['question_type']); ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="question-content">
                                                <div class="mb-2">
                                                    <span class="badge bg-primary">
                                                        <?php echo ucfirst(str_replace('_', ' ', $row['question_type'])); ?>
                                                    </span>
                                                    <small class="text-muted ms-2">
                                                        Last updated: <?php echo date('Y-m-d H:i', strtotime($row['updated_at'])); ?>
                                                    </small>
                                                </div>
                                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($row['question_text'])); ?></p>
                                                
                                                <?php 
                                                // Handle different question types
                                                switch($row['question_type']) {
                                                    case 'multiple_choice':
                                                        // Get choices for this question
                                                        $choicesSql = "SELECT choice_text, is_correct 
                                                             FROM question_bank_choices 
                                                             WHERE question_id = ?";
                                                        $choicesStmt = $conn->prepare($choicesSql);
                                                        $choicesStmt->bind_param("i", $row['question_id']);
                                                        $choicesStmt->execute();
                                                        $choicesResult = $choicesStmt->get_result();
                                                        ?>
                                                        <div class="options-list ms-3">
                                                            <?php 
                                                            while($choice = $choicesResult->fetch_assoc()) {
                                                                echo '<div class="option ' . ($choice['is_correct'] ? 'text-success' : '') . '">';
                                                                echo ($choice['is_correct'] ? '<i class="fas fa-check-circle"></i> ' : '<i class="far fa-circle"></i> ');
                                                                echo htmlspecialchars($choice['choice_text']);
                                                                echo '</div>';
                                                            }
                                                            ?>
                                                        </div>
                                                        <?php
                                                        break;

                                                    case 'true_false':
                                                        // Get true/false choices
                                                        $tfSql = "SELECT choice_text, is_correct 
                                                             FROM question_bank_choices 
                                                             WHERE question_id = ?";
                                                        $tfStmt = $conn->prepare($tfSql);
                                                        $tfStmt->bind_param("i", $row['question_id']);
                                                        $tfStmt->execute();
                                                        $tfResult = $tfStmt->get_result();
                                                        ?>
                                                        <div class="options-list ms-3">
                                                            <?php 
                                                            while($choice = $tfResult->fetch_assoc()) {
                                                                echo '<div class="option ' . ($choice['is_correct'] ? 'text-success' : '') . '">';
                                                                echo ($choice['is_correct'] ? '<i class="fas fa-check-circle"></i> ' : '<i class="far fa-circle"></i> ');
                                                                echo htmlspecialchars($choice['choice_text']);
                                                                echo '</div>';
                                                            }
                                                            ?>
                                                        </div>
                                                        <?php
                                                        break;

                                                    case 'programming':
                                                        if ($row['programming_language']) { // Only show if programming details exist
                                                            ?>
                                                            <div class="programming-details mt-3">
                                                                <p><strong>Language:</strong> <?php echo htmlspecialchars($row['programming_language']); ?></p>
                                                                
                                                                <!-- Test Cases -->
                                                                <?php include_test_cases($row['question_id'], $conn); ?>
                                                            </div>
                                                            <?php
                                                        }
                                                        break;
                                                }
                                                ?>
                                            </div>
                                            <div class="question-actions">
                                                <button class="btn btn-sm btn-outline-primary edit-question" 
                                                        data-question-id="<?php echo $row['question_id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-question" 
                                                        data-question-id="<?php echo $row['question_id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                echo '<div class="text-center py-5">';
                                echo '<p class="text-muted">No questions found in this category.</p>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/script.min.js"></script>

    <script>
    $(document).ready(function() {
        // Filter questions by type
        $('#typeFilter').change(function() {
            const selectedType = $(this).val();
            filterQuestions();
        });

        // Search questions
        $('#searchInput').on('keyup', function() {
            filterQuestions();
        });

        function filterQuestions() {
            const selectedType = $('#typeFilter').val();
            const searchText = $('#searchInput').val().toLowerCase();

            $('.question-item').each(function() {
                const questionType = $(this).data('type');
                const questionText = $(this).find('.question-content').text().toLowerCase();
                
                const typeMatch = !selectedType || questionType === selectedType;
                const textMatch = !searchText || questionText.includes(searchText);
                
                $(this).toggle(typeMatch && textMatch);
            });
        }

        // Handle question deletion
        $('.delete-question').click(function () {
            questionIdToDelete = $(this).data('question-id');
            $('#deleteConfirmationModal').modal('show');
        });

        // Handle confirmation button click
        $('#confirmDeleteButton').click(function () {
            if (questionIdToDelete) {
                $.ajax({
                    url: 'handlers/question_handler.php',
                    method: 'POST',
                    data: {
                        action: 'delete',
                        question_id: questionIdToDelete
                    },
                    success: function (response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.status === 'success') {
                                location.reload();
                            } else {
                                alert('Error: ' + result.message);
                            }
                        } catch (e) {
                            alert('Error processing the request');
                        }
                    },
                    error: function () {
                        alert('Error deleting the question');
                    }
                });
            }
            $('#deleteConfirmationModal').modal('hide');
        });

        // Update the edit button click handler
        $('.edit-question').click(function() {
            const questionId = $(this).data('question-id');
            // Fetch question data directly from get_question.php
            fetch(`get_question.php?id=${questionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateEditForm(data.question);
                        const editModal = new bootstrap.Modal(document.getElementById('editQuestionModal'));
                        editModal.show();
                    } else {
                        alert('Error loading question: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading question data');
                });
        });

        function populateEditForm(question) {
            $('#editQuestionId').val(question.question_id);
            $('#editQuestionType').val(question.question_type);
            $('#editQuestionText').val(question.question_text);

            // Clear previous content
            $('#editAnswerSection').empty();

            // Populate type-specific fields
            switch (question.question_type) {
                case 'multiple_choice':
                    let mcHtml = `
                        <div class="mb-3">
                            <label class="form-label">Options</label>
                            <div id="editOptionsContainer">`;
                    
                    // Add existing options
                    if (question.choices && question.choices.length > 0) {
                        question.choices.forEach((choice, index) => {
                            mcHtml += `
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" 
                                        name="options[]" 
                                        value="${escapeHtml(choice.text)}"
                                        required>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <input type="radio" 
                                                name="correct_answer" 
                                                value="${index}"
                                                ${choice.is_correct ? 'checked' : ''}>
                                            <label class="ms-2 mb-0">Correct</label>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger remove-option">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>`;
                        });
                    }

                    mcHtml += `</div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="editAddOptionBtn">
                            <i class="fas fa-plus"></i> Add Option
                        </button>
                    </div>`;

                    $('#editAnswerSection').html(mcHtml);

                    // Add event listener for add option button
                    $('#editAddOptionBtn').click(function() {
                        const optionCount = $('#editOptionsContainer .input-group').length;
                        const newOption = `
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" 
                                    name="options[]" 
                                    placeholder="Option ${optionCount + 1}" 
                                    required>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <input type="radio" 
                                            name="correct_answer" 
                                            value="${optionCount}">
                                        <label class="ms-2 mb-0">Correct</label>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-danger remove-option">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>`;
                        $('#editOptionsContainer').append(newOption);
                    });
                    break;

                case 'true_false':
                    let tfHtml = `
                        <div class="mb-3">
                            <label class="form-label">Correct Answer</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="correct_answer" value="True" id="editBtnTrue" 
                                    ${question.correct_answer === 'True' ? 'checked' : ''}>
                                <label class="btn btn-outline-primary" for="editBtnTrue">True</label>
                                
                                <input type="radio" class="btn-check" name="correct_answer" value="False" id="editBtnFalse"
                                    ${question.correct_answer === 'False' ? 'checked' : ''}>
                                <label class="btn btn-outline-primary" for="editBtnFalse">False</label>
                            </div>
                        </div>`;
                    $('#editAnswerSection').html(tfHtml);
                    break;

                case 'programming':
                    let progHtml = `
                        <div class="mb-3">
                            <label class="form-label">Programming Language</label>
                            <select class="form-control" name="programming_language" required>
                                <option value="python" ${question.programming_language === 'python' ? 'selected' : ''}>Python</option>
                                <option value="java" ${question.programming_language === 'java' ? 'selected' : ''}>Java</option>
                                <option value="c" ${question.programming_language === 'c' ? 'selected' : ''}>C</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Test Cases</label>
                            <div id="editTestCasesContainer">`;

                    // Add existing test cases
                    if (question.test_cases && question.test_cases.length > 0) {
                        question.test_cases.forEach((testCase, index) => {
                            progHtml += `
                                <div class="test-case mb-3">
                                    <div class="card">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <span>Test Case #${index + 1}</span>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-test-case">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label class="form-label">Input</label>
                                                <input type="text" class="form-control" 
                                                    name="test_case_input[]" 
                                                    value="${escapeHtml(testCase.test_input)}" 
                                                    required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Expected Output</label>
                                                <input type="text" class="form-control" 
                                                    name="test_case_output[]" 
                                                    value="${escapeHtml(testCase.expected_output)}"
                                                    required>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" 
                                                    name="test_case_hidden[]"
                                                    ${testCase.is_hidden ? 'checked' : ''}>
                                                <label class="form-check-label">Hidden Test Case</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        });
                    }

                    progHtml += `
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="editAddTestCaseBtn">
                                <i class="fas fa-plus"></i> Add Test Case
                            </button>
                        </div>`;

                    $('#editAnswerSection').html(progHtml);

                    // Add handler for adding new test cases
                    $('#editAddTestCaseBtn').click(function() {
                        const testCaseCount = $('#editTestCasesContainer .test-case').length + 1;
                        const newTestCase = `
                            <div class="test-case mb-3">
                                <div class="card">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <span>Test Case #${testCaseCount}</span>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-test-case">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <label class="form-label">Input</label>
                                            <input type="text" class="form-control" 
                                                name="test_case_input[]" 
                                                placeholder="Test input" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Expected Output</label>
                                            <input type="text" class="form-control" 
                                                name="test_case_output[]" 
                                                placeholder="Expected output" required>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" 
                                                name="test_case_hidden[]">
                                            <label class="form-check-label">Hidden Test Case</label>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        $('#editTestCasesContainer').append(newTestCase);
                    });
                    break;
            }

            // Initialize handlers for the edit form
            initializeEditFormHandlers();
        }

        function initializeEditFormHandlers() {
            // Handle adding new options in edit mode
            $('#editAddOptionBtn').click(function() {
                const newOption = `
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" name="options[]" required>
                        <div class="input-group-text">
                            <input type="radio" name="correct_answer" value="${$('#editOptionsContainer .input-group').length}">
                            <label class="ms-2 mb-0">Correct</label>
                        </div>
                        <button type="button" class="btn btn-outline-danger remove-option">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>`;
                $('#editOptionsContainer').append(newOption);
            });

            // Handle removing options in edit mode
            $(document).on('click', '.remove-option', function() {
                if ($('#editOptionsContainer .input-group').length > 1) {
                    $(this).closest('.input-group').remove();
                    // Update radio button values
                    $('#editOptionsContainer .input-group').each(function(index) {
                        $(this).find('input[type="radio"]').val(index);
                    });
                }
            });
        }

        // Helper function to escape HTML
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Update the form submission handler
        $('#editQuestionForm').on('submit', function(e) {
            e.preventDefault();
            
            const questionType = $('#editQuestionType').val();
            
            // Base form data
            const formData = {
                question_id: $('#editQuestionId').val(),
                question_text: $('#editQuestionText').val(),
                question_type: questionType
            };
            
            // Add type-specific data
            switch (questionType) {
                case 'multiple_choice':
                    formData.options = [];
                    formData.correct_answer = $('input[name="correct_answer"]:checked').val();
                    $('input[name="options[]"]').each(function() {
                        formData.options.push($(this).val());
                    });
                    break;
                    
                case 'true_false':
                    formData.correct_answer = $('input[name="correct_answer"]:checked').val();
                    break;
                    
                case 'programming':
                    formData.programming_language = $('select[name="programming_language"]').val();
                    formData.test_cases = [];
                    
                    $('.test-case').each(function() {
                        formData.test_cases.push({
                            test_input: $(this).find('input[name="test_case_input[]"]').val(),
                            expected_output: $(this).find('input[name="test_case_output[]"]').val(),
                            is_hidden: $(this).find('input[name="test_case_hidden[]"]').is(':checked'),
                            description: $(this).find('textarea[name="test_case_description[]"]')?.val() || ''
                        });
                    });
                    break;
            }
            
            console.log('Sending data:', formData); // Debug log
            
            fetch('update_question.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(result => {
                console.log('Server response:', result); // Debug log
                if (result.success) {
                    alert('Question updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating question: ' + (result.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating question: ' + error.message);
            });
        });
    });
    </script>

    <!-- Edit Question Modal -->
    <div class="modal fade" id="editQuestionModal" tabindex="-1" aria-labelledby="editQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editQuestionForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Question</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="question_id" id="editQuestionId">
                        <div class="mb-3">
                            <label class="form-label">Question Type</label>
                            <input type="text" class="form-control" id="editQuestionType" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Question Text</label>
                            <textarea class="form-control" name="question_text" id="editQuestionText" rows="3" required></textarea>
                        </div>
                        <div id="editAnswerSection">
                            <!-- Dynamic answer fields will be loaded here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--Delete Question-->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-trash-alt delete-icon"></i>
                        <p class="mb-0">Are you sure you want to delete this question? This action cannot be undone.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="confirmDeleteButton" type="button" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update the Import Question Modal -->
    <div class="modal fade" id="importQuestionModal" tabindex="-1" aria-labelledby="importQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Changed to modal-lg for more space -->
            <div class="modal-content">
                <form action="handlers/import_questions.php" method="POST" enctype="multipart/form-data" id="importQuestionForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Questions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Update the Import Instructions Section in the Import Modal -->
                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading mb-3"><i class="fas fa-info-circle"></i> How to Import Questions</h6>
                            <ol class="mb-0">
                                <li>Download the CSV template using the button below</li>
                                <li>Open the template in Excel, Google Sheets, or similar</li>
                                <li>Fill in your questions following these simple rules:
                                    <ul class="mt-2">
                                        <li><strong>question_type:</strong> Choose one of:
                                            <ul>
                                                <li><code>multiple_choice</code> - For questions with options</li>
                                                <li><code>true_false</code> - For True/False questions</li>
                                                <li><code>essay</code> - For open-ended questions</li>
                                            </ul>
                                        </li>
                                        <li><strong>question_text:</strong> Write your question here</li>
                                        <li><strong>options:</strong> For multiple choice questions:
                                            <ul>
                                                <li>Fill options 1-4 in their respective columns</li>
                                                <li>Leave empty for other question types</li>
                                            </ul>
                                        </li>
                                        <li><strong>correct_answer:</strong>
                                            <ul>
                                                <li>For multiple choice: Enter the option number (1-4)</li>
                                                <li>For true/false: Enter "True" or "False"</li>
                                                <li>For essay: Leave blank</li>
                                            </ul>
                                        </li>
                                        <li><strong>answer_guidelines:</strong> Optional guidelines for essay questions</li>
                                    </ul>
                                </li>
                                <li>Save your file as CSV</li>
                                <li>Upload using the form below</li>
                            </ol>
                        </div>

                        <!-- Update the Example Section in the Import Modal -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Example Questions</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Type</th>
                                                <th>Question</th>
                                                <th>Options/Answer</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><code>multiple_choice</code></td>
                                                <td>What is the capital of France?</td>
                                                <td>
                                                    Option 1: London<br>
                                                    Option 2: Berlin<br>
                                                    Option 3: Paris<br>
                                                    Option 4: Madrid<br>
                                                    Correct: 3
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><code>true_false</code></td>
                                                <td>The Earth is flat.</td>
                                                <td>Correct: False</td>
                                            </tr>
                                            <tr>
                                                <td><code>essay</code></td>
                                                <td>Explain the process of photosynthesis.</td>
                                                <td>Guidelines: Include key components: sunlight, chlorophyll, water, carbon dioxide, and glucose production.</td>
                                            </tr>
                                            <tr>
                                                <td><code>programming</code></td>
                                                <td>Sum of Two Numbers</td>
                                                <td>
                                                    Language: python<br>
                                                    Description: Write a function that takes two integers as input and returns their sum.<br>
                                                    Input Format: Two space-separated integers a and b<br>
                                                    Output Format: Single integer - the sum<br>
                                                    Constraints: 1 ≤ a, b ≤ 1000<br>
                                                    Sample Input: 5 3<br>
                                                    Sample Output: 8
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Update the Download Template Button -->
                        <div class="mb-3">
                            <a href="templates/generate_template.php" class="btn btn-outline-primary">
                                <i class="fas fa-download me-2"></i> Download Excel Template with Examples
                            </a>
                        </div>

                        <!-- Import Form -->
                        <div class="mb-3">
                            <label class="form-label">Select Category</label>
                            <select class="form-control" name="category" required>
                                <option value="">Choose Category</option>
                                <option value="new">+ Add New Category</option>
                                <?php
                                $sql = "SELECT DISTINCT category FROM question_bank ORDER BY category";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row['category']) . "'>" . 
                                             htmlspecialchars($row['category']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div id="importNewCategoryInput" class="mb-3" style="display: none;">
                            <input type="text" class="form-control" name="new_category" placeholder="Enter new category name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload File</label>
                            <input type="file" class="form-control" name="question_file" accept=".csv,.xlsx,.xls" required>
                            <div class="form-text">
                                <ul class="mb-0">
                                    <li>Download the template and open it in Excel/Spreadsheet software</li>
                                    <li>The template includes detailed instructions and examples</li>
                                    <li>The first row (after instructions) contains field names</li>
                                    <li>Delete the instruction rows before importing</li>
                                    <li>Save as CSV and upload</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Import Questions</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 
