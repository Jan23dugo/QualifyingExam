<?php
// Start session at the very beginning of the file
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCIS Qualifying Examination</title>
    <link rel="stylesheet" href="assets/css/style.css">  
    <style>
        /* Modal styles */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; 
        z-index: 1000; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
    }

    .modal-content {
        background-color: #fefefe;
        margin: 10% auto; /* 10% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 80%; /* Could be more or less, depending on screen size */
        max-width: 500px;
        text-align: left;
    }

    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close-btn:hover,
    .close-btn:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>
</head>

<body class="register">
    
    <?php include('navbar.php'); ?>

    <div id="infoModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>CCIS Qualifying Examination Information</h2>
        <p>Welcome to the CCIS Qualifying Examination registration. Please note the following requirements:</p>
        <strong><p>Exam Registration Requirements:</p></strong>
        <ul>
            <li>Must <strong>not</strong> have a <strong>failing grade or grade lower than 2.00 (or 85)</strong></li>
            <li>Must be an <strong>incoming Second Year if transferee or shiftee</strong> (must have completed at least 2 semester).
                <br>If ladderized, must be <strong>graduated on their 3-year diplomat program</strong>. </li>
            <li>Must have <strong>no failing grade, dropped, incomplete, and withdrawn mark</strong> in any subjects.</li>
        </ul>
        <strong><p>Required Documents:</p></strong>
        <ul>
            <li>Submit a copy of your <strong>Transcript of Records (TOR), or Informative or Certified Copy of Grades</strong> (initial requirement of the college only) </li>
            <li>Provide a <strong>valid School ID</strong></li>
            <li>Ensure all contact information is accurate</li>
            <li>Select the correct "Student Type" (Transferee, Shiftee, or Ladderized) as it affects the required information</li>
        </ul>
        <p>After completing the registration, you will receive an email with further instructions for the examination.</p>
    </div>
</div>

<section class="form-section">
    <div class="header-logo">
        <img src="assets/img/ccislogo.png" alt="PUP Logo" class="ccislogo">
        <h1>STREAMS Student Registration and Document Submission</h1>
        <img src="assets/img/puplogo.png" alt="PUP CCIS Logo" class="puplogo">
    </div>


    <?php
    if (isset($_SESSION['debug_output'])) {
        echo "<div class='debug-output'>";
        echo "<h3>Debug Information:</h3>";
        echo "<pre>" . htmlspecialchars($_SESSION['debug_output']) . "</pre>";
        echo "</div>";
        unset($_SESSION['debug_output']);
    }

    if (isset($_SESSION['last_error'])) {
        echo "<div class='error'>" . htmlspecialchars($_SESSION['last_error']) . "</div>";
        unset($_SESSION['last_error']);
    }
    ?>

    <!-- Form -->
    <form id="multi-step-form" action="registerBack.php" method="POST" enctype="multipart/form-data">
        <div class="step active">
            <div class="form-field">
                <label for="student_type">Student Type</label>
                <select id="student_type" name="student_type" required onchange="handleStudentTypeChange()">
                    <option value="">-- Select Student Type --</option>
                    <option value="transferee">Transferee</option>
                    <option value="shiftee">Shiftee</option>
                    <option value="ladderized">Ladderized</option>
                </select>
            </div>
            <div class="buttons">
                <button type="button" class="nxt-btn" onclick="validateStep()">Next</button>
            </div>
        </div>

        <div class="step">
            <h2>Personal Details</h2>
            <div class="form-group">
                <div class="form-field">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-field">
                    <label for="first_name">Given Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-field">
                    <label for="middle_name">Middle Name (Optional)</label>
                    <input type="text" id="middle_name" name="middle_name">
                </div>
                <div class="form-field">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
                <div class="form-field">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="">--Select Gender--</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            <div class="buttons">
                <button type="button" class="prev-btn" onclick="prevStep()">Previous</button>
                <button type="button" class="nxt-btn" onclick="validateStep()">Next</button>
            </div>
        </div>

        <!-- Step 2: Contact Details -->
        <div class="step">
            <h2>Contact Details</h2>
            <div class="form-group">
                <div class="form-field">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-field">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" required>
                </div>
                <div class="form-field">
                    <label for="street">Address</label>
                    <input type="text" id="street" name="street" required>
                </div>
            </div>
            <div class="buttons">
                <button type="button" class="prev-btn" onclick="prevStep()">Previous</button>
                <button type="button" class="nxt-btn" onclick="validateStep()">Next</button>
            </div>
        </div>

        <!-- Step 3: Academic Details -->
        <div class="step">
            <h2>Academic Information</h2>
            <div class="form-group">
                <div class="form-field" id="year-level-field">
                    <label for="year_level">Current Year Level</label>
                    <input type="number" id="year_level" name="year_level" required>
                </div>
                <div class="form-field" id="previous-school-field">
    <label for="previous_school">Name of Previous School</label>
    <select id="previous_school" name="previous_school" required>
        <option value="">--Select Previous University--</option>
        <option value="AMA University">AMA University (AMA)</option>
        <option value="Technological University of the Philippines">Technological University of the Philippines (TUP)</option>
        <option value="Polytechnic University of the Philippines">Polytechnic University of the Philippines (PUP)</option>
        <option value="University of Perpetual">University of Perpetual (UP)</option>
        <option value="University of Perpetual">University of the Philippines (UP)</option>
        <option value="Diploma in Information and Communication Technology">Diploma in Information and Communication Technology (DICT)</option>
    </select>
