<?php
// Include database configuration file
include('config/config.php');
require 'vendor/autoload.php'; // Include Composer autoload

// This script will be responsible for matching subjects from the OCR extracted text
// with the predefined subjects for IT and CS programs.

function determineCreditSubjects($extractedText, $desired_program) {
    // Normalize the extracted text
    $normalizedText = strtolower($extractedText);

    // Define course lists for Computer Science and Information Technology
    $cs_subjects = [
        'mathematics in the modern world',
        'purposive communication',
        'filipinolohiya at pambansang kaunlaran',
        'politics, governance and citizenship',
        'introduction to computing',
        'computer programming 1',
        'science, technology and society',
        'intelektwalisasyon ng filipino sa iba\'t ibang',
        'differential calculus',
        'understanding the self',
        'computer programming 2',
        'discrete structures 1'
    ];

    $it_subjects = [
        'physical fitness and self-testing activities',
        'filipinolohiya at pambansang kaunlaran',
        'purposive communication',
        'mathematics in the modern world',
        'civic welfare training service 1',
        'computer programming 1',
        'introduction to computing',
        'accounting principles',
        'computer programming 2',
        'discrete structures 1',
        'civic welfare training service 2',
        'readings in philippine history',
        'pagsasalin sa kontekstong filipino',
        'politics, governance and citizenship',
        'rhythmic activities'
    ];

    // Choose subjects based on the desired program
    $required_subjects = ($desired_program === 'BSCS') ? $cs_subjects : $it_subjects;

    // Determine the credited subjects based on OCR extracted text
    $credited_subjects = [];

    foreach ($required_subjects as $subject) {
        if (strpos($normalizedText, strtolower($subject)) !== false) {
            $credited_subjects[] = $subject;
        }
    }

    return $credited_subjects;
}

// Function to save credited subjects to the database
function saveCreditedSubjects($conn, $reference_id, $credited_subjects) {
    foreach ($credited_subjects as $subject) {
        $sql = "INSERT INTO credited_subjects (reference_id, subject_name) VALUES ('$reference_id', '$subject')";
        if (!mysqli_query($conn, $sql)) {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

?>