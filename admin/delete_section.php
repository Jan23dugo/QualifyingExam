<?php
include_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

try {
    // Get and validate input
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (!isset($data['section_id'])) {
        throw new Exception('Section ID is required');
    }

    $section_id = intval($data['section_id']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // First delete all questions in this section
        // This will automatically delete related data (options, test cases) due to foreign key constraints
        $delete_questions = $conn->prepare("DELETE FROM questions WHERE section_id = ?");
        $delete_questions->bind_param("i", $section_id);
        $delete_questions->execute();

        // Then delete the section
        $delete_section = $conn->prepare("DELETE FROM sections WHERE section_id = ?");
        $delete_section->bind_param("i", $section_id);
        $delete_section->execute();

        if ($delete_section->affected_rows === 0) {
            throw new Exception('Section not found or already deleted');
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Section deleted successfully']);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 