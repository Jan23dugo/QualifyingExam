<?php
// Include necessary files and libraries
include('config/config.php');
require 'vendor/autoload.php';
use thiagoalessio\TesseractOCR\TesseractOCR;
ini_set('error_log', __DIR__ . '/logs/php-error.log');
require 'send_email.php'; // Assuming this function is available for sending emails

// Session management
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['success'])) {
    $_SESSION['success'] = '';
}

function debug_log($message) {
    error_log($message);
    echo $message . "<br>";
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$errors = [];

// Validate the uploaded file
function validateUploadedFile($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, and PDF files are allowed.');
    }
    if ($file['size'] > 5000000) { // File size check
        throw new Exception('File is too large. Maximum size is 5MB.');
    }
    return true;
}

// Extract subjects from OCR text
function extractSubjects($text) {
    $subjects = [];
    
    // Split text into lines to process each line individually
    $lines = explode("\n", $text);
    
    $subjectCodes = [];
    $descriptions = [];
    $units = [];
    $grades = [];
    
    $currentSection = ''; // Track which section we're parsing
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue; // Skip empty lines
        
        // Detect sections based on known headers
        if (strpos($line, 'Subject Code') !== false) {
            $currentSection = 'subject_code';
            continue;
        }
        if (strpos($line, 'Description') !== false) {
            $currentSection = 'description';
            continue;
        }
        if (strpos($line, 'Units') !== false) {
            $currentSection = 'units';
            continue;
        }
        if (strpos($line, 'Final Grade') !== false) {
            $currentSection = 'grade';
            continue;
        }
        
        // Based on the current section, add the line to the respective array
        if ($currentSection === 'subject_code' && preg_match('/(COMP|CWTS|GEED|PHED)\s+\d{5}/', $line)) {
            $subjectCodes[] = $line;
        }
        elseif ($currentSection === 'description') {
            $descriptions[] = $line;
        }
        elseif ($currentSection === 'units' && preg_match('/\d+\.\d/', $line)) {
            $units[] = $line;
        }
        elseif ($currentSection === 'grade' && preg_match('/\d\.\d{2}/', $line)) {
            $grades[] = $line;
        }
    }
    
    // Combine subject codes, descriptions, units, and grades
    $numSubjects = min(count($subjectCodes), count($descriptions), count($units), count($grades)); // Ensure all arrays are equal in size
    for ($i = 0; $i < $numSubjects; $i++) {
        $subjects[] = [
            'subject_code' => $subjectCodes[$i],
            'description' => $descriptions[$i],
            'units' => floatval($units[$i]),
            'grade' => floatval($grades[$i])
        ];
    }

    return $subjects;
}

// 1. Function to compare parsed subjects with coded courses in the database and save matches
function matchCreditedSubjects($conn, $subjects, $student_id) {
    echo "<h3>Matching Results:</h3>";

    foreach ($subjects as $subject) {
        // Trim and convert to lowercase for comparison
        $code = strtolower(trim($subject['subject_code']));  
        $description = strtolower(trim($subject['description']));  
        $units = $subject['units'];

        // Print extracted subject data for debugging
        echo "Extracted Subject: Code = {$subject['subject_code']}, Description = {$subject['description']}, Units = {$subject['units']}<br>";

        // Query to check if the subject exists in the coded courses (case-insensitive)
        $sql = "SELECT * FROM coded_courses 
                WHERE LOWER(subject_code) = ? 
                AND LOWER(subject_description) = ? 
                AND units = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssd", $code, $description, $units);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "Matched in Database: Code = {$row['subject_code']}, Description = {$row['subject_description']}, Units = {$row['units']}<br>";

                // Insert the matched result into the matched_courses table, including the student_id
                $insert_sql = "INSERT INTO matched_courses (subject_code, subject_description, units, student_id, matched_at) 
                               VALUES (?, ?, ?, ?, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ssdi", $row['subject_code'], $row['subject_description'], $row['units'], $student_id);
                $insert_stmt->execute();
            }
            echo "Subject matched: {$subject['subject_code']} - {$subject['description']} ({$subject['units']} units)<br>";
        } else {
            echo "No match for: {$subject['subject_code']} - {$subject['description']} ({$subject['units']} units)<br>";
        }
    }
}
// 2. Function to determine eligibility based on grades and grading system rules
function determineEligibility($grades, $gradingRules) {
    $minPassingPercentage = 85.0; // Threshold percentage for eligibility

    foreach ($grades as $grade) {
        $isEligible = false;
        
        foreach ($gradingRules as $rule) {
            $gradeValue = $rule['grade_value'];
            $minPercentage = (float)$rule['min_percentage'];
            $maxPercentage = (float)$rule['max_percentage'];
            
            // Check if the student's grade matches the grade value in the database
            if ($grade == $gradeValue) {
                if ($minPercentage >= $minPassingPercentage) {
                    $isEligible = true;
                }
                break;
            }
        }

        // If any grade does not meet the threshold, return false
        if (!$isEligible) {
            return false;
        }
    }

    return true; // All grades meet eligibility criteria
}

// 3. Function to check if the student is a tech student based on parsed subjects
function isTechStudent($subjects) {
    // List of tech-related subjects (you can customize this list)
    $tech_subjects = [
        'Computer Programming', 'Software Engineering', 'Database Systems', 'Operating Systems',
        'Data Structures', 'Algorithms', 'Web Development', 'Networking', 'Information Technology',
        'Cybersecurity', 'System Analysis and Design'
    ];

    foreach ($subjects as $subject) {
        foreach ($tech_subjects as $tech_subject) {
            if (stripos($subject['description'], $tech_subject) !== false) {
                return true; // Student is a tech student
            }
        }
    }
    return false; // Student is not a tech student
}

