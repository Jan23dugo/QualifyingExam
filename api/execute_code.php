<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$language = $input['language'] ?? '';
$code = $input['code'] ?? '';
$testCases = $input['testCases'] ?? [];

// Configure JDoodle API
$JDOODLE_API = 'https://api.jdoodle.com/v1/execute';
$CLIENT_ID = 'your-client-id';  // Replace with your JDoodle client ID
$CLIENT_SECRET = 'your-client-secret';  // Replace with your JDoodle client secret

function executeCode($language, $code, $input) {
    global $JDOODLE_API, $CLIENT_ID, $CLIENT_SECRET;

    // Language IDs for JDoodle API
    $languageMap = [
        'python' => ['python3', '4'],
        'java' => ['java', '4'],
        'c' => ['c', '5']
    ];

    if (!isset($languageMap[$language])) {
        throw new Exception("Unsupported programming language");
    }

    [$langName, $langVersion] = $languageMap[$language];

    // Prepare the submission data
    $postData = [
        'clientId' => $CLIENT_ID,
        'clientSecret' => $CLIENT_SECRET,
        'script' => $code,
        'language' => $langName,
        'versionIndex' => $langVersion,
        'stdin' => $input
    ];

    $curl = curl_init($JDOODLE_API);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($postData)
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        throw new Exception("Code execution failed: $error");
    }

    $result = json_decode($response, true);
    
    return [
        'stdout' => $result['output'] ?? '',
        'stderr' => $result['error'] ?? '',
        'statusCode' => $result['statusCode'] ?? 0,
        'memory' => $result['memory'] ?? '',
        'cpuTime' => $result['cpuTime'] ?? ''
    ];
}

try {
    if (!in_array($language, ['java', 'python', 'c'])) {
        throw new Exception("Unsupported programming language");
    }

    $results = [];
    if (empty($testCases)) {
        $testCases = [['input' => '', 'output' => '']];
    }

    foreach ($testCases as $testCase) {
        $result = executeCode($language, $code, $testCase['input']);
        
        $output = '';
        if (!empty($result['stdout'])) {
            $output .= $result['stdout'];
        }
        if (!empty($result['stderr'])) {
            $output .= "\nError:\n" . $result['stderr'];
        }

        $results[] = [
            'input' => $testCase['input'],
            'expectedOutput' => $testCase['output'],
            'actualOutput' => $output,
            'cpuTime' => $result['cpuTime'],
            'memory' => $result['memory']
        ];
    }

    echo json_encode([
        'success' => true,
        'results' => $results
    ]);

} catch (Exception $e) {
    error_log("Error in execute_code.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 