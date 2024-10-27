<?php
// Include the database configuration file
include('config/config.php');

// Check if the file was uploaded
if (isset($_FILES['tor_file'])) {
    // Define the upload path
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["tor_file"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Allow only certain file formats (images and PDFs)
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "pdf") {
        echo "Sorry, only JPG, JPEG, PNG & PDF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // Try to upload the file
        if (move_uploaded_file($_FILES["tor_file"]["tmp_name"], $target_file)) {
            echo "The file ". htmlspecialchars(basename($_FILES["tor_file"]["name"])). " has been uploaded.<br>";
            
            // Check if the file exists on the server
            if (file_exists($target_file)) {
                echo "File exists: $target_file<br>";
                
                // Call Tesseract OCR to extract text
                $ocr_output = [];
                $error_output = [];
                $return_var = 0;

                // Execute Tesseract command and capture output and errors
                exec("tesseract \"$target_file\" stdout 2>&1", $ocr_output, $return_var);

                // Check for errors in Tesseract output
                if ($return_var !== 0) {
                    echo "Tesseract OCR failed:<br>";
                    echo implode("<br>", $ocr_output);  // Print Tesseract error messages
                } else {
                    $extracted_text = implode("\n", $ocr_output);  // Convert array to text
                    echo "<h3>Extracted Text:</h3>";
                    echo "<pre>$extracted_text</pre>";

                    // Parse the OCR output for subject data
                    $subjects = parse_subjects($extracted_text);

                    // Compare with the database
                    compare_with_database($subjects);
                }
            } else {
                echo "File does not exist!<br>";
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Function to parse the OCR output and extract subjects (code, description, units)
function parse_subjects($text) {
    $subjects = [];
    
    // Split the extracted text into lines
    $lines = explode("\n", $text);
    
    $subject_codes = [];
    $descriptions = [];
    $units = [];

    // Loop through the lines to find subject code, descriptions, and units
    $current_section = null;
    foreach ($lines as $line) {
        $line = trim($line);

        if (empty($line)) {
            continue; // Skip empty lines
        }

        // Detect sections in the text
        if (stripos($line, 'Subject Code') !== false) {
            $current_section = 'subject_code';
            continue;
        } elseif (stripos($line, 'Description') !== false) {
            $current_section = 'description';
            continue;
        } elseif (stripos($line, 'Units') !== false) {
            $current_section = 'units';
            continue;
        }

        // Collect data based on the current section
        if ($current_section == 'subject_code') {
            $subject_codes[] = $line;
        } elseif ($current_section == 'description') {
            $descriptions[] = $line;
        } elseif ($current_section == 'units') {
            $units[] = $line;
        }
    }

    // Now we have arrays of subject codes, descriptions, and units. Let's combine them
    $num_subjects = min(count($subject_codes), count($descriptions), count($units));

    for ($i = 0; $i < $num_subjects; $i++) {
        $subjects[] = [
            'subject_code' => trim($subject_codes[$i]),  
            'subject_description' => trim($descriptions[$i]),  
            'units' => (float)trim($units[$i])
        ];
    }

    return $subjects;
}

// Function to compare parsed subjects with coded courses in the database
function compare_with_database($subjects) {
    global $conn; // Use the $conn object from config.php

    echo "<h3>Matching Results:</h3>";

    foreach ($subjects as $subject) {
        // Trim and convert to lowercase for comparison
        $code = strtolower(trim($subject['subject_code']));  
        $description = strtolower(trim($subject['subject_description']));  
        $units = $subject['units'];

        // Print extracted subject data for debugging
        echo "Extracted Subject: Code = {$subject['subject_code']}, Description = {$subject['subject_description']}, Units = {$subject['units']}<br>";

        // Query to check if the subject exists in the coded courses (case-insensitive)
        $sql = "SELECT * FROM coded_courses 
                WHERE LOWER(subject_code) = ? 
                AND LOWER(subject_description) = ? 
                AND units = ?";

        // Prepare and bind parameters
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssd", $code, $description, $units);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "Matched in Database: Code = {$row['subject_code']}, Description = {$row['subject_description']}, Units = {$row['units']}<br>";

                // Insert the matched result into the matched_courses table
                $insert_sql = "INSERT INTO matched_courses (subject_code, subject_description, units) 
                               VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ssd", $row['subject_code'], $row['subject_description'], $row['units']);
                if ($insert_stmt->execute()) {
                    echo "Matched subject saved to database: {$row['subject_code']} - {$row['subject_description']}<br>";
                } else {
                    echo "Error saving match: " . $insert_stmt->error . "<br>";
                }
            }
            echo "Subject matched: {$subject['subject_code']} - {$subject['subject_description']} ({$subject['units']} units)<br>";
        } else {
            echo "No match for: {$subject['subject_code']} - {$subject['subject_description']} ({$subject['units']} units)<br>";
        }
    }
}

?>
