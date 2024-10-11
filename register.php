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
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $middle_initial = mysqli_real_escape_string($conn, $_POST['middle_initial']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $nationality = mysqli_real_escape_string($conn, $_POST['nationality']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    
    // Address details
    $street = mysqli_real_escape_string($conn, $_POST['street']);
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $zip_code = mysqli_real_escape_string($conn, $_POST['zip_code']);
    
    // Academic details
    $student_type = mysqli_real_escape_string($conn, $_POST['student_type']);
    if ($student_type === 'ladderized') {
        $errors[] = "Ladderized students should use a different registration form.";
    }
    $previous_school = mysqli_real_escape_string($conn, $_POST['previous_school']);
    $year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
    $previous_program = mysqli_real_escape_string($conn, $_POST['previous_program']);
    $desired_program = mysqli_real_escape_string($conn, $_POST['desired_program']);
    
    // Handle file uploads
    $upload_dir = __DIR__ . "/uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $tor = $_FILES['tor']['name'];
    $school_id = $_FILES['school_id']['name'];
    $birth_certificate = $_FILES['birth_certificate']['name'];

    // Check for errors before proceeding
    if (empty($last_name) || empty($first_name) || empty($gender) || empty($dob) || empty($email) || empty($student_type)) {
        $errors[] = "Please fill out all required fields.";
    }

    // Generate a unique Reference ID
    $reference_id = uniqid('STU-'); // This will generate something like STU-605c1c1c7a7f7

    // If no errors, process the form data
    if (count($errors) == 0) {
        // Move uploaded files to the designated directory
        $tor_path = $upload_dir . uniqid() . "_" . basename($tor);
        $school_id_path = $upload_dir . uniqid() . "_" . basename($school_id);
        $birth_certificate_path = $upload_dir . uniqid() . "_" . basename($birth_certificate);

        if (!move_uploaded_file($_FILES['tor']['tmp_name'], $tor_path)) {
            $errors[] = "Failed to upload Transcript of Records (TOR).";
        }
        if (!move_uploaded_file($_FILES['school_id']['tmp_name'], $school_id_path)) {
            $errors[] = "Failed to upload School ID.";
        }
        if (!move_uploaded_file($_FILES['birth_certificate']['tmp_name'], $birth_certificate_path)) {
            $errors[] = "Failed to upload Birth Certificate.";
        }

        if (count($errors) == 0) {
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

                    if ($isEligible) {
                        // Insert the data along with the generated Reference ID into the database
                        $sql = "INSERT INTO students (last_name, first_name, middle_initial, gender, dob, nationality, email, contact_number, street, barangay, city, province, zip_code, student_type, previous_school, year_level, previous_program, desired_program, tor, school_id, birth_certificate, reference_id)
                                VALUES ('$last_name', '$first_name', '$middle_initial', '$gender', '$dob', '$nationality', '$email', '$contact_number', '$street', '$barangay', '$city', '$province', '$zip_code', '$student_type', '$previous_school', '$year_level', '$previous_program', '$desired_program', '$tor_path', '$school_id_path', '$birth_certificate_path', '$reference_id')";

                        if (mysqli_query($conn, $sql)) {
                            // Send confirmation email using PHPMailer
                            sendRegistrationEmail($email, $reference_id); // Call the email function

                            // Redirect to success page with reference ID
                            header("Location: registration-confirmation.php?refid=$reference_id");
                            exit();
                        } else {
                            $errors[] = "Error: " . mysqli_error($conn);
                        }
                    } else {
                        $errors[] = "You are not eligible for the qualifying examination based on your grades.";
                    }
                } catch (Exception $e) {
                    $errors[] = "Error processing the TOR: " . $e->getMessage();
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
    <title>Student Registration</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<section class="form-section">
        <div class="form-group head"> 
        <h1>Student Registration and Requirements Submission</h1>
        <img src="puplogo.png" alt="Right Logo" class="puplogo">
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
        <form action="register.php" method="POST" enctype="multipart/form-data">
        <fieldset>
        <legend>Student Personal Information</legend>
        <div class="form-group name-gender">
            <div class="form-field">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-field">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>

            <div class="form-field">
                <label for="middle_initial">Middle Initial:</label>
                <input type="text" id="middle_initial" name="middle_initial">
            </div>

            <div class="form-field">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="" disabled selected>-- Select Gender --</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>

            </div>

        <div class="form-group contact">
            <div class="form-field">
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" required>
            </div>

            <div class="form-field">
                <label for="nationality">Nationality:</label>
                <input type="text" id="nationality" name="nationality" required>
            </div>

            <div class="form-field">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-field">
                <label for="contact_number">Contact Number:</label>
                <input type="text" id="contact_number" name="contact_number" required>
            </div>
        </div>

            <!-- Address -->
           
            <div class="form-group address1">
            <div class="form-field">
                <label for="street">Street:</label>
                <input type="text" id="street" name="street" required>
            </div>

            <div class="form-field">
                <label for="barangay">Barangay:</label>
                <input type="text" id="barangay" name="barangay" required>
            </div>

            <div class="form-field">
                <label for="city">City:</label>
                <input type="text" id="city" name="city" required>
            </div>

            <div class="form-field">
                <label for="province">Province/Region:</label>
                <input type="text" id="province" name="province" required>
            </div>

            <div class="form-field">
                <label for="zip_code">Zip Code:</label>
                <input type="text" id="zip_code" name="zip_code" required>
            </div>
        </div>
        </fieldset>

            <!-- Academic Information -->
        <fieldset>
            <legend>Student Academic Information</legend>
            <div class="form-group school">
            <div class="form-field">
                <label for="student_type">Student Type:</label>
                <select id="student_type" name="student_type" required>
                    <option value="" disabled selected>-- Select Student Type --</option>
                    <option value="shiftee">Shiftee</option>
                    <option value="transferee">Transferee</option>
                    
                </select>
            </div>

            <div class="form-field">
                <label for="previous_school">Name of Previous School:</label>
                <input type="text" id="previous_school" name="previous_school" required>
            </div>

            <div class="form-field">
                <label for="year_level">Current Year Level</label>
                <input type="text" id="year_level" name="year_level" required>
            </div>
            </div>

            <div class="form-group desired">
            <div class="form-field">
                <label for="previous_program">Previous Program:</label>
                <input type="text" id="previous_program" name="previous_program">
            </div>

            <div class="form-field">
                <label for="desired_program">Desired Program:</label>
                <select id="desired_program" name="desired_program" required>
                    <option value="" disabled selected>-- Select Desired Program --</option>
                    <option value="bsit">Bachelor of Science in Information Technology (BSIT)</option>
                    <option value="bscs">Bachelor of Science in Computer Science (BSCS)</option>
                </select>
            </div>
            </div>
            
            
            <!-- File Uploads -->
            <div class="form-group upload">
            <div class="form-field">
                <label for="tor">
                <input type="file" id="tor" name="tor" required>
            </div>
            <div class="form-field">
                <label for="school_id">Upload Copy of School ID:</label>
                <input type="file" id="school_id" name="school_id" required>
            </div>
            <div class="form-field">
                <label for="birth_certificate">Upload Copy of Birth Certificate:</label>
                <input type="file" id="birth_certificate" name="birth_certificate" required>
            </div>
            </div>
            </fieldset>
            <!-- Submit Button -->
            <button type="submit">SUBMIT</button>  
        </form>
    </div>

</body>
</html>