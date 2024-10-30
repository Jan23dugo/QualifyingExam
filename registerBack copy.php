<?php
// Start output buffering at the very beginning
ob_start();
session_start();

// Include necessary files and libraries
include('config/config.php');
require 'vendor/autoload.php';
use thiagoalessio\TesseractOCR\TesseractOCR;
ini_set('error_log', __DIR__ . '/logs/php-error.log');
require 'send_email.php';
require_once 'config/tesseract_config.php';

// Session management
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['success'])) {
    $_SESSION['success'] = '';
}

function debug_log($message) {
    // Store debug messages in session instead of echoing
    if (!isset($_SESSION['debug_messages'])) {
        $_SESSION['debug_messages'] = [];
    }
    $_SESSION['debug_messages'][] = $message;
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
    foreach ($subjects as $subject) {
        $code = strtolower(trim($subject['subject_code']));  
        $description = strtolower(trim($subject['description']));  
        $units = $subject['units'];

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
                $insert_sql = "INSERT INTO matched_courses (subject_code, subject_description, units, student_id, matched_at) 
                               VALUES (?, ?, ?, ?, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ssdi", $row['subject_code'], $row['subject_description'], $row['units'], $student_id);
                $insert_stmt->execute();
            }
            $_SESSION['matches'][] = "Subject matched: {$subject['subject_code']} - {$subject['description']} ({$subject['units']} units)";
        } else {
            $_SESSION['matches'][] = "No match for: {$subject['subject_code']} - {$subject['description']} ({$subject['units']} units)";
        }
    }
}

// 2. Function to determine eligibility based on grades and grading system rules
function determineEligibility($subjects, $gradingRules) {
    $minPassingPercentage = 85.0;
    
    $_SESSION['debug_output'] = '';
    $_SESSION['debug_output'] .= "<div style='background: #f5f5f5; padding: 15px; margin: 15px 0; border: 1px solid #ddd;'>";
    $_SESSION['debug_output'] .= "<h3>Grade Eligibility Check:</h3>";

    foreach ($subjects as $subject) {
        $grade = $subject['grade'];
        $isSubjectEligible = false;
        
        $_SESSION['debug_output'] .= "Checking grade: " . $grade . "<br>";
        
        foreach ($gradingRules as $rule) {
            $gradeValue = floatval($rule['grade_value']);
            $minPercentage = floatval($rule['min_percentage']);
            $maxPercentage = floatval($rule['max_percentage']);
            
            $_SESSION['debug_output'] .= "Comparing with rule: Grade Value = $gradeValue, Min % = $minPercentage, Max % = $maxPercentage<br>";
            
            if ($grade <= $gradeValue && $minPercentage >= $minPassingPercentage) {
                $isSubjectEligible = true;
                $_SESSION['debug_output'] .= "Subject is eligible with grade $grade<br>";
                break;
            }
        }

        if (!$isSubjectEligible) {
            $_SESSION['debug_output'] .= "Subject with grade $grade does not meet eligibility criteria<br>";
            $_SESSION['debug_output'] .= "</div>";
            return false;
        }
    }

    $_SESSION['debug_output'] .= "All subjects meet eligibility criteria<br>";
    $_SESSION['debug_output'] .= "</div>";
    return true;
}

