<?php
// Include database configuration file
include('config/config.php');
require 'vendor/autoload.php'; // Include Composer autoload
use thiagoalessio\TesseractOCR\TesseractOCR; // Use the OCR class

include('preprocess_image.php');

// Include PHPMailer library
require 'send_email.php';

// Include the credit_subjects_matching.php file
include('credit_subjects_matching.php');

// Start session for error handling
session_start();

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

    // Check for valid email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Validate uploaded image for TOR
    if ($tor) {
        $allowed_types = ['image/png', 'image/jpeg', 'application/pdf'];
        if (!in_array($_FILES['tor']['type'], $allowed_types)) {
            $errors[] = "Please upload a valid image or PDF file.";
        }
    }

    // Check if files are uploaded correctly
    if ($tor && $_FILES['tor']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading Transcript of Records (TOR).";
    }
    if ($_FILES['school-id']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading School ID.";
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
            } else {
                // Imagick Preprocessing
                if (class_exists('Imagick')) {
                    try {
                        // Call the preprocessImage function to process the image
                        $processedImagePath = preprocessImage($tor_path, $upload_dir);

                        // Check if an error occurred in the preprocessing
                        if (strpos($processedImagePath, "Error") !== false) {
                            $errors[] = $processedImagePath;
                        }
                    } catch (Exception $e) {
                        $errors[] = "Error processing image: " . $e->getMessage();
                    }
                } else {
                    $errors[] = "Imagick is not installed on the server.";
                }
            }
        }

        $school_id_path = $upload_dir . uniqid() . "_" . basename($school_id);
        if (!move_uploaded_file($_FILES['school-id']['tmp_name'], $school_id_path)) {
            $errors[] = "Failed to upload School ID.";
        }

        // OCR processing only for Transferee and Shiftee, skip for Ladderized
        $is_tech = false; // Default to non-tech
        $credited_subjects = []; // Array to store credited subjects

        if (count($errors) == 0 && $student_type !== 'ladderized' && $tor) {
            // OCR on the processed image
            $ocr = new TesseractOCR($processedImagePath);
            $ocr->executable('C:/Program Files/Tesseract-OCR/tesseract.exe'); // Path to the Tesseract executable

            // Improved OCR Configuration
            $ocr->lang('eng') // Set language to English
                ->psm(11) // Set Page Segmentation Mode to "Assume a uniform block of text"
                ->config('tessedit_char_whitelist', 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.,:-()') // Whitelist common characters to improve accuracy
                ->config('textord_min_linesize', 2.5); // Helps in cases where lines might be faint or text too small

            // Run OCR and extract text
            try {
                $extractedText = $ocr->run();

                // Debugging step: Display extracted text for verification
                echo "<pre>Extracted Text from OCR:\n" . htmlentities($extractedText) . "</pre>";

                // Extract important parts from the OCR results using refined regex
                $subject_codes = [];
                $descriptions = [];
                $grades = [];
                $units = [];

                // Improved regex patterns to capture each part
                preg_match_all('/[A-Z]{2,3}\d{3}/', $extractedText, $subject_codes); // Matches subject codes like MIT626
                preg_match_all('/[A-Z][A-Za-z\s]+(?=\d{1,2}\.\d{2})/', $extractedText, $descriptions); // Matches course descriptions before grades
                preg_match_all('/\b[1-2]\.\d{2}\b/', $extractedText, $grades); // Matches numeric grades like 1.25, 2.00 but avoids larger numbers like years
                preg_match_all('/\b\d+\b(?=\scredits?)/i', $extractedText, $units); // Matches units followed by "credits"

                // Output the extracted data (for debugging or processing)
                foreach ($subject_codes[0] as $i => $code) {
                    echo "Subject: " . $code . "<br>";
                    echo "Description: " . (isset($descriptions[0][$i]) ? $descriptions[0][$i] : 'N/A') . "<br>";
                    echo "Grade: " . (isset($grades[0][$i]) ? $grades[0][$i] : 'N/A') . "<br>";
                    echo "Units: " . (isset($units[0][$i]) ? $units[0][$i] : 'N/A') . "<br><br>";
                }

                // Determine eligibility based on extracted grades
                $isEligible = determineEligibility($grades[0]);

                if (!$isEligible) {
                    $errors[] = "You are not eligible for the qualifying examination based on your grades.";
                }

                // Determine if the student has taken any programming course
                $is_tech = determineTechBackground($extractedText);

                // Determine credited subjects based on the extracted text and the desired program
                $credited_subjects = determineCreditSubjects($extractedText, $desired_program);

            } catch (Exception $e) {
                $errors[] = "Error processing the TOR: " . $e->getMessage();
            }
        }

        if (count($errors) == 0) {
            // Prepare the SQL statement for inserting the student data
            $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, middle_name, gender, dob, email, contact_number, street, student_type, previous_school, year_level, previous_program, desired_program, tor, school_id, reference_id, is_tech) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            // The number of placeholders must match the number of variables, and types should match the data types
            // Corrected to match all variables being bound
            $stmt->bind_param(
                "ssssssssssssssssi", // Updated type definition: match number of variables
                $last_name,
                $first_name,
                $middle_name,
                $gender,
                $dob,
                $email,
                $contact_number,
                $street,
                $student_type,
                $previous_school,
                $year_level,
                $previous_program,
                $desired_program,
                $processedImagePath,
                $school_id_path,
                $reference_id,
                $is_tech
            );
        
            if ($stmt->execute()) {
                // Save credited subjects to the database
                saveCreditedSubjects($conn, $reference_id, $credited_subjects);
        
                // Send confirmation email using PHPMailer
                sendRegistrationEmail($email, $reference_id); // Call the email function
        
                // Redirect to success page with reference ID
                header("Location: registration-confirmation.php?refid=$reference_id");
                exit();
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
        
    }

    // If there are errors, display errors immediately
    if (count($errors) > 0) {
        $_SESSION['registration_errors'] = $errors;
        foreach ($errors as $error) {
            echo "<p>Error: $error</p>";
        }
        exit();
    }
}

function determineEligibility($grades) {
    $eligible = true;

    foreach ($grades as $grade) {
        $grade = trim($grade);

        // Normalize possible mistakes in OCR like commas instead of dots
        $grade = str_replace(',', '.', $grade);

        if (is_numeric($grade)) {
            $gradeValue = (float)$grade;

            // Mark as ineligible if any grade is worse than 2.50
            if ($gradeValue > 2.50) {
                $eligible = false;
                break;
            }
        }
    }

    return $eligible;
}

function determineTechBackground($extractedText) {
    $normalizedText = strtolower($extractedText);
    
    // List of keywords to identify tech-related subjects
    $programming_keywords = ['programming', 'coding', 'software development', 'java', 'python', 'c++', 'introduction to programming', 'cs fundamentals'];

    // Check if any of the programming keywords are present in the extracted text
    foreach ($programming_keywords as $keyword) {
        if (strpos($normalizedText, $keyword) !== false) {
            return true; // The student has a tech background
        }
    }
    return false; // No programming subjects found
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
<body class="register-body">
<?php include('navbar.php'); ?>

<section class="form-section">
    <div class="form-group head">
        <h1>STREAM Student Registration and Document Submission</h1>
        <img src="assets/img/puplogo.png" alt="PUP CCIS Logo" class="puplogo">
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
