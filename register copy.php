<?php 
// Include database configuration file
include('config/config.php');
require 'vendor/autoload.php'; // Include Composer autoload
use thiagoalessio\TesseractOCR\TesseractOCR; // Use the OCR class

// Include PHPMailer library
require 'send_email.php';

// Initialize variables for error messages or success message
$errors = [];
$success = "";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data and validate
    $student_type = mysqli_real_escape_string($conn, $_POST['student-type']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last-name']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first-name']);
    $middle_name = mysqli_real_escape_string($conn, $_POST['middle-name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $gender = mysqli_real_escape_string($conn, $_POST['sex']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact-number']);
    $street = mysqli_real_escape_string($conn, $_POST['address-street']);
    $previous_school = mysqli_real_escape_string($conn, $_POST['previous-school']);
    $previous_program = mysqli_real_escape_string($conn, $_POST['previous-program']);
    $desired_program = mysqli_real_escape_string($conn, $_POST['program-apply']);
    
    // Handle different conditions for Ladderized students
    if ($student_type === 'ladderized') {
        $year_level = NULL;
        $previous_program = 'DICT'; // Automatically set to DICT for ladderized
    } else {
        $year_level = mysqli_real_escape_string($conn, $_POST['year-level']);
    }

    // Handle file uploads
    $upload_dir = __DIR__ . "/uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $tor = isset($_FILES['tor']['name']) ? $_FILES['tor']['name'] : null;
    $school_id = $_FILES['school-id']['name'];

    // Check for errors before proceeding
    if (empty($last_name) || empty($first_name) || empty($gender) || empty($dob) || empty($email) || empty($student_type)) {
        $errors[] = "Please fill out all required fields.";
    }

    // Generate a unique Reference ID
    $reference_id = uniqid('STU-'); // This will generate something like STU-605c1c1c7a7f7

    // If no errors, process the form data
    if (count($errors) == 0) {
        // Move uploaded files to the designated directory
        if ($tor) {
            $tor_path = $upload_dir . uniqid() . "_" . basename($tor);
            if (!move_uploaded_file($_FILES['tor']['tmp_name'], $tor_path)) {
                $errors[] = "Failed to upload Transcript of Records (TOR).";
            }
        }
        $school_id_path = $upload_dir . uniqid() . "_" . basename($school_id);
        if (!move_uploaded_file($_FILES['school-id']['tmp_name'], $school_id_path)) {
            $errors[] = "Failed to upload School ID.";
        }

        // OCR processing only for Transferee and Shiftee, skip for Ladderized
        if (count($errors) == 0 && $student_type !== 'ladderized' && $tor) {
            // Preprocess image before OCR for better accuracy (optional)
            try {
                $imagick = new \Imagick($tor_path);
                $imagick->setImageType(\Imagick::IMGTYPE_GRAYSCALE);
                $imagick->adaptiveThresholdImage(100, 100, 1);
                $processedImagePath = $upload_dir . 'processed_' . uniqid() . '_' . basename($tor);
                $imagick->writeImage($processedImagePath);
            } catch (Exception $e) {
                $errors[] = "Error preprocessing the TOR: " . $e->getMessage();
            }

            // OCR only if preprocessing is successful
            if (count($errors) == 0) {
                // Instantiate Tesseract OCR
                $ocr = new TesseractOCR($processedImagePath);

                // Set language and configuration for better accuracy
                $ocr->lang('eng') // Specify language
                    ->psm(6);      // Assume a uniform block of text (PSM 6)

                // Run OCR and extract text
                try {
                    $extractedText = $ocr->run();

                    // Print the extracted text (for debugging purposes)
                    echo "<pre>$extractedText</pre>";

                    // Determine eligibility based on extracted text
                    $isEligible = determineEligibility($extractedText);

                    if (!$isEligible) {
                        $errors[] = "You are not eligible for the qualifying examination based on your grades.";
                    }
                } catch (Exception $e) {
                    $errors[] = "Error processing the TOR: " . $e->getMessage();
                }
            }
        }

        if (count($errors) == 0) {
            // Insert the data along with the generated Reference ID into the database
            $sql = "INSERT INTO students (last_name, first_name, middle_name, gender, dob, email, contact_number, street, student_type, previous_school, year_level, previous_program, desired_program, tor, school_id, reference_id)
                    VALUES ('$last_name', '$first_name', '$middle_name', '$gender', '$dob', '$email', '$contact_number', '$street', '$student_type', '$previous_school', '$year_level', '$previous_program', '$desired_program', '$tor_path', '$school_id_path', '$reference_id')";

            if (mysqli_query($conn, $sql)) {
                // Send confirmation email using PHPMailer
                sendRegistrationEmail($email, $reference_id); // Call the email function

                // Redirect to success page with reference ID
                header("Location: registration-confirmation.php?refid=$reference_id");
                exit();
            } else {
                $errors[] = "Error: " . mysqli_error($conn);
            }
        }
    }

    // If there are errors, redirect to an error page with errors as query parameter
    if (count($errors) > 0) {
        $_SESSION['registration_errors'] = $errors;
        header("Location: registration-error.php");
        exit();
    }
} // Ensure this closing brace is present

