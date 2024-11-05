<?php
session_start();

// Clear any old session messages at the start
if (isset($_SESSION['success']) && isset($_SESSION['ocr_error'])) {
    // If both exist, prioritize showing the error
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration Successful</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(250,243,213,0.60);
        }

        .modal-content {
            background-color: #73343a;
            color: #f4d6a3;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
            position: relative;
        }

        .close {
            color: #f4d6a3;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .confirm-container {
            max-width: 800px;
            min-height: 200px;
            margin: 0 auto;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            background-color: #73343a;
            color: #faf3d5;
        }

        .success-container {
            text-align: center;
            padding: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            background-color: #f4d6a3;
            color: #73343a;
            font-weight: bolder;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #73343a;
            color: #f4d6a3;
        }

        .btn-secondary {
            background-color: #f4d6a3;
        }

        .btn-secondary:hover {
            background-color: #faf3d5;
            color: #73343a;
        }

        .success-message {
            color: #faf3d5;
            margin-top: 10px;
            margin: 30px 0;
            border-radius: 10px;
            text-align: center;
        }

        .success-message h2 {
            font-size: 35px;
            font-weight: bold;
            text-align: center;
            color: #faf3d5;
        }

        .success-message p {
            font-size: 18px;
            text-align: center;
        }

        .matches-info {
            margin: 20px 0;
            text-align: left;
        }

        .matches-info div {
            padding: 5px 0;
        }

        .eligibility-status {
            font-size: 1.1em;
            margin: 20px 0;
            padding: 10px;
            border-radius: 5px;
            align-items: center;
            text-align: center;
        }

        .eligible {
            background-color: #e5b168;
            border-color: #c3e6cb;
            color: black;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .eligible h2 {
            color: #73343a;
        }

        .not-eligible {
            background-color: #e5b168;
            border-color: transparent;
            color: #73343a;
            padding: 15px;
            margin: 20px auto;
            border: 1px solid transparent;
            border-radius: 4px;
            align-items: center;
            text-align: center;
        }

        .not-eligible h3 {
            color: #73343a;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .not-eligible p, .not-eligible li {
            color: #73343a;
            text-align: center;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .not-eligible ul {
            list-style-position: inside;
            padding-left: 0;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    
    <?php include 'navbar.php'?>
    
    <div class="success-container">
        <script>
            window.onload = function() {
                <?php if (isset($_SESSION['success'])) {
                    echo "showEligibilityModal();";
                } ?>
            }
        </script>

        <!-- Eligibility Modal -->
        <div id="eligibilityModal" class="confirm-container">
            <div class="success-message">
                <h2>Registration Status</h2>
                <?php
                if (isset($_SESSION['ocr_error'])) {
                    echo "<div class='eligibility-status not-eligible'>";
                    echo "<h2>Document Verification Error</h2>";
                    echo "<p>" . htmlspecialchars($_SESSION['ocr_error']) . "</p>";
                    echo "<p>Please ensure you have uploaded:</p>";
                    echo "<ul>";
                    echo "<li>A valid Transcript of Records (TOR)</li>";
                    echo "<li>A clear, readable copy of the document</li>";
                    echo "<li>The document contains your grades and subject information</li>";
                    echo "<li>The image is not blurry or distorted</li>";
                    echo "</ul>";
                    echo "</div>";
                    echo "<div style='text-align: center; margin-top: 20px;'>";
                    echo "<a href='registerFront.php' class='btn btn-secondary'>Try Again</a>";
                    echo "<a href='index.php' class='btn btn-secondary'>Home</a>";
                    echo "</div>";
                    unset($_SESSION['ocr_error']);
                } elseif (isset($_SESSION['is_eligible'])) {
                    if ($_SESSION['is_eligible']) {
                        echo "<div class='eligibility-status eligible'>";
                        echo "<h2>Your registration has been submitted successfully!</h2>";
                        echo "<p>Congratulations! Based on your grades you are qualified to take the Qualifying Exam</p>";
                        if (isset($_SESSION['success'])) {
                            echo "<p>" . htmlspecialchars($_SESSION['success']) . "</p>";
                        }
                        echo "</div>";
                        echo "<button class='btn' onclick='showCreditedSubjectsModal()'>View Credited Subjects</button>";
                    } else {
                        echo "<div class='eligibility-status not-eligible'>";
                        echo "<p>Registration Completed</p>";
                        if (isset($_SESSION['eligibility_message'])) {
                            echo "<p>" . htmlspecialchars($_SESSION['eligibility_message']) . "</p>";
                        }
                        echo "</div>";
                    }
                    unset($_SESSION['is_eligible']);
                    unset($_SESSION['success']);
                    unset($_SESSION['eligibility_message']);
                }
                ?>
            </div>
        </div>

        <!-- Credited Subjects Modal -->
        <div id="creditedSubjectsModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeCreditedSubjectsModal()">&times;</span>
                <h2>Credited Subjects</h2>
                <?php
                if (isset($_SESSION['matches'])) {
                    echo "<div class='matches-info'>";
                    foreach ($_SESSION['matches'] as $match) {
                        echo "<div>" . htmlspecialchars($match) . "</div>";
                    }
                    echo "</div>";
                    unset($_SESSION['matches']);
                }
                ?>
                <button class="btn" onclick="closeCreditedSubjectsModal()">Close</button>
            </div>
        </div>

        <!-- Debug Info Modal (if needed) -->
        <?php if (isset($_SESSION['debug_output'])): ?>
        <div id="debugModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeDebugModal()">&times;</span>
                <h2>Debug Information</h2>
                <div class="debug-info">
                    <?php 
                    echo $_SESSION['debug_output'];
                    unset($_SESSION['debug_output']);
                    ?>
                </div>
                <button class="btn" onclick="closeDebugModal()">Close</button>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Modal control functions
        function showEligibilityModal() {
            document.getElementById('eligibilityModal').style.display = 'block';
        }

        function closeEligibilityModal() {
            document.getElementById('eligibilityModal').style.display = 'none';
        }

        function showCreditedSubjectsModal() {
            document.getElementById('creditedSubjectsModal').style.display = 'block';
            document.getElementById('eligibilityModal').style.display = 'none';
        }

        function closeCreditedSubjectsModal() {
            document.getElementById('creditedSubjectsModal').style.display = 'none';
            document.getElementById('eligibilityModal').style.display = 'block';
        }

        function showDebugModal() {
            document.getElementById('debugModal').style.display = 'block';
        }

        function closeDebugModal() {
            document.getElementById('debugModal').style.display = 'none';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            var modal = document.getElementById('ocrErrorModal');
            if (event.target == modal) {
                closeOcrErrorModal();
            }
        }

        // Handle escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>
