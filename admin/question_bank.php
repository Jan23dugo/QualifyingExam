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
    <style>
    
        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }
    
        .clickable-row:hover {
            color: white;
            background-color: #0056b3; /* Light gray hover effect */
        }
    
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
                                                        echo "<tr class='clickable-row' data-href='view_questions.php?category=" . urlencode($row['category']) . "'>";
                                                        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                                                        echo "<td>" . $row['question_count'] . "</td>";
                                                        echo "<td>" . date('Y-m-d H:i', strtotime($row['last_modified'])) . "</td>";
                                                        echo "<td>";
                                                        echo "<button class='btn btn-sm btn-outline-success me-2 add-question-to-category' 
                                                                data-category='" . htmlspecialchars($row['category']) . "'
                                                                data-bs-toggle='modal' 
                                                                data-bs-target='#addQuestionModal'>
                                                                <i class='fas fa-plus'></i>
                                                            </button>";
                                                        echo '<button 
                                                            class="btn btn-sm btn-outline-danger delete-category" 
                                                            data-category="' . htmlspecialchars($row['category']) . '" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteCategoryModal">
                                                            <i class="fas fa-trash"></i>
                                                        </button>';
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
                <div class="modal-header">
                    <h5 class="modal-title">Import Questions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="importForm">
                        <div class="mb-3">
                            <label class="form-label">Select Category</label>
                            <div class="input-group">
                                <select class="form-control" name="category" id="importCategorySelect" required>
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
                            <div id="importNewCategoryInput" class="mt-2" style="display: none;">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="new_category" id="newCategoryName" placeholder="Enter new category name">
                                    <button type="button" class="btn btn-outline-secondary" id="cancelNewCategory">Cancel</button>
                                </div>
                            </div>
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
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="importSubmitBtn">Import</button>
                </div>
            </div>
        </div>
    </div>

     <!-- Category Deletion -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCategoryModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- This text will be dynamically updated -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="confirmDeleteCategoryButton" type="button" class="btn btn-danger">Delete</button>
                </div>
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
                            <option value="c">C</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Test Cases</label>
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
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addTestCaseBtn">
                            <i class="fas fa-plus"></i> Add Test Case
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
            let testCaseCount = 1;

            $('#addTestCaseBtn').click(function() {
                testCaseCount++;
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
                $('#testCasesContainer').append(newTestCase);
            });

            // Handle remove test case button
            $(document).on('click', '.remove-test-case', function() {
                if ($('#testCasesContainer .test-case').length > 1) {
                    $(this).closest('.test-case').remove();
                    // Update test case numbers
                    $('#testCasesContainer .test-case').each(function(index) {
                        $(this).find('.card-header span').text(`Test Case #${index + 1}`);
                    });
                    testCaseCount = $('#testCasesContainer .test-case').length;
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
                    currentQuestion.test_cases = [];
                    $('.test-case').each(function() {
                        currentQuestion.test_cases.push({
                            test_input: $(this).find('input[name="test_case_input[]"]').val(),
                            expected_output: $(this).find('input[name="test_case_output[]"]').val(),
                            is_hidden: $(this).find('input[name="test_case_hidden[]"]').is(':checked')
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
                    $('select[name="programming_language"]').val('');
                    // Clear test cases except the first one
                    const firstTestCase = $('.test-case:first');
                    $('#testCasesContainer').empty().append(firstTestCase.clone());
                    firstTestCase.find('input').val('');
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
        $('#importCategorySelect').change(function() {
            if ($(this).val() === 'new') {
                $('#importNewCategoryInput').show();
                $('#newCategoryName').prop('required', true).focus();
                $(this).prop('required', false);
            } else {
                $('#importNewCategoryInput').hide();
                $('#newCategoryName').prop('required', false);
                $(this).prop('required', true);
            }
        });

        // Handle cancel new category
        $('#cancelNewCategory').click(function() {
            $('#importCategorySelect').val('').prop('required', true);
            $('#importNewCategoryInput').hide();
            $('#newCategoryName').prop('required', false).val('');
        });

        // Update the import submit handler
        $('#importSubmitBtn').click(function() {
            const form = document.getElementById('importForm');
            const formData = new FormData(form);
            const submitBtn = $(this);
            
            // Handle new category
            const selectedCategory = $('#importCategorySelect').val();
            if (selectedCategory === 'new') {
                const newCategory = $('#newCategoryName').val().trim();
                if (!newCategory) {
                    alert('Please enter a category name');
                    $('#newCategoryName').focus();
                    return;
                }
                formData.set('category', newCategory);
            } else if (!selectedCategory) {
                alert('Please select a category');
                $('#importCategorySelect').focus();
                return;
            }

            // Disable the submit button and show loading state
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');

            // Show loading message
            const modalBody = $('#importQuestionModal .modal-body');
            if ($('#importLoadingMessage').length === 0) {
                modalBody.append('<div id="importLoadingMessage" class="alert alert-info mt-3" style="display: none;"></div>');
            }
            $('#importLoadingMessage').html('Importing questions...').fadeIn();

            // Log the FormData contents for debugging
            console.log('Category being sent:', formData.get('category'));
            console.log('File being sent:', formData.get('question_file'));

            $.ajax({
                url: 'handlers/import_questions.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('Server response:', response);
                    
                    try {
                        const result = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (result.status === 'success') {
                            $('#importLoadingMessage')
                                .removeClass('alert-info alert-danger')
                                .addClass('alert-success')
                                .html(`Success! ${result.total_imported} questions imported.`);
                            
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            $('#importLoadingMessage')
                                .removeClass('alert-info alert-success')
                                .addClass('alert-danger')
                                .html('Error: ' + (result.message || 'Unknown error occurred'));
                            
                            submitBtn.prop('disabled', false).html('Import');
                        }
                    } catch (e) {
                        console.error('Parse error:', e);
                        console.log('Raw response:', response);
                        
                        $('#importLoadingMessage')
                            .removeClass('alert-info alert-success')
                            .addClass('alert-danger')
                            .html('Error processing the import response');
                        
                        submitBtn.prop('disabled', false).html('Import');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', {xhr, status, error});
                    
                    $('#importLoadingMessage')
                        .removeClass('alert-info alert-success')
                        .addClass('alert-danger')
                        .html('Error uploading file: ' + error);
                    
                    submitBtn.prop('disabled', false).html('Import');
                }
            });
        });

        // Reset form when modal is closed
        $('#importQuestionModal').on('hidden.bs.modal', function() {
            $('#importForm')[0].reset();
            $('#importSubmitBtn').prop('disabled', false).html('Import');
            $('#importLoadingMessage').remove();
            $('#importNewCategoryInput').hide();
            $('#newCategoryName').prop('required', false).val('');
            $('#importCategorySelect').prop('required', true);
        });

        // Show the confirmation modal and set category data
        $('.delete-category').click(function () {
            const categoryToDelete = $(this).data('category');

            // Create the icon and text elements dynamically
            const deleteIconDiv = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-trash-alt delete-icon"></i>
                    <p>Are you sure you want to delete the category "<strong>${categoryToDelete}</strong>" and all its questions? This action cannot be undone.</p>
                </div>
            `;
            // Update the modal body with the new content
            $('#deleteCategoryModal .modal-body').html(deleteIconDiv);
        });


        // Handle delete confirmation
        $('#confirmDeleteCategoryButton').click(function () {
            if (categoryToDelete) {
                $.ajax({
                    url: 'handlers/category_handler.php',
                    method: 'POST',
                    data: {
                        action: 'delete',
                        category: categoryToDelete
                    },
                    success: function (response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.status === 'success') {
                                location.reload(); // Refresh the page
                            } else {
                                alert('Error: ' + result.message);
                            }
                        } catch (e) {
                            alert('Error processing the request');
                        }
                    },
                    error: function () {
                        alert('Error deleting the category');
                    }
                });
            }
            $('#deleteCategoryModal').modal('hide');
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
        
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if (!e.target.closest('button')) {
                    window.location = this.dataset.href;
                }
            });
        });
    });
    </script>
</body>
</html>