function determineEligibility($extractedText) {
    $normalizedText = strtolower($extractedText);
    
    // Extract possible grades (assuming grades are either numbers, percentages, or letters)
    preg_match_all('/(\d+\.\d+|\d+|[a-fA-F]|\d+%)\b/', $normalizedText, $matches);
    $grades = $matches[0];

    // Define logic to determine eligibility based on grading systems
    $eligible = true;
    foreach ($grades as $grade) {
        // Clean up grade value to remove extra characters
        $grade = trim($grade);
        if (is_numeric($grade)) {
            $gradeValue = (float)$grade;
            // Handle numeric grades in different ranges and ensure equivalency to 86%
            if ($gradeValue > 1.50 && $gradeValue <= 3.0) {
                // If grade is worse than 1.50, mark as ineligible
                $eligible = false;
                break;
            } elseif ($gradeValue >= 5.0) {
                // Failing grades or low passing in some systems
                $eligible = false;
                break;
            }
        } elseif (preg_match('/\d+%/', $grade)) {
            // Handle percentage grades
            $percentage = (int)rtrim($grade, '%');
            if ($percentage < 86) {
                $eligible = false;
                break;
            }
        } elseif (preg_match('/[a-f]/i', $grade)) {
            // Handle letter grades (A-F)
            switch (strtoupper($grade)) {
                case 'A':
                case 'A+':
                case 'A-':
                case 'B+':
                case 'B':
                    // Excellent to very good
                    break;
                case 'C':
                case 'C+':
                case 'D':
                case 'F':
                    // Grades C, D, F are considered ineligible
                    $eligible = false;
                    break;
            }
        } elseif (preg_match('/[1-5]\.\d+/', $grade)) {
            // Handle fractional grading system, such as 4.0 (Excellent) or 3.5 (Very Good)
            $fractionGrade = (float)$grade;
            if ($fractionGrade < 2.0) {
                // Grades below 2.0 are considered not acceptable
                $eligible = false;
                break;
            }
        }
    }
    return $eligible;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCIS Qualifying Examination</title>
    <link rel="stylesheet" href="assets/css/style.css">  
</head>
<body>
<?php include('navbar.php'); ?>

<section class="form-section">
    <div class="form-group head">
        <h1>STREAM Student Registration and Document Submission</h1>
        <img src="assets/img/PUP_CCIS_logo.png" alt="PUP CCIS Logo" class="puplogo">
    </div>

    <!-- Display errors or success message -->
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="success">
            <p><?php echo $success; ?></p>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form id="multi-step-form" action="register.php" method="POST" enctype="multipart/form-data">
        <div class="step active">
            <div class="form-field">
                <label for="student-type">Student Type</label>
                <select id="student-type" name="student-type" required onchange="handleStudentTypeChange()">
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
                    <label for="last-name">Last Name</label>
                    <input type="text" id="last-name" name="last-name" required>
                </div>
                <div class="form-field">
                    <label for="first-name">Given Name</label>
                    <input type="text" id="first-name" name="first-name" required>
                </div>
                <div class="form-field">
                    <label for="middle-name">Middle Name (Optional)</label>
                    <input type="text" id="middle-name" name="middle-name">
                </div>
                <div class="form-field">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
                <div class="form-field">
                    <label for="sex">Sex</label>
                    <select id="sex" name="sex" required>
                        <option value="">--Select Sex--</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
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
                    <label for="contact-number">Contact Number</label>
                    <input type="text" id="contact-number" name="contact-number" required>
                </div>
                <div class="form-field">
                    <label for="address">Address</label>
                    <input type="text" id="address-street" name="address-street" required>
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
                    <label for="year-level">Current Year Level</label>
                    <input type="number" id="year-level" name="year-level" required>
                </div>
                <div class="form-field" id="previous-school-field">
                    <label for="previous-school">Name of Previous School</label>
                    <input type="text" id="previous-school" name="previous-school" required>
                </div>
                <div class="form-field" id="previous-program-field">
                    <label for="previous-program">Name of Previous Program</label>
                    <select id="previous-program" name="previous-program" required>
                        <option value="">--Select Previous Program--</option>
                        <option value="DICT">Diploma in Information and Communication Technology (DICT)</option>
                        <option value="BSA">Bachelor of Science in Accountancy</option>
                        <!-- Other programs for transferee and shiftee students -->
                    </select>
                </div>
                <div class="form-field" id="program-apply">
                    <label for="program-apply">Name of Program Applying To</label>
                    <select id="program-apply" name="program-apply" required>
                        <option value="">--Select Desired Program--</option>
                        <option value="BSCS">Bachelor of Science in Computer Science</option>
                        <option value="BSIT">Bachelor of Science in Information Technology</option>
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
            <h2>Upload Documents</h2>
            <div class="form-group">
                <div class="form-field" id="tor-field">
                    <label for="tor">Upload Copy of Transcript of Records (TOR)</label>
                    <input type="file" id="tor" name="tor" required>
                </div>
                <div class="form-field">
                    <label for="school-id">Upload Copy of School ID</label>
                    <input type="file" id="school-id" name="school-id" required>
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
    function validateStep() {
        // Get the active step
        const activeStep = document.querySelector('.step.active');
        const inputs = activeStep.querySelectorAll('input, select');
        let isValid = true;

        // Loop through inputs to check if they are valid
        inputs.forEach(input => {
            if (!input.checkValidity()) {
                isValid = false;
                input.reportValidity(); // This will display validation messages if input is invalid
            }
        });

        // Proceed to the next step if valid
        if (isValid) {
            nextStep();
        }
    }

    function nextStep() {
        const currentStep = document.querySelector('.step.active');
        const nextStep = currentStep.nextElementSibling;

        if (nextStep) {
            currentStep.classList.remove('active');
            nextStep.classList.add('active');
        }
    }

    function prevStep() {
        const currentStep = document.querySelector('.step.active');
        const prevStep = currentStep.previousElementSibling;

        if (prevStep) {
            currentStep.classList.remove('active');
            prevStep.classList.add('active');
        }
    }

    function handleStudentTypeChange() {
        const studentType = document.getElementById('student-type').value;
        const yearLevelField = document.getElementById('year-level-field');
        const previousProgramField = document.getElementById('previous-program-field');
        const previousProgramSelect = document.getElementById('previous-program');

        if (studentType === 'ladderized') {
            // Hide year level field and limit previous program to DICT
            yearLevelField.style.display = 'none';
            previousProgramSelect.innerHTML = '<option value="DICT">Diploma in Information and Communication Technology (DICT)</option>';
        } else {
            // Show year level field and reset previous programs to all options
            yearLevelField.style.display = 'block';
            previousProgramSelect.innerHTML = ` 
                <option value="">--Select Previous Program--</option>
                <option value="BSA">Bachelor of Science in Accountancy</option>
                <option value="BSBAFM">Bachelor of Science in Business Administration Major in Financial Management</option>
                <!-- Other programs... -->
            `;
        }
    }

    // Call this on page load in case the form is revisited
    document.addEventListener('DOMContentLoaded', function() {
        handleStudentTypeChange();
    });
</script>

</body>
</html>