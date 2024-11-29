<?php
include('../config/config.php');
// Rest of your code...
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Question Bank - Brand</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i&display=swap">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link rel="stylesheet" href="assets/css/question-bank.css">
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
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-header bg-white">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h4 class="mb-0">Question Bank</h4>
                                        </div>
                                        <div class="col text-right">
                                            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importQuestionModal">
                                                <i class="fas fa-file-import"></i> Import Questions
                                            </button>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                                                <i class="fas fa-plus"></i> Add New Question
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Filter Section -->
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <select class="form-control" id="categoryFilter">
                                                <option value="">All Categories</option>
                                                <?php
                                                include('../config/config.php');
                                                $sql = "SELECT DISTINCT category FROM question_bank ORDER BY category";
                                                $result = $conn->query($sql);
                                                if ($result && $result->num_rows > 0) {
                                                    while($row = $result->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($row['category']) . "'>" . htmlspecialchars($row['category']) . "</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-control" id="typeFilter">
                                                <option value="">All Question Types</option>
                                                <option value="multiple_choice">Multiple Choice</option>
                                                <option value="true_false">True/False</option>
                                                <option value="essay">Essay</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" id="searchInput" placeholder="Search questions...">
                                        </div>
                                    </div>

                                    <!-- Questions Table -->
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Category Name</th>
                                                    <th>Number of Questions</th>
                                                    <th>Last Modified</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Get categories with question counts and last modified dates
                                                $sql = "SELECT 
                                                            category,
                                                            COUNT(*) as question_count,
                                                            MAX(updated_at) as last_modified
                                                        FROM question_bank 
                                                        GROUP BY category 
                                                        ORDER BY category";
                                                
                                                $result = $conn->query($sql);
                                                
                                                if ($result && $result->num_rows > 0) {
                                                    while($row = $result->fetch_assoc()) {
                                                        echo "<tr>";
                                                        echo "<td><a href='view_questions.php?category=" . urlencode($row['category']) . "'>" . 
                                                             htmlspecialchars($row['category']) . "</a></td>";
                                                        echo "<td>" . $row['question_count'] . "</td>";
                                                        echo "<td>" . date('Y-m-d H:i', strtotime($row['last_modified'])) . "</td>";
                                                        echo "<td>";
                                                        echo "<button class='btn btn-sm btn-outline-success me-2 add-question-to-category' 
                                                                data-category='" . htmlspecialchars($row['category']) . "'
                                                                data-bs-toggle='modal' 
                                                                data-bs-target='#addQuestionModal'>
                                                                <i class='fas fa-plus'></i>
                                                              </button>";
                                                        echo "<button class='btn btn-sm btn-outline-danger delete-category' 
                                                                data-category='" . htmlspecialchars($row['category']) . "'>
                                                                <i class='fas fa-trash'></i>
                                                              </button>";
                                                        echo "</td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='4' class='text-center'>No categories found</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <!-- Scroll to Top Button-->
    <a class="border rounded d-inline scroll-to-top" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Add Question Modal -->
    <div class="modal fade" id="addQuestionModal" tabindex="-1" aria-labelledby="addQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="handlers/question_handler.php" method="POST" id="addQuestionForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Question</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <div class="input-group">
                                <select class="form-control" name="category" id="categorySelect">
                                    <option value="">Select Category</option>
                                    <option value="new">+ Add New Category</option>
                                    <?php
                                    // Fetch existing categories from database
                                    include('../config/config.php');
                                    $sql = "SELECT DISTINCT category FROM question_bank ORDER BY category";
                                    $result = $conn->query($sql);
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<option value='" . htmlspecialchars($row['category']) . "'>" . htmlspecialchars($row['category']) . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <!-- New Category Input (hidden by default) -->
                            <div id="newCategoryInput" class="mt-2" style="display: none;">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="new_category" placeholder="Enter new category name">
                                    <button type="button" class="btn btn-outline-secondary" id="cancelNewCategory">Cancel</button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Question Type</label>
                            <select class="form-control" name="question_type" id="questionType" required>
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="true_false">True/False</option>
                                <option value="essay">Essay</option>
                                <option value="programming">Programming</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Question Text</label>
                            <textarea class="form-control" name="question_text" rows="3" required></textarea>
                        </div>
                        <div id="answersSection">
                            <!-- Dynamic answer fields will be loaded here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-between w-100">
                            <div>
                                <button type="button" class="btn btn-success" id="addAnotherQuestion">
                                    <i class="fas fa-plus"></i> Add Another Question
                                </button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save Question(s)</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Question Modal -->
    <div class="modal fade" id="importQuestionModal" tabindex="-1" aria-labelledby="importQuestionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="handlers/import_questions.php" method="POST" enctype="multipart/form-data" id="importQuestionForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Questions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
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
                            <input type="file" class="form-control" name="question_file" accept=".csv" required>
                            <small class="text-muted">Accepted format: CSV</small>
                        </div>
                        <div class="mb-3">
                            <a href="templates/generate_template.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download"></i> Download CSV Template
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/script.min.js"></script>

    <script>
    $(document).ready(function() {
        // Add this function to properly clean up the modal
        function resetModal() {
            // Reset form
            $('#addQuestionForm')[0].reset();
            
            // Reset category input
            $('#newCategoryInput').hide();
            $('#categorySelect').prop('required', true);
            $('input[name="new_category"]').prop('required', false);
            
            // Remove modal backdrop
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
            
            // Reset to default question type
            updateAnswerFields('multiple_choice');
        }

        // Handle modal close button
        $('.btn-close, .modal button[data-bs-dismiss="modal"]').click(function() {
            $('#addQuestionModal').modal('hide');
            resetModal();
        });

        // Handle modal hidden event
        $('#addQuestionModal').on('hidden.bs.modal', function () {
            resetModal();
        });

        // Debug log
        console.log('Document ready');
        
        // Test modal trigger
        $('.btn-primary[data-bs-toggle="modal"]').click(function() {
            console.log('Modal button clicked');
            $('#addQuestionModal').modal('show');
        });

        // Initialize with multiple choice options since it's the default
        updateAnswerFields('multiple_choice');
        
        $('#questionType').change(function() {
            updateAnswerFields($(this).val());
        });
        
        function updateAnswerFields(questionType) {
            let html = '';
            
            if (questionType === 'multiple_choice') {
                html = `
                    <div class="mb-3">
                        <label class="form-label">Options</label>
                        <div id="optionsContainer">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="options[]" placeholder="Option 1" required>
                                <div class="input-group-text">
                                    <input type="radio" name="correct_answer" value="0" required>
                                    <label class="ms-2 mb-0">Correct</label>
                                </div>
                                <button type="button" class="btn btn-outline-danger remove-option">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addOptionBtn">
                            <i class="fas fa-plus"></i> Add Option
                        </button>
                    </div>`;
            } else if (questionType === 'true_false') {
                html = `
                    <div class="mb-3">
                        <label class="form-label">Correct Answer</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="correct_answer" value="True" id="btnTrue" required>
                            <label class="btn btn-outline-primary" for="btnTrue">True</label>
                            
                            <input type="radio" class="btn-check" name="correct_answer" value="False" id="btnFalse" required>
                            <label class="btn btn-outline-primary" for="btnFalse">False</label>
                        </div>
                    </div>`;
            } else if (questionType === 'essay') {
                html = `
                    <div class="mb-3">
                        <label class="form-label">Answer Guidelines (Optional)</label>
                        <textarea class="form-control" name="answer_guidelines" rows="3"></textarea>
                    </div>`;
            } else if (questionType === 'programming') {
                html = `
                    <div class="mb-3">
                        <label class="form-label">Programming Language</label>
                        <select class="form-control" name="programming_language" required>
                            <option value="python">Python</option>
                            <option value="java">Java</option>
                            <option value="cpp">C++</option>
                            <option value="c">C</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Problem Description</label>
                        <textarea class="form-control" name="problem_description" rows="4" placeholder="Detailed description of the programming problem" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Input Format</label>
                        <textarea class="form-control" name="input_format" rows="2" placeholder="Describe the format of input" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Output Format</label>
                        <textarea class="form-control" name="output_format" rows="2" placeholder="Describe the format of expected output" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Constraints</label>
                        <textarea class="form-control" name="constraints" rows="2" placeholder="List any constraints (e.g., input size limits, value ranges)" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sample Test Cases</label>
                        <div id="testCasesContainer">
                            <div class="test-case mb-3">
                                <div class="card">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <span>Test Case #1</span>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-test-case">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <label class="form-label">Sample Input</label>
                                            <textarea class="form-control" name="test_case_input[]" rows="2" placeholder="Enter sample input" required></textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Sample Output</label>
                                            <textarea class="form-control" name="test_case_output[]" rows="2" placeholder="Enter expected output" required></textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Explanation (Optional)</label>
                                            <textarea class="form-control" name="test_case_explanation[]" rows="2" placeholder="Explain this test case"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addTestCaseBtn">
                            <i class="fas fa-plus"></i> Add Test Case
                        </button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Solution Template (Optional)</label>
                        <textarea class="form-control" name="solution_template" rows="4" placeholder="Provide starter code or function template that students need to complete"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hidden Test Cases</label>
                        <div id="hiddenTestCasesContainer">
                            <div class="test-case mb-3">
                                <div class="card">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <span>Hidden Test Case #1</span>
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-hidden-test-case">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <label class="form-label">Input</label>
                                            <textarea class="form-control" name="hidden_test_input[]" rows="2" placeholder="Enter input for hidden test" required></textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Expected Output</label>
                                            <textarea class="form-control" name="hidden_test_output[]" rows="2" placeholder="Enter expected output" required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addHiddenTestCaseBtn">
                            <i class="fas fa-plus"></i> Add Hidden Test Case
                        </button>
                    </div>`;
            }
            
            $('#answersSection').html(html);
            
            // Reinitialize event handlers for new elements
            if (questionType === 'multiple_choice') {
                initializeMultipleChoiceHandlers();
            } else if (questionType === 'programming') {
                initializeProgrammingHandlers();
            }
        }
        
        function initializeMultipleChoiceHandlers() {
            let optionCount = 1;
            
            $('#addOptionBtn').click(function() {
                optionCount++;
                const newOption = `
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" name="options[]" placeholder="Option ${optionCount}" required>
                        <div class="input-group-text">
                            <input type="radio" name="correct_answer" value="${optionCount - 1}">
                            <label class="ms-2 mb-0">Correct</label>
                        </div>
                        <button type="button" class="btn btn-outline-danger remove-option">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>`;
                $('#optionsContainer').append(newOption);
            });
            
            // Handle remove option button
            $(document).on('click', '.remove-option', function() {
                if ($('#optionsContainer .input-group').length > 1) {
                    $(this).closest('.input-group').remove();
                }
            });
        }

        function initializeProgrammingHandlers() {
            $('#addTestCaseBtn').click(function() {
                const newTestCase = `
                    <div class="test-case mb-3">
                        <div class="input-group mb-2">
                            <span class="input-group-text">Input</span>
                            <textarea class="form-control" name="test_case_input[]" rows="2" placeholder="Enter input test case" required></textarea>
                        </div>
                        <div class="input-group mb-2">
                            <span class="input-group-text">Expected Output</span>
                            <textarea class="form-control" name="test_case_output[]" rows="2" placeholder="Enter expected output" required></textarea>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-test-case">
                            <i class="fas fa-times"></i> Remove Test Case
                        </button>
                    </div>`;
                $('#testCasesContainer').append(newTestCase);
            });

            $(document).on('click', '.remove-test-case', function() {
                if ($('#testCasesContainer .test-case').length > 1) {
                    $(this).closest('.test-case').remove();
                }
            });
        }

        // Handle category selection
        $('#categorySelect').change(function() {
            if ($(this).val() === 'new') {
                $('#newCategoryInput').show();
                $('input[name="new_category"]').prop('required', true);
                $(this).prop('required', false);
            } else {
                $('#newCategoryInput').hide();
                $('input[name="new_category"]').prop('required', false);
                $(this).prop('required', true);
            }
        });

        // Handle cancel new category
        $('#cancelNewCategory').click(function() {
            $('#categorySelect').val('').prop('required', true);
            $('#newCategoryInput').hide();
            $('input[name="new_category"]').prop('required', false).val('');
        });

        // Replace the existing form submission handler with this updated version
        $('#addQuestionForm').on('submit', function(e) {
            e.preventDefault();
            
            // Get all questions data
            const questions = collectQuestionsData();
            
            // Basic validation
            if (questions.length === 0) {
                alert('Please add at least one question');
                return false;
            }
            
            // Submit the questions
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: {
                    action: 'add_multiple',
                    questions: JSON.stringify(questions)  // Stringify the questions array
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.status === 'success') {
                            alert('Questions saved successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Error processing the request');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);
                    alert('Error submitting the questions');
                }
            });
        });

        // Add these new functions to handle multiple questions
        let questionsList = [];

        function collectQuestionsData() {
            const currentQuestion = {
                category: $('#categorySelect').val() === 'new' ? $('input[name="new_category"]').val() : $('#categorySelect').val(),
                question_type: $('#questionType').val(),
                question_text: $('textarea[name="question_text"]').val(),
            };

            // Add type-specific data
            switch(currentQuestion.question_type) {
                case 'multiple_choice':
                    currentQuestion.options = [];
                    $('input[name="options[]"]').each(function() {
                        currentQuestion.options.push($(this).val());
                    });
                    currentQuestion.correct_answer = $('input[name="correct_answer"]:checked').val();
                    break;
                case 'true_false':
                    currentQuestion.correct_answer = $('.btn-check:checked').val() || null;
                    if (!currentQuestion.correct_answer) {
                        throw new Error('Please select True or False');
                    }
                    break;
                case 'essay':
                    currentQuestion.answer_guidelines = $('textarea[name="answer_guidelines"]').val();
                    break;
                case 'programming':
                    currentQuestion.programming_language = $('select[name="programming_language"]').val();
                    currentQuestion.problem_description = $('textarea[name="problem_description"]').val();
                    currentQuestion.input_format = $('textarea[name="input_format"]').val();
                    currentQuestion.output_format = $('textarea[name="output_format"]').val();
                    currentQuestion.constraints = $('textarea[name="constraints"]').val();
                    // Collect test cases
                    currentQuestion.test_cases = [];
                    $('.test-case').each(function() {
                        currentQuestion.test_cases.push({
                            input: $(this).find('textarea[name="test_case_input[]"]').val(),
                            output: $(this).find('textarea[name="test_case_output[]"]').val(),
                            explanation: $(this).find('textarea[name="test_case_explanation[]"]').val()
                        });
                    });
                    break;
            }

            return questionsList.concat([currentQuestion]);
        }

        // Add this code to handle the "Add Another Question" button
        $('#addAnotherQuestion').click(function() {
            // Validate current question
            if (!validateCurrentQuestion()) {
                return;
            }
            
            // Add current question to the list
            const currentQuestion = collectQuestionsData()[questionsList.length];
            questionsList.push(currentQuestion);
            
            // Update questions counter
            updateQuestionsCounter();
            
            // Clear form for next question
            clearQuestionForm();
            
            // Show success message
            showTemporaryMessage('Question added! Add another one or click Save to finish.');
        });

        function validateCurrentQuestion() {
            if (!$('textarea[name="question_text"]').val().trim()) {
                alert('Please enter the question text');
                return false;
            }
            
            const questionType = $('#questionType').val();
            
            if (questionType === 'multiple_choice') {
                if (!$('input[name="correct_answer"]:checked').length) {
                    alert('Please select a correct answer');
                    return false;
                }
            } else if (questionType === 'true_false') {
                if (!$('.btn-check:checked').length) {
                    alert('Please select True or False as the correct answer');
                    return false;
                }
            }
            
            return true;
        }

        function clearQuestionForm() {
            // Clear question text
            $('textarea[name="question_text"]').val('');
            
            // Reset type-specific fields
            switch($('#questionType').val()) {
                case 'multiple_choice':
                    $('input[name="options[]"]').val('');
                    $('input[name="correct_answer"]').prop('checked', false);
                    break;
                case 'true_false':
                    $('input[name="correct_answer"]').prop('checked', false);
                    break;
                case 'essay':
                    $('textarea[name="answer_guidelines"]').val('');
                    break;
                case 'programming':
                    $('textarea[name="problem_description"]').val('');
                    $('textarea[name="input_format"]').val('');
                    $('textarea[name="output_format"]').val('');
                    $('textarea[name="constraints"]').val('');
                    // Clear test cases except the first one
                    const firstTestCase = $('.test-case:first');
                    $('#testCasesContainer').empty().append(firstTestCase.clone());
                    firstTestCase.find('textarea').val('');
                    break;
            }
        }

        function updateQuestionsCounter() {
            // Add this HTML right after the modal title if it doesn't exist
            if ($('#questionsCounter').length === 0) {
                $('.modal-title').after('<div id="questionsCounter" class="ms-3 badge bg-primary"></div>');
            }
            $('#questionsCounter').text(`Questions: ${questionsList.length + 1}`);
        }

        function showTemporaryMessage(message) {
            // Add this HTML if it doesn't exist
            if ($('#temporaryMessage').length === 0) {
                $('.modal-body').prepend('<div id="temporaryMessage" class="alert alert-success" style="display: none;"></div>');
            }
            
            $('#temporaryMessage')
                .text(message)
                .fadeIn()
                .delay(3000)
                .fadeOut();
        }

        // Reset questions list when modal is closed
        $('#addQuestionModal').on('hidden.bs.modal', function() {
            questionsList = [];
            $('#questionsCounter').remove();
            clearQuestionForm();
        });

        // Handle import category selection
        $('select[name="category"]').change(function() {
            if ($(this).val() === 'new') {
                $('#importNewCategoryInput').show();
                $('input[name="new_category"]').prop('required', true);
            } else {
                $('#importNewCategoryInput').hide();
                $('input[name="new_category"]').prop('required', false);
            }
        });

        // Handle import form submission
        $('#importQuestionForm').on('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.status === 'success') {
                            alert('Questions imported successfully!');
                            location.reload();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (e) {
                        alert('Error processing the import');
                    }
                },
                error: function() {
                    alert('Error uploading file');
                }
            });
        });

        // Handle category deletion
        $('.delete-category').click(function() {
            const category = $(this).data('category');
            if (confirm(`Are you sure you want to delete the category "${category}" and all its questions? This action cannot be undone.`)) {
                $.ajax({
                    url: 'handlers/category_handler.php',
                    method: 'POST',
                    data: {
                        action: 'delete',
                        category: category
                    },
                    success: function(response) {
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
                    error: function() {
                        alert('Error deleting the category');
                    }
                });
            }
        });

        // Handle clicking "Add Question" button on a category
        $('.add-question-to-category').click(function() {
            const category = $(this).data('category');
            $('#categorySelect').val(category);
            // Disable category selection since we're adding to a specific category
            $('#categorySelect').prop('disabled', true);
            $('#newCategoryInput').hide();
        });

        // When modal is hidden, reset category select
        $('#addQuestionModal').on('hidden.bs.modal', function() {
            $('#categorySelect').prop('disabled', false);
        });
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Remove the old modal trigger code and replace with this
        const addQuestionBtn = document.querySelector('[data-bs-target="#addQuestionModal"]');
        const importQuestionBtn = document.querySelector('[data-bs-target="#importQuestionModal"]');
        
        if (addQuestionBtn) {
            addQuestionBtn.addEventListener('click', function() {
                var addModal = new bootstrap.Modal(document.getElementById('addQuestionModal'));
                addModal.show();
            });
        }

        if (importQuestionBtn) {
            importQuestionBtn.addEventListener('click', function() {
                var importModal = new bootstrap.Modal(document.getElementById('importQuestionModal'));
                importModal.show();
            });
        }
    });
    </script>
</body>
</html>