</div>
                <div class="form-field" id="previous-program-field">
                    <label for="previous_program">Name of Previous Program</label>
                    <select id="previous_program" name="previous_program" required>
                        <option value="">--Select Previous Program--</option>
                    </select>
                </div>

                <div class="form-field" id="program-apply-field">
                    <label for="desired_program">Name of Program Applying To</label>
                    <select id="desired_program" name="desired_program" required>
                        <option value="">--Select Desired Program--</option>
                        <option value="BSCS">Bachelor of Science in Computer Science (BSCS)</option>
                        <option value="BSIT">Bachelor of Science in Information Technology (BSIT)</option>
                    </select>
                </div>
            </div>
            <div class="buttons">
                <button type="button" class="prev-btn" onclick="prevStep()">Previous</button>
                <button type="button" class="nxt-btn" onclick="validateStep()">Next</button>
            </div>
        </div>

        <!-- Step 4: Upload Documents -->
        <div class="step">
            <div class="form-group">
                <div class="form-field" id="tor-field">
                    <label for="tor">Upload Copy of Transcript of Records (TOR)</label>
                    <input type="file" id="tor" name="tor" required>
                </div>
                <div class="form-field">
                    <label for="school_id">Upload Copy of School ID</label>
                    <input type="file" id="school_id" name="school_id" required>
                </div>
            </div>
            <div class="buttons">
                <button type="button" class="prev-btn" onclick="prevStep()">Previous</button>
                <button type="submit" class="nxt-btn">Submit</button>
            </div>
        </div>
    </form>
</section>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const previousProgramSelect = document.getElementById("previous_program");

    // Function to populate the dropdown from JSON
    function populatePreviousProgramSelect() {
        fetch('assets/data/courses.json') // Ensure this path is correct
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                // Clear existing options, but keep the default option
                previousProgramSelect.innerHTML = `<option value="">--Select Previous Program--</option>`;
                
                // Populate dropdown with courses from JSON
                data.forEach(course => {
                    const option = document.createElement("option");
                    option.value = course; // Set the value to the course name
                    option.textContent = course; // Set the displayed text to the course name
                    previousProgramSelect.appendChild(option); // Add option to the dropdown
                });

                // Call the function to handle student type change after populating
                handleStudentTypeChange(); 
            })
            .catch(error => console.error('Error loading programs:', error));
    }

    // This function should be defined here to ensure it's in the global scope
    window.validateStep = function() {
        const activeStep = document.querySelector('.step.active');
        const inputs = activeStep.querySelectorAll('input, select');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.checkValidity()) {
                isValid = false;
                input.reportValidity(); // This will show validation messages
            }
        });

        if (isValid) {
            nextStep(); // Move to the next step if valid
        }
    };

    // This function should be defined here to ensure it's in the global scope
    window.nextStep = function() {
        const currentStep = document.querySelector('.step.active');
        const nextStep = currentStep.nextElementSibling;

        if (nextStep) {
            currentStep.classList.remove('active');
            nextStep.classList.add('active');
        }
    };

    // This function should be defined here to ensure it's in the global scope
    window.prevStep = function() {
        const currentStep = document.querySelector('.step.active');
        const prevStep = currentStep.previousElementSibling;

        if (prevStep) {
            currentStep.classList.remove('active');
            prevStep.classList.add('active');
        }
    };

    // Handle student type change logic
    window.handleStudentTypeChange = function() {
        const studentType = document.getElementById('student_type').value; // Get selected student type

        if (studentType === 'ladderized') {
            // Set previous program to DICT
            previousProgramSelect.value = 'Diploma in Information and Communication Technology (DICT)'; // Ensure DICT is in your JSON options if you're setting it directly
        } else {
            previousProgramSelect.value = ''; // Reset to default if not ladderized
        }
    };

    // Initially populate the previous program dropdown
    populatePreviousProgramSelect();

    // Add event listener for student type selection
    document.getElementById('student_type').addEventListener('change', handleStudentTypeChange);
});

    // Function to open the modal
    function openModal() {
        document.getElementById("infoModal").style.display = "block";
    }

    // Function to close the modal
    function closeModal() {
        document.getElementById("infoModal").style.display = "none";
    }

    // Open the modal when the page loads
    window.onload = function() {
        openModal();
    };

</script>


</body>
</html>