// 3. Function to check if the student is a tech student based on parsed subjects
function isTechStudent($subjects) {
    // List of tech-related subjects (you can customize this list)
    $tech_subjects = [
        'Computer Programming 1', 'Software Engineering', 'Database Systems', 'Operating Systems',
        'Data Structures', 'Algorithms', 'Web Development', 'Computer Programming 2', 'Information Technology',
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
    $reference_id = uniqid('STU_', true);
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
        $student_id = $stmt->insert_id;
        $_SESSION['success'] = "Registration successful! Your reference ID is: " . $reference_id;
        $_SESSION['reference_id'] = $reference_id;
        $_SESSION['student_id'] = $student_id;
        
        // Store debug information in session
        $_SESSION['debug_output'] = ob_get_clean();
        
        sendRegistrationEmail($studentData['email'], $studentData['reference_id']);
        matchCreditedSubjects($conn, $subjects, $student_id);
        
        // Clean output buffer before redirect
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header("Location: registration_success.php");
        exit();
    } else {
        $_SESSION['last_error'] = "Error registering student: " . $stmt->error;
        header("Location: registerFront.php");
        exit();
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

// Main processing code
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Clear any existing output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();

        if (isset($_FILES['tor']) && $_FILES['tor']['error'] == UPLOAD_ERR_OK &&
            isset($_FILES['school_id']) && $_FILES['school_id']['error'] == UPLOAD_ERR_OK) {
            
            validateUploadedFile($_FILES['tor']);
            $tor_path = 'uploads/tor/' . basename($_FILES['tor']['name']);
            move_uploaded_file($_FILES['tor']['tmp_name'], __DIR__ . '/' . $tor_path);

            validateUploadedFile($_FILES['school_id']);
            $school_id_path = 'uploads/school_id/' . basename($_FILES['school_id']['name']);
            move_uploaded_file($_FILES['school_id']['tmp_name'], __DIR__ . '/' . $school_id_path);

            $isEligible = false;
            $student_type = $_POST['student_type'] ?? '';

            // Store debug information
            $debug_output = "";
            
            // Add OCR debug output
            try {
                if (!class_exists('thiagoalessio\TesseractOCR\TesseractOCR')) {
                    throw new Exception("Tesseract OCR library not found. Please ensure it's properly installed.");
                }

                // Create OCR instance with explicit path
                $ocr = new TesseractOCR($tor_path);
                $ocr->executable(TESSERACT_PATH);
                
                // Optional: Add additional configurations
                $ocr->lang('eng')  // Specify language
                    ->dpi(300)     // Set DPI
                    ->psm(6);      // Page segmentation mode
                
                $ocr_output = $ocr->run();
                
                if (empty($ocr_output)) {
                    throw new Exception("OCR failed to extract text from the document.");
                }

                $debug_output .= "<div style='background: #f5f5f5; padding: 15px; margin: 15px 0; border: 1px solid #ddd;'>";
                $debug_output .= "<h3>OCR Debug Output:</h3>";
                $debug_output .= "<pre style='white-space: pre-wrap;'>" . htmlspecialchars($ocr_output) . "</pre>";
                $debug_output .= "</div>";

            } catch (Exception $e) {
                $_SESSION['last_error'] = "OCR Error: " . $e->getMessage();
                error_log("OCR Error: " . $e->getMessage());
                header("Location: registerFront.php");
                exit();
            }

            $subjects = extractSubjects($ocr_output);
            $debug_output .= "<div style='background: #f5f5f5; padding: 15px; margin: 15px 0; border: 1px solid #ddd;'>";
            $debug_output .= "<h3>Extracted Subjects:</h3>";
            $debug_output .= "<pre style='white-space: pre-wrap;'>" . print_r($subjects, true) . "</pre>";
            $debug_output .= "</div>";

            // Store debug output in session
            $_SESSION['debug_output'] = $debug_output;

            if (strtolower($student_type) === 'ladderized') {
                $isEligible = true;
            } else {
                $previous_school = $_POST['previous_school'] ?? '';
                $gradingRules = getGradingSystemRules($conn, $previous_school);
                $isEligible = determineEligibility($subjects, $gradingRules);
            }

            if ($isEligible) {
                $is_tech = isTechStudent($subjects);
                $studentData = [
                    'first_name' => $_POST['first_name'] ?? '',
                    'middle_name' => $_POST['middle_name'] ?? '',
                    'last_name' => $_POST['last_name'] ?? '',
                    'gender' => $_POST['gender'] ?? '',
                    'dob' => $_POST['dob'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'contact_number' => $_POST['contact_number'] ?? '',
                    'street' => $_POST['street'] ?? '',
                    'student_type' => $student_type,
                    'previous_school' => $_POST['previous_school'] ?? '',
                    'year_level' => $_POST['year_level'] ?? '',
                    'previous_program' => $_POST['previous_program'] ?? '',
                    'desired_program' => $_POST['desired_program'] ?? '',
                    'tor_path' => $tor_path,
                    'school_id_path' => $school_id_path,
                    'is_tech' => $is_tech
                ];
                
                // Clean any output before registration
                while (ob_get_level()) {
                    ob_end_clean();
                }
                
                registerStudent($conn, $studentData, $subjects);
            } else {
                $_SESSION['last_error'] = "The student is not eligible.";
                header("Location: registerFront.php");
                exit();
            }
        } else {
            throw new Exception("Both TOR and School ID files are required.");
        }
    } catch (Exception $e) {
        $_SESSION['last_error'] = $e->getMessage();
        header("Location: registerFront.php");
        exit();
    }
}

// If we reach here, something went wrong
$_SESSION['last_error'] = "An unexpected error occurred.";
header("Location: registerFront.php");
exit();
?>
