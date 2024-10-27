<?php
// grading_utilities.php
// This file contains functions that are used in Evaluating Grading System on Different UNiversities

// Function to fetch grading system rules for a university
function getGradingSystemRules($conn, $universityName) {
    error_log("Fetching grading rules for: " . $universityName);
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

    if (empty($gradingRules)) {
        error_log("No grading rules found for: " . $universityName);
    } else {
        error_log("Found " . count($gradingRules) . " grading rules for " . $universityName);
    }

    return $gradingRules;
}

function determineEligibility($grades, $gradingRules) {
    error_log("Grades to check: " . print_r($grades, true));
    error_log("Grading rules: " . print_r($gradingRules, true));

    if (empty($gradingRules)) {
        error_log("Error: No grading rules available.");
        return false;
    }

    $eligible = true;

    foreach ($grades as $grade) {
        $grade = floatval(trim(str_replace(',', '.', $grade)));
        error_log("Checking grade: $grade");

        $isGradeEligible = false;

        foreach ($gradingRules as $rule) {
            $minGrade = (float)$rule['min_grade'];
            $maxGrade = (float)$rule['max_grade'];
            $gradeValue = $rule['grade_value'];
            $gradingType = strtolower($rule['grading_type']);

            error_log("Checking against rule: min=$minGrade, max=$maxGrade, value=$gradeValue, type=$gradingType");

            if ($gradingType === 'numeric_custom') {
                if ($grade >= $minGrade && $grade <= $maxGrade) {
                    $isGradeEligible = true;
                    error_log("Numeric grade $grade is eligible");
                    break;
                }
            } elseif ($gradingType === 'letter') {
                if (strtoupper($grade) === strtoupper($gradeValue) && strtoupper($gradeValue) !== 'F') {
                    $isGradeEligible = true;
                    error_log("Letter grade $grade is eligible");
                    break;
                }
            }
            // Add more grading type checks as needed
        }

        if (!$isGradeEligible) {
            error_log("Grade $grade is not eligible");
            $eligible = false;
            break;
        }
    }

    error_log("Final eligibility result: " . ($eligible ? "Eligible" : "Not Eligible"));
    return $eligible;
}

?>