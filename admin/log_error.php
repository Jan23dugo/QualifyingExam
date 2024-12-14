<?php
header('Content-Type: application/json');

// Define log file path
$logFile = __DIR__ . '/logs/error.log';

try {
    // Debug: Log raw input
    file_put_contents(__DIR__ . '/logs/debug.log', "Raw input: " . file_get_contents('php://input') . "\n", FILE_APPEND);

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Debug: Log parsed data
    file_put_contents(__DIR__ . '/logs/debug.log', "Parsed data: " . print_r($data, true) . "\n", FILE_APPEND);
    
    if (!$data) {
        throw new Exception('No data received');
    }

    // Format log message
    $logMessage = sprintf(
        "[%s]\nContext: %s\nURL: %s\nExam ID: %s\nMessage: %s\nStack Trace:\n%s\n%s\n",
        $data['timestamp'] ?? 'No timestamp',
        $data['context'] ?? 'No context',
        $data['url'] ?? 'No URL',
        $data['exam_id'] ?? 'No exam ID',
        $data['message'] ?? 'No message',
        $data['stack'] ?? 'No stack trace',
        str_repeat('-', 80)  // Separator line
    );

    // Debug: Log the formatted message
    file_put_contents(__DIR__ . '/logs/debug.log', "Attempting to write message:\n" . $logMessage . "\n", FILE_APPEND);

    // Write to log file
    if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
        throw new Exception('Failed to write to log file');
    }

    // Debug: Log success
    file_put_contents(__DIR__ . '/logs/debug.log', "Successfully wrote to error.log\n", FILE_APPEND);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Log the error to both PHP's error log and our debug log
    $errorMessage = 'Error logger failed: ' . $e->getMessage();
    error_log($errorMessage);
    file_put_contents(__DIR__ . '/logs/debug.log', $errorMessage . "\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 