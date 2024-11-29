<?php
include('../config/config.php');

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
                            // Modified query to match your database structure
                            $sql = "SELECT question_bank.*, 
                                    GROUP_CONCAT(CONCAT(question_bank_choices.choice_text, ':', question_bank_choices.is_correct) SEPARATOR '||') as choices 
                                    FROM question_bank 
                                    LEFT JOIN question_bank_choices ON question_bank.question_id = question_bank_choices.question_id 
                                    WHERE question_bank.category = ? 
                                    GROUP BY question_bank.question_id 
                                    ORDER BY question_bank.updated_at DESC";
                            
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
                                                
                                                <?php if ($row['question_type'] === 'multiple_choice' && $row['choices']) { ?>
                                                    <div class="options-list ms-3">
                                                        <?php 
                                                        $choices = explode('||', $row['choices']);
                                                        foreach ($choices as $choice) {
                                                            list($text, $is_correct) = explode(':', $choice);
                                                            echo '<div class="option ' . ($is_correct ? 'text-success' : '') . '">';
                                                            echo ($is_correct ? '<i class="fas fa-check-circle"></i> ' : '<i class="far fa-circle"></i> ');
                                                            echo htmlspecialchars($text);
                                                            echo '</div>';
                                                        }
                                                        ?>
                                                    </div>
                                                <?php } elseif ($row['question_type'] === 'true_false') { ?>
                                                    <div class="mb-2">
                                                        <strong>Correct Answer:</strong> <?php echo htmlspecialchars($row['correct_answer'] ?? ''); ?>
                                                    </div>
                                                <?php } ?>
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
        $('.delete-question').click(function() {
            const questionId = $(this).data('question-id');
            if (confirm('Are you sure you want to delete this question?')) {
                $.ajax({
                    url: 'handlers/question_handler.php',
                    method: 'POST',
                    data: {
                        action: 'delete',
                        question_id: questionId
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
                        alert('Error deleting the question');
                    }
                });
            }
        });

        // Update the edit button click handler
        $('.edit-question').click(function() {
            const questionId = $(this).data('question-id');
            console.log('Edit clicked for question:', questionId); // Debug log
            loadQuestionForEdit(questionId);
        });

        function loadQuestionForEdit(questionId) {
            $.ajax({
                url: 'handlers/question_handler.php',
                method: 'POST',
                data: {
                    action: 'get_question',
                    question_id: questionId
                },
                success: function(response) {
                    try {
                        console.log('Response:', response); // Debug log
                        const result = JSON.parse(response);
                        if (result.status === 'success') {
                            populateEditForm(result.data);
                            const editModal = new bootstrap.Modal(document.getElementById('editQuestionModal'));
                            editModal.show();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        alert('Error processing the request');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('Error loading question data');
                }
            });
        }

        function populateEditForm(question) {
            console.log('Populating form with:', question); // Debug log
            
            $('#editQuestionId').val(question.question_id);
            $('#editQuestionType').val(question.question_type);
            $('#editQuestionText').val(question.question_text);

            // Clear previous content
            $('#editAnswerSection').empty();

            // Populate type-specific fields
            switch(question.question_type) {
                case 'multiple_choice':
                    let html = `
                        <div class="mb-3">
                            <label class="form-label">Options</label>
                            <div id="editOptionsContainer">`;
                    
                    question.choices.forEach((choice, index) => {
                        html += `
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="options[]" value="${escapeHtml(choice.text)}" required>
                                <div class="input-group-text">
                                    <input type="radio" name="correct_answer" value="${index}" ${choice.is_correct ? 'checked' : ''}>
                                    <label class="ms-2 mb-0">Correct</label>
                                </div>
                                <button type="button" class="btn btn-outline-danger remove-option">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>`;
                    });

                    html += `</div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="editAddOptionBtn">
                            <i class="fas fa-plus"></i> Add Option
                        </button>
                        </div>`;
                    $('#editAnswerSection').html(html);
                    break;

                case 'true_false':
                    const tfHtml = `
                        <div class="mb-3">
                            <label class="form-label">Correct Answer</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="correct_answer" value="True" id="editTrue" 
                                    ${question.correct_answer === 'True' ? 'checked' : ''}>
                                <label class="btn btn-outline-primary" for="editTrue">True</label>
                                
                                <input type="radio" class="btn-check" name="correct_answer" value="False" id="editFalse"
                                    ${question.correct_answer === 'False' ? 'checked' : ''}>
                                <label class="btn btn-outline-primary" for="editFalse">False</label>
                            </div>
                        </div>`;
                    $('#editAnswerSection').html(tfHtml);
                    break;

                case 'essay':
                    const essayHtml = `
                        <div class="mb-3">
                            <label class="form-label">Answer Guidelines</label>
                            <textarea class="form-control" name="answer_guidelines" rows="3">${escapeHtml(question.answer_guidelines || '')}</textarea>
                        </div>`;
                    $('#editAnswerSection').html(essayHtml);
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

        // Handle edit form submission
        $('#editQuestionForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            $.ajax({
                url: 'handlers/question_handler.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.status === 'success') {
                            $('#editQuestionModal').modal('hide');
                            location.reload(); // Reload to show updated question
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Error processing the request');
                    }
                },
                error: function() {
                    alert('Error updating question');
                }
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