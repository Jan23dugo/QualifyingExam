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
                                                    <div class="ms-3">
                                                        <strong>Correct Answer: </strong><?php echo ucfirst($row['correct_answer']); ?>
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

        // Handle question editing (you'll need to implement this based on your requirements)
        $('.edit-question').click(function() {
            const questionId = $(this).data('question-id');
            // Implement edit functionality
            // You might want to open a modal with the question details for editing
        });
    });
    </script>
</body>
</html> 