// Function to register the student with updated fields
// Function to register the student with updated fields and also pass subjects
function registerStudent($conn, $studentData, $subjects) {
    $reference_id = uniqid('STU_', true);  // Generate a unique reference ID
    $studentData['reference_id'] = $reference_id;
    
    $stmt = $conn->prepare("INSERT INTO students (
        last_name, first_name, middle_name, gender, dob, email, contact_number, street, 
        student_type, previous_school, year_level, previous_program, desired_program, 
        tor, school_id, reference_id, is_tech
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssssssssssssssssi", 
        $studentData['last_name'],
        $studentData['first_name'],
        $studentData['middle_name'],
        $studentData['gender'],
        $studentData['dob'],
        $studentData['email'],
        $studentData['contact_number'],
        $studentData['street'],
        $studentData['student_type'],
        $studentData['previous_school'],
        $studentData['year_level'],
        $studentData['previous_program'],
        $studentData['desired_program'],
        $studentData['tor_path'],
        $studentData['school_id_path'],
        $studentData['reference_id'],
        $studentData['is_tech']
    );

    if ($stmt->execute()) {
        $student_id = $stmt->insert_id; // Retrieve the newly generated student ID

        // Store success message and reference ID in session
        $_SESSION['success'] = "Registration successful! Your reference ID is: " . $reference_id;
        $_SESSION['reference_id'] = $reference_id;
        $_SESSION['student_id'] = $student_id;  // Save the student ID in session
        // Send registration email
        sendRegistrationEmail($studentData['email'], $studentData['reference_id']);
        
        // Call matchCreditedSubjects and pass $student_id and $subjects
        matchCreditedSubjects($conn, $subjects, $student_id);
        
        // For now, do not redirect to the success page to see debug output
        header("Location: registration_success.php");
        exit();
    } else {
        echo "Error registering student: " . $stmt->error;
    }
    $stmt->close();
}

function getGradingSystemRules($conn, $universityName) {
    $query = "SELECT min_percentage, max_percentage, grade_value FROM university_grading_systems WHERE university_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $universityName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt->close();
        $universityName = "%$universityName%";
        $query = "SELECT min_percentage, max_percentage, grade_value FROM university_grading_systems WHERE university_name LIKE ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $universityName);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    
    $gradingRules = [];
    while ($row = $result->fetch_assoc()) {
        $gradingRules[] = $row;
    }

    $stmt->close();
    return $gradingRules;
}
// Handle file upload and process OCR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_FILES['tor']) && $_FILES['tor']['error'] == UPLOAD_ERR_OK &&
            isset($_FILES['school_id']) && $_FILES['school_id']['error'] == UPLOAD_ERR_OK) {
            
            // Validate and upload the files
            validateUploadedFile($_FILES['tor']);
            $tor_path = 'uploads/tor/' . basename($_FILES['tor']['name']);
            move_uploaded_file($_FILES['tor']['tmp_name'], __DIR__ . '/' . $tor_path);

            validateUploadedFile($_FILES['school_id']);
            $school_id_path = 'uploads/school_id/' . basename($_FILES['school_id']['name']);
            move_uploaded_file($_FILES['school_id']['tmp_name'], __DIR__ . '/' . $school_id_path);

            // Extract text from TOR using OCR
            $ocr_output = (new TesseractOCR($tor_path))->run();
            $subjects = extractSubjects($ocr_output);  // Parse OCR text into subjects

            // Retrieve grading rules from database
            $previous_school = $_POST['previous_school'] ?? '';
            $gradingRules = getGradingSystemRules($conn, $previous_school);

            // Determine eligibility based on grades
            $isEligible = determineEligibility($subjects, $gradingRules);

            if ($isEligible) {
                echo "<h3>The student is eligible.</h3>";

                // Check if the student is tech-related
                $is_tech = isTechStudent($subjects);

                // Gather and register student data
                $studentData = [
                    'first_name' => $_POST['first_name'] ?? '',
                    'middle_name' => $_POST['middle_name'] ?? '',
                    'last_name' => $_POST['last_name'] ?? '',
                    'gender' => $_POST['gender'] ?? '',
                    'dob' => $_POST['dob'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'contact_number' => $_POST['contact_number'] ?? '',
                    'street' => $_POST['street'] ?? '',
                    'student_type' => $_POST['student_type'] ?? '',
                    'previous_school' => $_POST['previous_school'] ?? '',
                    'year_level' => $_POST['year_level'] ?? '',
                    'previous_program' => $_POST['previous_program'] ?? '',
                    'desired_program' => $_POST['desired_program'] ?? '',
                    'tor_path' => $tor_path,
                    'school_id_path' => $school_id_path,
                    'is_tech' => $is_tech
                ];
                // Register student in the database and pass $subjects
                registerStudent($conn, $studentData, $subjects);  // Pass subjects here
                
            } else {
                echo "<h3>The student is not eligible.</h3>";
            }
        } else {
            throw new Exception("Both TOR and School ID files are required.");
        }
    } catch (Exception $e) {
        echo "<h3>Error:</h3>" . htmlspecialchars($e->getMessage());
    }
}
?>
