<?php
function getExamData($exam_id, $conn) {
    try {
        // Get exam details
        $exam_stmt = $conn->prepare("
            SELECT e.*, COUNT(DISTINCT q.question_id) as total_questions 
            FROM exams e 
            LEFT JOIN sections s ON e.exam_id = s.exam_id
            LEFT JOIN questions q ON s.section_id = q.section_id
            WHERE e.exam_id = ?
            GROUP BY e.exam_id
        ");
        
        if (!$exam_stmt) {
            throw new Exception("Failed to prepare exam statement: " . $conn->error);
        }

        $exam_stmt->bind_param("i", $exam_id);
        $exam_stmt->execute();
        $exam = $exam_stmt->get_result()->fetch_assoc();

        if (!$exam) {
            throw new Exception("Exam not found");
        }

        // Get sections with their questions
        $sections_stmt = $conn->prepare("
            SELECT 
                s.section_id,
                s.title as section_title,
                s.description as section_description,
                s.section_order,
                q.question_id,
                q.question_text,
                q.question_type,
                q.question_order,
                q.programming_language
            FROM sections s
            LEFT JOIN questions q ON s.section_id = q.section_id
            WHERE s.exam_id = ?
            ORDER BY s.section_order, q.question_order
        ");

        if (!$sections_stmt) {
            throw new Exception("Failed to prepare sections statement: " . $conn->error);
        }

        $sections_stmt->bind_param("i", $exam_id);
        $sections_stmt->execute();
        $result = $sections_stmt->get_result();

        $sections = [];
        $question_number = 1;

        while ($row = $result->fetch_assoc()) {
            $section_id = $row['section_id'];
            
            // Initialize section if not exists
            if (!isset($sections[$section_id])) {
                $sections[$section_id] = [
                    'title' => $row['section_title'],
                    'description' => $row['section_description'],
                    'questions' => []
                ];
            }

            if ($row['question_id']) {
                // Get options for multiple choice questions
                $options = [];
                if ($row['question_type'] === 'multiple_choice') {
                    $options_stmt = $conn->prepare("
                        SELECT choice_text 
                        FROM multiple_choice_options 
                        WHERE question_id = ?
                        ORDER BY option_id  /* Changed from option_order to option_id */
                    ");
                    $options_stmt->bind_param("i", $row['question_id']);
                    $options_stmt->execute();
                    $options_result = $options_stmt->get_result();
                    while ($option = $options_result->fetch_assoc()) {
                        $options[] = $option['choice_text'];
                    }
                }

                // Add question to section
                $sections[$section_id]['questions'][] = [
                    'number' => $question_number++,
                    'question_id' => $row['question_id'],
                    'question' => $row['question_text'],
                    'type' => $row['question_type'],
                    'options' => $options,
                    'programming_language' => $row['programming_language']
                ];
            }
        }

        return [
            'exam' => $exam,
            'sections' => $sections
        ];

    } catch (Exception $e) {
        error_log("Error in exam_data_handler.php: " . $e->getMessage());
        throw $e;
    }
}
?>