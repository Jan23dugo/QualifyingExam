<?php
$subscriptionKey = "FVYm463cREqzCksKR321NICLAcLVeKAVFsUjmGPslg8kIe8JqDg1JQQJ99AKACqBBLyXJ3w3AAAFACOGc33R";
$endpoint = "https://streams-ocr.cognitiveservices.azure.com/";
$url = $endpoint . "vision/v3.2/read/analyze";

// Image URL for OCR analysis
$imageUrl = 'https://upload.wikimedia.org/wikipedia/commons/a/a3/June_odd-eyed-cat.jpg';

$headers = [
    "Ocp-Apim-Subscription-Key: $subscriptionKey",
    "Content-Type: application/json"
];

// Prepare the JSON payload for an image URL
$data = json_encode(["url" => $imageUrl]);

// Initialize cURL session
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

// Execute the request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'Request Error:' . curl_error($ch);
} else {
    echo 'Response:' . $response;
}

// Close cURL session
curl_close($ch);
?>
