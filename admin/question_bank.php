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
    
    <!-- Add SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
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
                                    </div>

                                    <!-- Questions Table -->
                                    <div class="table-responsive">
                                        <table class="table">
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
                    <form id="importForm" enctype="multipart/form-data">
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
                                            echo "<option value='" . htmlspecialchars($row['category']) . "'>" . htmlspecialchars($row['category']) . "</option>";
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

                // Add validation for true/false
                setTimeout(() => {
                    $('input[name="correct_answer"]').on('change', function() {
                        console.log('Selected answer:', this.value);
                    });
                }, 0);
            } else if (questionType === 'essay') {
                html = `
                    <div class="mb-3">
                        <label class="form-label">Answer Guidelines (Optional)</label>
                        <textarea class="form-control" name="answer_guidelines" rows="3"></textarea>
                    </div>`;
            } else if (questionType === 'programming') {
                html = `
                    <div class="programming-question-fields">
                        <div class="mb-3">
                            <label class="form-label">Programming Language</label>
                            <select class="form-control" name="programming_language" id="programmingLanguage" required>
                                <option value="">Select Language</option>
                                <option value="Python">Python</option>
                                <option value="Java">Java</option>
                                <option value="C++">C++</option>
                                <option value="JavaScript">JavaScript</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Test Cases</label>
                            <div id="testCasesContainer">
                                <div class="test-case mb-3">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <label class="form-label">Input</label>
                                            <textarea class="form-control" name="test_case_input[]" rows="2" required></textarea>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Expected Output</label>
                                            <textarea class="form-control" name="test_case_output[]" rows="2" required></textarea>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input test-case-hidden" type="checkbox" name="test_case_hidden[]" value="1">
                                                <label class="form-check-label">Hidden</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2 description-row" style="display: none;">
                                        <div class="col-12">
                                            <label class="form-label">Description (Optional)</label>
                                            <input type="text" class="form-control" name="test_case_description[]" 
                                                placeholder="Describe what this test case is checking">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary" id="addTestCase">
                                <i class="fas fa-plus"></i> Add Test Case
                            </button>
                        </div>
                    </div>
                `;

                // Add event handlers after HTML is inserted
                setTimeout(() => {
                    // Handle showing/hiding description based on hidden checkbox
                    $(document).on('change', '.test-case-hidden', function() {
                        const descriptionRow = $(this).closest('.test-case').find('.description-row');
                        if (this.checked) {
                            descriptionRow.slideDown();
                        } else {
                            descriptionRow.slideUp();
                            descriptionRow.find('input').val(''); // Clear description when hidden
                        }
                    });

                    // Handle adding new test cases
                    $('#addTestCase').click(function() {
                        const newTestCase = `
                            <div class="test-case mb-3">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="form-label">Input</label>
                                        <textarea class="form-control" name="test_case_input[]" rows="2" required></textarea>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Expected Output</label>
                                        <textarea class="form-control" name="test_case_output[]" rows="2" required></textarea>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input test-case-hidden" type="checkbox" name="test_case_hidden[]" value="1">
                                            <label class="form-check-label">Hidden</label>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm remove-test-case mt-2">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-2 description-row" style="display: none;">
                                    <div class="col-12">
                                        <label class="form-label">Description (Optional)</label>
                                        <input type="text" class="form-control" name="test_case_description[]" 
                                            placeholder="Describe what this test case is checking">
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#testCasesContainer').append(newTestCase);
                    });

                    // Handle removing test cases
                    $(document).on('click', '.remove-test-case', function() {
                        $(this).closest('.test-case').remove();
                    });

                    // Add first test case automatically
                    if ($('#testCasesContainer .test-case').length === 0) {
                        $('#addTestCase').click();
                    }
                }, 0);
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

        // Replace the existing form submission handler
        $('#addQuestionForm').on('submit', function(e) {
            e.preventDefault();
            
            // Validate current question
            if (!validateCurrentQuestion()) {
                return;
            }

            // Add the current question to the list before submitting
            try {
                const currentQuestion = collectCurrentQuestionData();
                questionsList.push(currentQuestion);
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message,
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Create FormData object to send all questions
            const formData = new FormData();
            formData.append('action', 'add_multiple');
            formData.append('questions', JSON.stringify(questionsList));
            
            // Debug log
            console.log('Questions to be submitted:', questionsList);
            console.log('FormData contents:');
            for (let pair of formData.entries()) {
                console.log(pair[0], pair[1]);
            }

            // Show loading state
            const submitButton = $(this).find('button[type="submit"]');
            const originalText = submitButton.html();
            submitButton.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

            $.ajax({
                url: 'handlers/question_handler.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log('Server response:', response);
                    try {
                        const result = typeof response === 'object' ? response : JSON.parse(response);
                        
                        if (result.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: `Successfully saved ${questionsList.length} question(s)!`,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(result.message || 'Failed to save questions');
                        }
                    } catch (e) {
                        console.error('Error details:', {
                            error: e,
                            rawResponse: response
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: e.message || 'Failed to process the server response',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error details:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to save the questions. Please try again.',
                        confirmButtonColor: '#d33'
                    });
                },
                complete: function() {
                    // Reset button state
                    submitButton.prop('disabled', false).html(originalText);
                }
            });
        });

        // Add this new function to collect current question data
        function collectCurrentQuestionData() {
            const questionType = $('#questionType').val();
            const questionText = $('textarea[name="question_text"]').val().trim();
            
            if (!questionText) {
                throw new Error('Question text is required');
            }

            const questionData = {
                category: $('#categorySelect').val() === 'new' ? 
                    $('input[name="new_category"]').val() : 
                    $('#categorySelect').val(),
                question_type: questionType,
                question_text: questionText
            };

            // Add type-specific data
            switch(questionType) {
                case 'multiple_choice':
                    const options = [];
                    const correctAnswer = $('input[name="correct_answer"]:checked').val();
                    
                    $('input[name="options[]"]').each(function() {
                        options.push($(this).val().trim());
                    });

                    if (!options.length) {
                        throw new Error('At least one option is required');
                    }
                    if (correctAnswer === undefined) {
                        throw new Error('Please select the correct answer');
                    }

                    questionData.options = options;
                    questionData.correct_answer = correctAnswer;
                    break;

                case 'true_false':
                    const tfAnswer = $('input[name="correct_answer"]:checked').val();
                    if (!tfAnswer) {
                        throw new Error('Please select True or False');
                    }
                    questionData.correct_answer = tfAnswer;
                    break;

                case 'programming':
                    const language = $('#programmingLanguage').val();
                    if (!language) {
                        throw new Error('Please select a programming language');
                    }

                    questionData.programming_language = language;
                    questionData.test_cases = [];

                    $('.test-case').each(function() {
                        const input = $(this).find('textarea[name="test_case_input[]"]').val().trim();
                        const output = $(this).find('textarea[name="test_case_output[]"]').val().trim();
                        const isHidden = $(this).find('.test-case-hidden').prop('checked');
                        const description = $(this).find('input[name="test_case_description[]"]').val().trim();

                        if (input && output) {
                            questionData.test_cases.push({
                                input: input,
                                output: output,
                                is_hidden: isHidden ? 1 : 0,
                                description: description
                            });
                        }
                    });

                    if (!questionData.test_cases.length) {
                        throw new Error('At least one test case is required');
                    }
                    break;
            }

            return questionData;
        }

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
        // Replace the existing import button handler
$('#importSubmitBtn').click(function(e) {
    e.preventDefault();
    
    const form = $('#importForm')[0];
    const formData = new FormData(form);
    const submitBtn = $(this);
    const modalBody = $('#importQuestionModal .modal-body');
    
    // Validate file input
    const fileInput = form.querySelector('input[type="file"]');
    if (!fileInput.files.length) {
        modalBody.append(
            `<div class="alert alert-danger mt-3">
                Please select a file to import.
            </div>`
        );
        return;
    }
    
    // Remove any existing messages
    $('.alert').remove();
    
    // Disable submit button and show loading
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');

    $.ajax({
        url: 'handlers/import_questions.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (result.status === 'success') {
                    modalBody.append(
                        `<div class="alert alert-success mt-3">
                            ${result.message}
                        </div>`
                    );
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(result.message || 'Unknown error occurred');
                }
            } catch (error) {
                modalBody.append(
                    `<div class="alert alert-danger mt-3">
                        ${error.message}
                    </div>`
                );
                submitBtn.prop('disabled', false).html('Import');
            }
        },
        error: function(xhr, status, error) {
            let errorMessage = 'Error uploading file. Please try again.';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch (e) {
                console.error('Error parsing response:', e);
            }
            
            modalBody.append(
                `<div class="alert alert-danger mt-3">
                    ${errorMessage}
                </div>`
            );
            submitBtn.prop('disabled', false).html('Import');
        }
    });
});

        // Reset modal when closed
        $('#importQuestionModal').on('hidden.bs.modal', function() {
            const form = $('#importForm')[0];
            form.reset();
            $('.alert').remove();
            $('#importSubmitBtn').prop('disabled', false).html('Import');
        });

        // Show the confirmation modal and set category data
        $('.delete-category').click(function(e) {
            e.preventDefault();
            const categoryToDelete = $(this).data('category');
            
            // Show delete confirmation using SweetAlert2
            Swal.fire({
                title: 'Delete Category?',
                html: `Are you sure you want to delete the category <strong>"${categoryToDelete}"</strong>?<br><br>` +
                      `<div class="alert alert-warning" role="alert">` +
                      `<i class="fas fa-exclamation-triangle"></i> ` +
                      `This will permanently delete all questions in this category!</div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: 'handlers/category_handler.php',
                        method: 'POST',
                        data: {
                            action: 'delete',
                            category: categoryToDelete
                        },
                        dataType: 'json'
                    }).catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error.responseText || 'Unknown error occurred'}`
                        );
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value.status === 'success') {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Category and all its questions have been deleted.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Error!',
                        text: result.value.message || 'Failed to delete category',
                        icon: 'error'
                    });
                }
            });
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