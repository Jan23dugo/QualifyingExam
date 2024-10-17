<?php
// credit_subjects_matching.php

// Hardcoded credit requirements for IT and CS programs
$creditRequirements = [
    'IT' => [
        ['subject_code' => 'ACCO 20213', 'description' => 'Accounting Principles', 'min_units' => 3],
        ['subject_code' => 'COMP 20013', 'description' => 'Introduction to Computing', 'min_units' => 3],
        ['subject_code' => 'COMP 20023', 'description' => 'Computer Programming 1', 'min_units' => 3],
        ['subject_code' => 'CWTS 10013', 'description' => 'Civic Welfare Training Service 1', 'min_units' => 3],
        ['subject_code' => 'GEED 10053', 'description' => 'Mathematics in the Modern World', 'min_units' => 3],
        ['subject_code' => 'GEED 10063', 'description' => 'Purposive Communication', 'min_units' => 3],
        ['subject_code' => 'GEED 10103', 'description' => 'Filipinolohiya at Pambansang Kaunlaran', 'min_units' => 3],
        ['subject_code' => 'PHED 10012', 'description' => 'Physical Fitness and Self-Testing Activities', 'min_units' => 2],

        ['subject_code' => 'COMP 20033', 'description' => 'Computer Programming 2', 'min_units' => 3],
        ['subject_code' => 'COMP 20043', 'description' => 'Discrete Structures 1', 'min_units' => 3],
        ['subject_code' => 'CWTS 10023', 'description' => 'Civic Welfare Training Service 2', 'min_units' => 3],
        ['subject_code' => 'GEED 10033', 'description' => 'Readings in Philippine History', 'min_units' => 3],
        ['subject_code' => 'GEED 10113', 'description' => 'Pagsasalin sa Kontekstong Filipino', 'min_units' => 3],
        ['subject_code' => 'GEED 20023', 'description' => 'Politics, Governance and Citizenship', 'min_units' => 3],
        ['subject_code' => 'PHED 10022', 'description' => 'Rhythmic Activities', 'min_units' => 2],
    ],
    'CS' => [
        ['subject_code' => 'GEED 10053', 'description' => 'Mathematics in the Modern World', 'min_units' => 3],
        ['subject_code' => 'GEED 10063', 'description' => 'Purposive Communication', 'min_units' => 3],
        ['subject_code' => 'GEED 10103', 'description' => 'Filipinolohiya at Pambansang Kaunlaran', 'min_units' => 3],
        ['subject_code' => 'GEED 20023', 'description' => 'Politics, Governance and Citizenship', 'min_units' => 3],
        ['subject_code' => 'COMP 20013', 'description' => 'Introduction to Computing', 'min_units' => 3],
        ['subject_code' => 'COMP 20023', 'description' => 'Computer Programming 1', 'min_units' => 3],
        ['subject_code' => 'PHED 20023', 'description' => 'Physical Education 1', 'min_units' => 2],
        ['subject_code' => 'NSTP 20023', 'description' => 'National Service Training Program 1', 'min_units' => 3],
 
        ['subject_code' => 'GEED 10083', 'description' => 'Science, Technology and Society', 'min_units' => 3],
        ['subject_code' => 'GEED 10113', 'description' => 'Intelektwaslisasyon ng Filipino sa ibat ibang Larangan', 'min_units' => 3],
        ['subject_code' => 'MATH 20333', 'description' => 'Differential Calculus', 'min_units' => 3],
        ['subject_code' => 'GEED 10023', 'description' => 'Understanding the Self', 'min_units' => 3],
        ['subject_code' => 'COMP 20023', 'description' => 'Computer Programming 2', 'min_units' => 3],
        ['subject_code' => 'COMP 20043', 'description' => 'Discrete Structures 1', 'min_units' => 3],
        ['subject_code' => 'PHED 10022', 'description' => 'Physical Education', 'min_units' => 2],
        ['subject_code' => 'NSTP 10023', 'description' => 'National Service Training Program 2', 'min_units' => 3],
    ]
];

// Function to determine credited subjects based on extracted text and credit requirements
function determineCreditSubjects($extractedText, $desiredProgram, $creditRequirements) {
    $creditedSubjects = [];

    // Check if the desired program is listed in the credit requirements
    if (!isset($creditRequirements[$desiredProgram])) {
        return $creditedSubjects; // No requirements found for the desired program
    }

    // Extract subject codes, descriptions, and units using improved regex
    preg_match_all('/([A-Z]{2,3}\d{3})/', $extractedText, $extractedCodes); // Matches subject codes like IT101
    preg_match_all('/([A-Za-z\s\d,:-]+)/', $extractedText, $extractedDescriptions); // Matches descriptions
    preg_match_all('/(\d+)\s?(?:credits?|units?)/i', $extractedText, $extractedUnits); // Matches units followed by "credits" or "units"

    // Flatten the results arrays
    $extractedCodes = $extractedCodes[0];
    $extractedDescriptions = $extractedDescriptions[0];
    $extractedUnits = $extractedUnits[0];

    // Loop through the program's credit requirements
    foreach ($creditRequirements[$desiredProgram] as $requirement) {
        $subjectCode = $requirement['subject_code'];
        $description = $requirement['description'];
        $requiredUnits = (float)$requirement['min_units'];

        // Attempt to find the subject code in the extracted text
        foreach ($extractedCodes as $codeIndex => $extractedCode) {
            if (stripos($extractedCode, $subjectCode) !== false) {
                // If the subject code is found, look for the description and units nearby
                $descriptionMatch = false;
                $unitsMatch = false;
                $unitsValue = 0;

                // Check if the description matches in the extracted text
                foreach ($extractedDescriptions as $extractedDescription) {
                    if (stripos($extractedDescription, $description) !== false) {
                        $descriptionMatch = true;
                        break;
                    }
                }

                // Check if there are sufficient units for the subject
                foreach ($extractedUnits as $extractedUnit) {
                    $unitsValue = (float)$extractedUnit;
                    if ($unitsValue >= $requiredUnits) {
                        $unitsMatch = true;
                        break;
                    }
                }

                // If all conditions match, add the credited subject to the list
                if ($descriptionMatch && $unitsMatch) {
                    $creditedSubjects[] = [
                        'subject_code' => $subjectCode,
                        'description' => $description,
                        'units' => $unitsValue
                    ];
                }
            }
        }
    }

    return $creditedSubjects;
}
?>
