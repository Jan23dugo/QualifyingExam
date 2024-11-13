<?php
require 'vendor/autoload.php';
require 'config/azure_config.php';

use GuzzleHttp\Client;

function testAzureConnection() {
    $client = new GuzzleHttp\Client();
    
    try {
        // Create a simple test image - black text on white background
        $image = imagecreatetruecolor(200, 100);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Fill background with white
        imagefilledrectangle($image, 0, 0, 200, 100, $white);
        
        // Add some text
        imagestring($image, 5, 50, 40, "Test 123", $black);
        
        // Capture the image data
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        // Use the base endpoint URL and append the vision API path
        $endpoint = rtrim(AZURE_ENDPOINT, '/') . '/vision/v3.2/read/analyze';
        
        echo "Sending request to: " . $endpoint . "\n";
        
        // Test the OCR endpoint with actual image data
        $response = $client->post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/octet-stream',
                'Ocp-Apim-Subscription-Key' => AZURE_KEY
            ],
            'body' => $imageData
        ]);
        
        if ($response->getStatusCode() === 202) {
            $operationLocation = $response->getHeader('Operation-Location')[0];
            echo "Connection successful! Azure Computer Vision service is accessible.\n";
            echo "Operation Location: " . $operationLocation;
            return true;
        }
        
    } catch (Exception $e) {
        echo "Connection failed: " . $e->getMessage();
        if (method_exists($e, 'getResponse') && $e->getResponse()) {
            echo "\nResponse body: " . $e->getResponse()->getBody();
        }
        echo "\nEndpoint: " . $endpoint;
        echo "\nPlease verify your Azure credentials and endpoint URL.";
        return false;
    }
}

testAzureConnection();