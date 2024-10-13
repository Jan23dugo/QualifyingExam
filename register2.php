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
    $year_level = mysqli_real_escape_string($conn, $_POST['year-level']);
    $previous_school = mysqli_real_escape_string($conn, $_POST['previous-school']);
    $previous_program = mysqli_real_escape_string($conn, $_POST['previous-program']);
    $desired_program = mysqli_real_escape_string($conn, $_POST['program-apply']);
    
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

        if (count($errors) == 0) {
            // Preprocess image before OCR for better accuracy (optional)
            if ($tor) {
                try {
                    $imagick = new \Imagick($tor_path);
                    $imagick->setImageType(\Imagick::IMGTYPE_GRAYSCALE);
                    $imagick->adaptiveThresholdImage(100, 100, 1);
                    $processedImagePath = $upload_dir . 'processed_' . uniqid() . '_' . basename($tor);
                    $imagick->writeImage($processedImagePath);
                } catch (Exception $e) {
                    $errors[] = "Error preprocessing the TOR: " . $e->getMessage();
                }
            }

            if (count($errors) == 0 && $tor) {
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
    }

    // If there are errors, redirect to an error page with errors as query parameter
    if (count($errors) > 0) {
        $_SESSION['registration_errors'] = $errors;
        header("Location: registration-error.php");
        exit();
    }
}

// Function to determine eligibility based on TOR extracted text
function determineEligibility($extractedText) {
    // Normalize the text
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
                    <select id="student-type" name="student-type" required onchange="toggleFields()">
                        <option value="">-- Select Student Type --</option>
                        <option value="transferee">Transferee</option>
                        <option value="shiftee">Shiftee</option>
                        <option value="ladderized">Ladderized</option>
                    </select>
                </div>
                <div class="buttons">
                    <button type="button" class="nxt-btn" onclick="nextStep()">Next</button>
                </div>
            </div>

           
            <div class="step">
            <h2>Personal Details</h2>
                <div class="form-group">
                    <div class="form-field">
                        <label for="last-name">Last Name</label>
                        <input type="text" id="last-name" name="last-name">
                    </div>
                    <div class="form-field">
                        <label for="first-name">Given Name</label>
                        <input type="text" id="first-name" name="first-name">
                    </div>
                    <div class="form-field">
                        <label for="middle-name">Middle Name (Optional)</label>
                        <input type="text" id="middle-name" name="middle-name">
                    </div>
                    <div class="form-field">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob">
                    </div>
                    <div class="form-field">
                        <label for="sex">Sex</label>
                        <select id="sex" name="sex">
                            <option value="">--Select Sex--</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="buttons">
                    <button type="button" class="prev-btn" onclick="prevStep()">Previous</button>
                    <button type="button" class="nxt-btn" onclick="nextStep()">Next</button>
                </div>
            </div>
        <!-- Step 2: Contact Details -->
            <div class="step">
                <h2>Contact Details</h2>
                <div class="form-group">
                    <div class="form-field">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email">
                    </div>
                    <div class="form-field">
                        <label for="contact-number">Contact Number</label>
                        <input type="text" id="contact-number" name="contact-number">
                    </div>
                    <div class="form-field">
                        <label for="address">Address</label>
                        <input type="text" id="address-street" name="address-street">
                    </div>
                </div>
                <div class="buttons">
                    <button type="button" class="prev-btn" onclick="prevStep()">Previous</button>
                    <button type="button" class="nxt-btn" onclick="nextStep()">Next</button>
                </div>
            </div>

              <!-- Step 3: Academic Details -->
            <div class="step">
                <h2>Academic Information</h2>
                <div class="form-group">
                    <div class="form-field">
                        <label for="year-level">Current Year Level</label>
                        <input type="number" id="year-level" name="year-level">
                    </div>
                    <div class="form-field" id="previous-school-field">
                        <label for="previous-school">Name of Previous School</label>
                        <input type="text" id="previous-school" name="previous-school">
                    </div>
                    <div class="form-field" id="previous-program-field">
                        <label for="previous-program">Name of Previous Program</label>
                        <select id="previous-program" name="previous-program">
                            <option value="">--Select Previous Program--</option>
                            <option value="BSA">Bachelor of Science in Accountancy</option>
                            <option value="BSBAFM">Bachelor of Science in Business Administration Major in Financial Management (formerly Bachelor of Science in Banking and Finance)</option>
                            <option value="BSMA">Bachelor of Science in Management Accounting</option>
                            <option value="BS-ARCH">Bachelor of Science in Architecture</option>
                            <option value="BSID">Bachelor of Science in Interior Design</option>
                            <option value="BSEP">Bachelor of Science in Environmental Planning</option>
                            <option value="BSCE">Bachelor of Science in Civil Engineering</option>
                            <option value="BSCpE">Bachelor of Science in Computer Engineering</option>
                            <option value="BSECE">Bachelor of Science in Electronics Engineering</option>
                        </select>
                    </div>
                    <div class="form-field" id="program-apply">
                        <label for="program-apply">Name of Program Applying To</label>
                        <select id="program-apply" name="program-apply">
                            <option value="">--Select Previous Program--</option>
                            <option value="BSCS">Bachelor of Science in Computer Sciences</option>
                            <option value="BSIT">Bachelor of Science in Information Technology</option>
                        </select>
                    </div>
                </div>
                <div class="buttons">
                    <button type="button" class="prev-btn" onclick="prevStep()">Previous</button>
                    <button type="button" class="nxt-btn" onclick="nextStep()">Next</button>
                </div>
            </div>

            <!-- Step 4: Upload Documents -->
            <div class="step">
                <h2>Upload Documents</h2>
                <div class="form-group">
                    <div class="form-field" id="tor-field">
                        <label for="tor">Upload Copy of Transcript of Records (TOR)</label>
                        <input type="file" id="tor" name="tor">
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
        let currentStep = 0;
        const steps = document.querySelectorAll('.step');

        function showStep(n) {
            steps.forEach(step => step.classList.remove('active'));
            steps[n].classList.add('active');
        }

        function nextStep() {
            if (currentStep < steps.length - 1) {
                currentStep++;
                showStep(currentStep);
            }
        }

        function prevStep() {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            showStep(currentStep);
        });

        function toggleFields() {
            const studentType = document.getElementById("student-type").value;
            
            const torField = document.getElementById("tor-field");
            const previousSchoolField = document.getElementById("previous-school-field");

            if (studentType === "transferee") {
                torField.style.display = "block"; // Show TOR upload
                previousSchoolField.style.display = "block"; // Show previous school field
            } else if (studentType === "shiftee") {
                torField.style.display = "block"; // Show TOR upload
                previousSchoolField.style.display = "none"; // Hide previous school field
            } else if (studentType === "ladderized") {
                torField.style.display = "none"; // Hide TOR upload
                previousSchoolField.style.display = "none"; // Hide previous school field
            } else {
                torField.style.display = "none"; // Default hide for TOR
                previousSchoolField.style.display = "none"; // Default hide for previous school
            }
        }
    </script>
</body>
</html>