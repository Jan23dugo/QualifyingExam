<?php
include_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Function to sanitize input but preserve HTML from CKEditor
function sanitize_input($data) {
    if (empty($data)) return '';
    return $data; // Preserve CKEditor formatting
}

// Function to log errors
function logError($error, $context = '') {
    $logFile = __DIR__ . '/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] $context: $error\n";
    error_log($message, 3, $logFile);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postData = file_get_contents('php://input');
        $data = json_decode($postData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }
        
        if (empty($data['exam_id'])) {
            throw new Exception('Exam ID is required');
        }

        $exam_id = intval($data['exam_id']);
        
        // Verify exam exists
        $stmt = $conn->prepare("SELECT exam_id FROM exams WHERE exam_id = ?");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            throw new Exception("Invalid exam ID: $exam_id");
        }

        $conn->begin_transaction();

        try {
            // Handle different actions
            $action = $data['action'] ?? 'save_sections';
            
            switch ($action) {
                case 'delete_section':
                    if (empty($data['section_id'])) {
                        throw new Exception('Section ID is required for deletion');
                    }
                    
                    $section_id = intval($data['section_id']);
                    
                    // Delete the section (cascading delete will handle related records)
                    $stmt = $conn->prepare("DELETE FROM sections WHERE section_id = ? AND exam_id = ?");
                    $stmt->bind_param("ii", $section_id, $exam_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to delete section: " . $stmt->error);
                    }
                    break;

                case 'save_sections':
                    if (!empty($data['sections'])) {
                        foreach ($data['sections'] as $index => $sectionData) {
                            if (empty($sectionData['title'])) {
                                throw new Exception('Section title is required');
                            }

                            $title = $sectionData['title'];
                            $description = $sectionData['description'] ?? '';
                            $section_order = $sectionData['section_order'] ?? $index;
                            
                            if (!empty($sectionData['section_id'])) {
                                // Update existing section
                                $stmt = $conn->prepare("UPDATE sections SET title = ?, description = ?, section_order = ? WHERE section_id = ? AND exam_id = ?");
                                $stmt->bind_param("ssiii", $title, $description, $section_order, $sectionData['section_id'], $exam_id);
                            } else {
                                // Create new section
                                $stmt = $conn->prepare("INSERT INTO sections (exam_id, title, description, section_order) VALUES (?, ?, ?, ?)");
                                $stmt->bind_param("issi", $exam_id, $title, $description, $section_order);
                            }
                            
                            if (!$stmt->execute()) {
                                throw new Exception("Error saving section: " . $stmt->error);
                            }
                        }
                    }
                    break;

                default:
                    throw new Exception('Invalid action specified');
            }
            
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Changes saved successfully']);
            
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    } else {
        throw new Exception('Invalid request method');
    }
    
} catch (Exception $e) {
    logError($e->getMessage(), 'save_question.php');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

exit;
?>
