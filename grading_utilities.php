<?php
// grading_utilities.php

// Function to fetch grading system rules for a university
function getGradingSystemRules($conn, $universityName) {
    $query = "SELECT ugs.min_grade, ugs.max_grade, ugs.grade_value, gt.grading_type
              FROM university_grading_systems ugs
              JOIN grading_types gt ON ugs.grading_type_id = gt.id
              WHERE ugs.university_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $universityName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $gradingRules = [];
    while ($row = $result->fetch_assoc()) {
        $gradingRules[] = $row;
    }

    return $gradingRules;
}

// Function to determine eligibility based on grades and grading rules
function determineEligibility($grades, $gradingRules) {
    $eligible = true;

    foreach ($grades as $grade) {
        $grade = trim($grade);
        $grade = str_replace(',', '.', $grade);

        $isGradeEligible = false;
        
        // Loop through the grading rules to check eligibility
        foreach ($gradingRules as $rule) {
            $minGrade = (float)$rule['min_grade'];
            $maxGrade = (float)$rule['max_grade'];
            $gradeValue = $rule['grade_value'];
            $gradingType = $rule['grading_type'];
            
            // Determine if the grade falls within the current range
            if ($grade >= $minGrade && $grade <= $maxGrade) {
                // If the grading type is 'letter', check if it's a passing grade
                if ($gradingType === 'letter' && strtoupper($gradeValue) !== 'F') {
                    $isGradeEligible = true;
                    break;
                } 
                // If the grading type is numeric, check if it's below the threshold
                elseif ($gradingType === 'numeric_custom' && $gradeValue <= 2.50) {
                    $isGradeEligible = true;
                    break;
                }
                // Add additional grading type checks as needed
            }
        }
        
        // If no eligible grading rule is matched, mark as ineligible
        if (!$isGradeEligible) {
            $eligible = false;
            break;
        }
    }

    return $eligible;
}
