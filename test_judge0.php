<?php
require_once 'api/judge0_handler.php';

try {
    $judge0 = new Judge0Handler();
    
    // Test 1: Get available languages
    echo "<h3>Testing Language List:</h3>";
    $languages = $judge0->getLanguages();
    if (isset($languages['error'])) {
        echo "Error getting languages: " . $languages['error'];
    } else {
        echo "Successfully retrieved " . count($languages) . " languages<br>";
    }

    // Test 2: Submit a simple Python code
    echo "<h3>Testing Code Submission:</h3>";
    $pythonCode = "print('Hello from Judge0!')";
    $result = $judge0->submitCode($pythonCode, 71); // 71 is Python3
    
    echo "<pre>";
    if (isset($result['error'])) {
        echo "Error executing code: " . $result['error'];
    } else {
        echo "Submission Result:\n";
        echo "Output: " . ($result['stdout'] ?? 'No output') . "\n";
        echo "Error: " . ($result['stderr'] ?? 'No errors') . "\n";
        echo "Status: " . ($result['status']['description'] ?? 'Unknown status');
    }
    echo "</pre>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 