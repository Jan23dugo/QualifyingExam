<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eligibility Checker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php include 'navbar.php'?>
    
    <div class="EC-container">
        <header class="EC-header">
            <h1>Eligibility Checker</h1>
        </header>
        <p>To check, input the <strong>Reference ID</strong> that we sent on your Email Address.</p>
        <div class="eligibility-form">
            <label for="reference-id">Reference ID: </label>
            <div class="input-container">
                <input type="text" id="reference-id" placeholder="Enter your reference ID">
                <button onclick="checkEligibility()">Check Eligibility</button>
            </div>
        </div>

        <div class="eligibility-result" >
            <div id="results-section" style="display: none;">
                
                <h2>RESULTS</h2>
                <hr>

                <!-- Table-like layout for results -->
                <div class="eligibility-result-table">
                    <!-- Headers -->
                    <div class="result-label">Student Name</div>
                    <div class="result-label">Program Applying To</div>
                    <div class="result-label">Reference ID</div>
                </div>
                <hr>
                <div class="eligibility-result-table">
                    <div class="result-value" id="student-name"></div>
                    <div class="result-value" id="program-applying-to"></div>
                    <div class="result-value" id="referenceid"></div>
                </div>
            </div>
            <div class="not-eligible">
                <p id="not-eligible-msg" style="display: none;">I'm sorry, but you're not eligible. <br>If you haven't filled out the registration form yet, <a href="register.html" class="click-register">click here</a>!</p>
            </div>
        </div>
    </div>

    <script>
        const eligibleIDs = ['REF12345', 'REF67890', 'REF11111'];

        function checkEligibility() {
            var referenceId = document.getElementById("reference-id").value;
            var studentName = document.getElementById("student-name");
            var programApplyingTo = document.getElementById("program-applying-to");
            var referenceID = document.getElementById("referenceid")
            var notEligibleMsg = document.getElementById("not-eligible-msg");
            var resultsSection = document.getElementById("results-section");

            notEligibleMsg.style.display = "none";
            studentName.innerText = "";
            programApplyingTo.innerText = "";
            referenceID.innerText = "";
            resultsSection.style.display = "none";

            if (referenceId.trim() === "") {
                alert("Please enter a Reference ID before checking eligibility.");
                return;
            }

            // Mock data to demonstrate result population
            if (eligibleIDs.includes(referenceId)) {
                studentName.innerText = "John Doe";  // Example data
                programApplyingTo.innerText = "Bachelor of Science in Information Technology";  // Example program
                referenceID.innerText = "REF12345";
                
                resultsSection.style.display = "block";
            } else {
                notEligibleMsg.style.display = "block";
            }
        }
    </script>
</body>
</html>
