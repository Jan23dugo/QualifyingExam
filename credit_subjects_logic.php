<?php
// credit_subjects_logic.php

// Function to normalize strings (remove extra spaces, convert to uppercase)
function normalizeString($string) {
    return strtoupper(trim(preg_replace('/\s+/', ' ', $string))); // Normalize string
}

// Function to determine credited subjects based on extracted text, desired program, and database subjects
function determineCreditSubjects($extractedText, $desiredProgram, $conn) {
    $creditedSubjects = [];

    if (!$conn) {
        throw new Exception("Database connection is not established.");
    }

    // Query to fetch all subjects from the database related to the program
    $query = "SELECT * FROM subjects WHERE program = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $desiredProgram);
    $stmt->execute();
    $result = $stmt->get_result();

    // Store program subjects in an array and normalize them
    $programSubjects = [];
    while ($row = $result->fetch_assoc()) {
        $programSubjects[] = [
            'subject_code' => normalizeString($row['subject_code']), // Normalize subject code
            'description' => normalizeString($row['description']),   // Normalize description
            'units' => (float) $row['units']
        ];
    }

    // Extract subjects from OCR result using regex
    preg_match_all('/([A-Z]{2,4}\s?\d{3,5})\s+([A-Za-z\s,\'-]+)\s+(\d+)\s+(?:BSIT|CS|IT).+(\d+\.\d{2})/i', $extractedText, $matches, PREG_SET_ORDER);

    // Loop through each extracted subject and compare with database subjects
    foreach ($matches as $match) {
        $extractedCode = normalizeString($match[1]);
        $extractedDescription = normalizeString($match[2]);
        $extractedUnits = (float)trim($match[3]);

        // Compare the extracted subject with the subjects in the database
        foreach ($programSubjects as $subject) {
            if (
                levenshtein($extractedCode, $subject['subject_code']) <= 2 && 
                levenshtein($extractedDescription, $subject['description']) <= 5 && 
                $extractedUnits >= $subject['units']
            ) {
                // If match is found, add the subject to the credited list
                $creditedSubjects[] = [
                    'subject_code' => $subject['subject_code'],
                    'description' => $subject['description'],
                    'units' => $extractedUnits
                ];
                break; // Stop once a match is found
            }
        }
    }

    return $creditedSubjects;
}

?>
