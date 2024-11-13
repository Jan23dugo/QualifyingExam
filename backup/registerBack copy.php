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
    addDebugOutput("Starting Subject Matching Process");
    
    foreach ($subjects as $subject) {
        $code = strtolower(trim($subject['subject_code']));  
        $description = strtolower(trim($subject['description']));  
        $units = $subject['units'];

        addDebugOutput("Checking Subject:", [
            'code' => $code,
            'description' => $description,
            'units' => $units
        ]);

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
                addDebugOutput("Match Found:", $row);
                
                $insert_sql = "INSERT INTO matched_courses (subject_code, subject_description, units, student_id, matched_at) 
                               VALUES (?, ?, ?, ?, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ssdi", $row['subject_code'], $row['subject_description'], $row['units'], $student_id);
                $insert_stmt->execute();
            }
            $_SESSION['matches'][] = "Subject matched: {$subject['subject_code']} - {$subject['description']} ({$subject['units']} units)";
        } else {
            addDebugOutput("No Match Found for Subject", [
                'searched_code' => $code,
                'searched_description' => $description,
                'searched_units' => $units
            ]);
            $_SESSION['matches'][] = "No match for: {$subject['subject_code']} - {$subject['description']} ({$subject['units']} units)";
        }
    }
}

// 2. Function to determine eligibility based on grades and grading system rules
function determineEligibility($subjects, $gradingRules) {
    $minPassingPercentage = 85.0; // Minimum required percentage
    
    $_SESSION['debug_output'] = '';
    $_SESSION['debug_output'] .= "<div style='background: #f5f5f5; padding: 15px; margin: 15px 0; border: 1px solid #ddd;'>";
    $_SESSION['debug_output'] .= "<h3>Grade Eligibility Check:</h3>";

    foreach ($subjects as $subject) {
        $grade = $subject['grade'];
        $isSubjectEligible = false;
        
        $_SESSION['debug_output'] .= "Checking grade: " . $grade . "<br>";
        
        // Find the matching grade rule for this grade
        $matchingRule = null;
        foreach ($gradingRules as $rule) {
            $gradeValue = floatval($rule['grade_value']);
            $minPercentage = floatval($rule['min_percentage']);
            $maxPercentage = floatval($rule['max_percentage']);
            
            $_SESSION['debug_output'] .= "Comparing with rule: Grade Value = $gradeValue, Min % = $minPercentage, Max % = $maxPercentage<br>";
            
            // Check if the grade falls within this rule's range
            if ($grade == $gradeValue) {
                $matchingRule = $rule;
                $_SESSION['debug_output'] .= "Found matching grade rule. Percentage range: $minPercentage - $maxPercentage<br>";
                break;
            }
        }
        
        // If we found a matching rule, check if the percentage meets our requirement
        if ($matchingRule) {
            $ruleMinPercentage = floatval($matchingRule['min_percentage']);
            if ($ruleMinPercentage >= $minPassingPercentage) {
                $isSubjectEligible = true;
                $_SESSION['debug_output'] .= "Subject is eligible: Grade $grade corresponds to percentage $ruleMinPercentage% (≥ $minPassingPercentage%)<br>";
            } else {
                $_SESSION['debug_output'] .= "Subject is not eligible: Grade $grade corresponds to percentage $ruleMinPercentage% (< $minPassingPercentage%)<br>";
            }
        } else {
            $_SESSION['debug_output'] .= "No matching grade rule found for grade $grade<br>";
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
    addDebugOutput("Starting Tech Student Check");
    
    $tech_subjects = [
        'Computer Programming 1', 'Software Engineering', 'Database Systems', 'Operating Systems',
        'Data Structures', 'Algorithms', 'Web Development', 'Computer Programming 2', 'Information Technology',
        'Cybersecurity', 'System Analysis and Design'
    ];

    addDebugOutput("Tech Subject Keywords:", $tech_subjects);

    foreach ($subjects as $subject) {
        foreach ($tech_subjects as $tech_subject) {
            if (stripos($subject['description'], $tech_subject) !== false) {
                addDebugOutput("Tech Subject Found:", [
                    'subject' => $subject['description'],
                    'matched_keyword' => $tech_subject
                ]);
                return true;
            }
        }
    }
    
    addDebugOutput("No Tech Subjects Found in Student's Records");
    return false;
}

// Function to register the student with updated fields
// Function to register the student with updated fields and also pass subjects
function registerStudent($conn, $studentData, $subjects) {
    $reference_id = uniqid('STU_', true);
    $studentData['reference_id'] = $reference_id;
    
    // Set year_level to NULL for ladderized students
    if ($studentData['student_type'] === 'ladderized') {
        $studentData['year_level'] = null;
        // Ensure DICT is set as previous program
        $studentData['previous_program'] = 'Diploma in Information and Communication Technology (DICT)';
    }
    
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
        $_SESSION['success'] = "Your reference ID is: " . $reference_id;
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

// Add this function at the top
function addDebugOutput($message, $data = null) {
    if (!isset($_SESSION['debug_output'])) {
        $_SESSION['debug_output'] = '';
    }
    $_SESSION['debug_output'] .= "<div style='background: #f5f5f5; margin: 10px 0; padding: 10px; border-left: 4px solid #008CBA;'>";
    $_SESSION['debug_output'] .= "<strong>$message</strong><br>";
    if ($data !== null) {
        $_SESSION['debug_output'] .= "<pre>" . print_r($data, true) . "</pre>";
    }
    $_SESSION['debug_output'] .= "</div>";
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

            // Add this function at the top
            try {
                addDebugOutput("Starting OCR Process");
                
                if (!class_exists('thiagoalessio\TesseractOCR\TesseractOCR')) {
                    $_SESSION['ocr_error'] = "OCR system is not available. Please try again later.";
                    header("Location: registration_success.php");
                    exit();
                }

                $ocr = new TesseractOCR($tor_path);
                $ocr->executable(TESSERACT_PATH);
                
                try {
                    $ocr_output = $ocr->run();
                } catch (Exception $ocrException) {
                    // Catch specific OCR execution errors and provide a cleaner message
                    $_SESSION['ocr_error'] = "Unable to process the uploaded document. Please ensure your document is clear and readable.";
                    header("Location: registration_success.php");
                    exit();
                }
                
                if (empty($ocr_output)) {
                    $_SESSION['ocr_error'] = "The system could not read any text from the uploaded document. Please ensure you have uploaded:
                        \n- A clear, high-quality image
                        \n- An official Transcript of Records
                        \n- A document that is not password protected";
                    header("Location: registration_success.php");
                    exit();
                }

                // Store the raw OCR output in debug but don't show it in the error message
                addDebugOutput("Raw OCR Output:", $ocr_output);

                // Basic validation of OCR output to check if it's a TOR
                $required_keywords = [
                    'TRANSCRIPT','Transcript', 'Records', 'RECORDS', 'UNIT', 'Units', 'Unit', 'UNITS', 'Credit Unit/s',
                    'GRADE', 'Grade','SUBJECT', 'Subject', 'Subject Code', 'Subject Description', 'Course Code', 'Course Code','Course Description'
                ];
                $found_keywords = 0;
                $found_words = [];
                
                foreach ($required_keywords as $keyword) {
                    if (stripos($ocr_output, $keyword) !== false) {
                        $found_keywords++;
                        $found_words[] = $keyword;
                    }
                }

                // Require at least 4 keywords to consider it a valid TOR
                if ($found_keywords < 4) {
                    $_SESSION['ocr_error'] = "The uploaded document does not appear to be a valid Transcript of Records. Please ensure you are uploading an official TOR that contains grades and subject information.";
                    header("Location: registration_success.php");
                    exit();
                }

                addDebugOutput("Document Validation:", [
                    'Keywords Found' => $found_keywords,
                    'Matched Words' => $found_words
                ]);

                addDebugOutput("Raw OCR Output:", $ocr_output);

                // Extract subjects from OCR text
                $subjects = extractSubjects($ocr_output);
                addDebugOutput("Extracted Subjects:", $subjects);

                // Process eligibility
                $isEligible = false;
                $student_type = $_POST['student_type'] ?? '';
                
                addDebugOutput("Student Type:", $student_type);

                if (strtolower($student_type) === 'ladderized') {
                    $isEligible = true;
                    addDebugOutput("Ladderized Student - Automatically Eligible");
                } else {
                    $previous_school = $_POST['previous_school'] ?? '';
                    addDebugOutput("Previous School:", $previous_school);
                    
                    $gradingRules = getGradingSystemRules($conn, $previous_school);
                    addDebugOutput("Retrieved Grading Rules:", $gradingRules);
                    
                    $isEligible = determineEligibility($subjects, $gradingRules);
                }

                // Check if student is tech
                $is_tech = isTechStudent($subjects);
                addDebugOutput("Tech Student Check:", [
                    'is_tech' => $is_tech ? 'Yes' : 'No',
                    'checked_subjects' => $subjects
                ]);

            } catch (Exception $e) {
                // Log the full error for debugging but show a cleaner message to users
                error_log("OCR Error: " . $e->getMessage());
                $_SESSION['ocr_error'] = "There was an error processing your document. Please try uploading again with a clearer image.";
                header("Location: registration_success.php");
                exit();
            }

            // Store eligibility status in session
            $_SESSION['is_eligible'] = $isEligible;

            if ($isEligible) {
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
                    'year_level' => ($_POST['student_type'] === 'ladderized') ? null : ($_POST['year_level'] ?? ''),
                    'previous_program' => ($_POST['student_type'] === 'ladderized') ? 
                        'Diploma in Information and Communication Technology (DICT)' : 
                        ($_POST['previous_program'] ?? ''),
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
                $_SESSION['success'] = "Registration completed, but you are not eligible for credit transfer.";
                $_SESSION['eligibility_message'] = "Based on your grades and our criteria, you do not meet the eligibility requirements for credit transfer.";
                header("Location: registration_success.php");
                exit();
            }
        } else {
            $_SESSION['ocr_error'] = "Please upload both TOR and School ID files.";
            header("Location: registration_success.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['ocr_error'] = $e->getMessage();
        header("Location: registration_success.php");
        exit();
    }
}

// If we reach here, something went wrong
$_SESSION['ocr_error'] = "An unexpected error occurred.";
header("Location: registration_success.php");
exit();
?>
