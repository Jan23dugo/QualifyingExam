<?php
// Include database configuration file
include('config/config.php');
require 'vendor/autoload.php'; // Include Composer autoload
use thiagoalessio\TesseractOCR\TesseractOCR;

include('preprocess_image.php');
require 'send_email.php';
include('grading_utilities.php');
include('credit_subjects_logic.php');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize the $errors array to avoid undefined variable error
$errors = []; // <-- Add this to ensure $errors is always an array

// Function to aggressively insert missing spaces and clean up OCR text
function insertMissingSpaces($text) {
    // Add space between subject codes and descriptions (e.g., COMP20033 -> COMP 20033)
    $text = preg_replace('/([A-Z]{2,4})(\d{3,5})/', '$1 $2', $text);

    // Add space between descriptions and faculty names, units, or codes if merged
    $text = preg_replace('/([a-z])([A-Z])/', '$1 $2', $text);
    $text = preg_replace('/([a-z])(\d)/', '$1 $2', $text);

    // Add space between numbers and letters (e.g., 30BSITI -> 30 BSITI)
    $text = preg_replace('/(\d)([A-Z])/', '$1 $2', $text);

    // Add space between description and numbers, such as units (e.g., "Programming2" -> "Programming 2")
    $text = preg_replace('/([a-zA-Z]+)(\d)/', '$1 $2', $text);

    // Normalize multiple spaces into a single space
    $text = preg_replace('/\s+/', ' ', $text);

    return trim($text);
}

// Flexible regex to extract subject codes, descriptions, and units from cleaned OCR text
function extractFlexibleSubjects($text) {
    $credited_subjects = [];

    // Split text by occurrences of subject codes (letter-number patterns)
    $lines = preg_split('/(?=[A-Z]{2,4}\s?\d{3,5})/', $text);

    foreach ($lines as $line) {
        // Attempt to capture subject code, description, and units flexibly
        if (preg_match('/([A-Z]{2,4}\s?\d{3,5})\s+([A-Za-z\s,\'-]+?)\s+(\d+\.\d+|\d+)/i', $line, $match)) {
            $subject_code = $match[1];
            $description = $match[2];
            $units = $match[3];
            $grades = $match[4];
            // Add to the credited subjects array
            $credited_subjects[] = [
                'subject_code' => $subject_code,
                'description' => $description,
                'units' => $units,
                'grades' => $grades
            ];
        }
    }

    return $credited_subjects;
}


// Define the saveCreditedSubjects function
function saveCreditedSubjects($conn, $reference_id, $credited_subjects) {
    if (!empty($credited_subjects)) {
        foreach ($credited_subjects as $subject) {
            $stmt = $conn->prepare("INSERT INTO credited_subjects (reference_id, subject_code, subject_description, units) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssd", $reference_id, $subject['subject_code'], $subject['description'], $subject['units']);
            if (!$stmt->execute()) {
                throw new Exception("Error saving credited subject: " . $stmt->error);
            }
            $stmt->close();
        }
    }
}

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
                // Preprocess the image to make OCR more accurate
                $processedImagePath = preprocessImage($tor_path); // Only pass the image path
    
           // OCR on the preprocessed image
           $ocr = new TesseractOCR($tor_path);
           $ocr->lang('eng') 
               ->psm(6) // Set to block of text mode
               ->config('tessedit_char_whitelist', 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.,:-');

           try {
               $extractedText = $ocr->run();

               // Insert missing spaces to clean up the OCR text
               $cleanedText = insertMissingSpaces($extractedText);

               // Debugging: Output cleaned text
               echo "<pre>Cleaned Text from OCR:\n" . htmlentities($cleanedText) . "</pre>";

               // Extract the subjects from the cleaned text
               $credited_subjects = extractFlexibleSubjects($cleanedText);

               // Debugging: Output credited subjects
               echo "<pre>Credited Subjects:\n";
               print_r($credited_subjects);
               echo "</pre>";

           } catch (Exception $e) {
               $errors[] = "Error processing the TOR: " . $e->getMessage();
           }
       }
   }


        $school_id_path = $upload_dir . uniqid() . "_" . basename($school_id);
        if (!move_uploaded_file($_FILES['school-id']['tmp_name'], $school_id_path)) {
            $errors[] = "Failed to upload School ID.";
        }

        if (count($errors) == 0) {
            // Prepare the SQL statement for inserting the student data
            $stmt = $conn->prepare("INSERT INTO students (last_name, first_name, middle_name, gender, dob, email, contact_number, street, student_type, previous_school, year_level, previous_program, desired_program, tor, school_id, reference_id, is_tech) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // Set is_tech explicitly as 0 or 1
            $is_tech = ($student_type === 'tech') ? 1 : 0;

            $stmt->bind_param(
                "ssssssssssssssssi", 
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
                $tor_path,
                $school_id_path,
                $reference_id,
                $is_tech
            );
        
            if ($stmt->execute()) {
                // Save credited subjects to the database
                saveCreditedSubjects($conn, $reference_id, $credited_subjects);
        
                // Send confirmation email using PHPMailer
                sendRegistrationEmail($email, $reference_id);
        
                // Redirect to success page with reference ID
                // Comment this during debugging
                header("Location: registration-confirmation.php?refid=$reference_id");
                exit();
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // If there are errors, display them
    if (count($errors) > 0) {
        $_SESSION['registration_errors'] = $errors;
        foreach ($errors as $error) {
            echo "<p>Error: $error</p>";
        }
        exit();
    }
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
    <form id="multi-step-form" action="register-test.php" method="POST" enctype="multipart/form-data">
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
                    <select id="previous-school" name="previous-school" required>
                        <option value="">--Select Previous University--</option>
                        <option value="AMA">AMA University (AMA)</option>
                        <option value="TUP">Technological University of the Philippines (TUP)</option>
                        <option value="PUP">University of the Philippines (PUP)</option>
                        <option value="DICT">Diploma in Information and Communication Technology (DICT)</option>
                    </select>
                </div>

                <div class="form-field" id="previous-program-field">
                    <label for="previous-program">Name of Previous Program</label>
                    <select id="previous-program" name="previous-program" required>
                        <option value="">--Select Previous Program--</option>
                    </select>
                </div>

                <div class="form-field" id="program-apply">
                    <label for="program-apply">Name of Program Applying To</label>
                    <select id="program-apply" name="program-apply" required>
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
document.addEventListener('DOMContentLoaded', function() {
    const previousProgramSelect = document.getElementById("previous-program");

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
        const studentType = document.getElementById('student-type').value; // Get selected student type

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
    document.getElementById('student-type').addEventListener('change', handleStudentTypeChange);
});
</script>


</body>
</html>
