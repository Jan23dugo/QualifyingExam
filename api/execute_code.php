<?php
header('Content-Type: application/json');

// JDoodle API Configuration
define('JDOODLE_CLIENT_ID', '8c686c1b1579a59d4b1757074bb59fd2'); // Replace with your JDoodle client ID
define('JDOODLE_CLIENT_SECRET', '33aff191a34149669be03ad9e1853e67a894f9460fff913952c1e2441ea4ac77'); // Replace with your JDoodle client secret
define('JDOODLE_API_URL', 'https://api.jdoodle.com/v1/execute');

function executeCode($code, $language) {
    $languageMap = [
        'java' => ['java', 3],
        'python' => ['python3', 3],
        'c' => ['c', 4],
        'cpp' => ['cpp', 3],
        'javascript' => ['nodejs', 3]
    ];

    $postData = [
        'clientId' => JDOODLE_CLIENT_ID,
        'clientSecret' => JDOODLE_CLIENT_SECRET,
        'script' => $code,
        'language' => $languageMap[$language][0],
        'versionIndex' => $languageMap[$language][1]
    ];

    $ch = curl_init(JDOODLE_API_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return [
            'success' => false,
            'error' => $error
        ];
    }

    return json_decode($response, true);
}

// Handle incoming requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['code']) || !isset($input['language'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters']);
        exit;
    }

    $result = executeCode($input['code'], $input['language']);
    echo json_encode($result);
} 