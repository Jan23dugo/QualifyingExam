<?php
session_start();
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
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
            position: relative;
        }

        .close {
            color: #aaa;
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

        .success-container {
            text-align: center;
            padding: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .btn-secondary {
            background-color: #008CBA;
        }

        .btn-secondary:hover {
            background-color: #007399;
        }

        .success-message {
            color: #4CAF50;
            font-size: 1.2em;
            margin-bottom: 20px;
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
        }

        .eligible {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .not-eligible {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <?php if (isset($_SESSION['success'])): ?>
            <script>
                window.onload = function() {
                    showEligibilityModal();
                }
            </script>
        <?php endif; ?>

        <!-- Eligibility Modal -->
        <div id="eligibilityModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEligibilityModal()">&times;</span>
                <h2>Registration Status</h2>
                <?php
                if (isset($_SESSION['is_eligible'])) {
                    if ($_SESSION['is_eligible']) {
                        echo "<div class='eligibility-status eligible'>";
                        echo "<p>Congratulations! You are eligible.</p>";
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
                <button class="btn btn-secondary" onclick="closeEligibilityModal()">Close</button>
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

        <a href="registerFront.php" class="btn">Back to Registration</a>
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
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
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
