<?php
require_once __DIR__ . '/../config/judge0_config.php';

class Judge0Handler {
    private $headers;
    
    public function __construct() {
        $this->headers = [
            'X-RapidAPI-Host: ' . JUDGE0_API_HOST,
            'X-RapidAPI-Key: ' . JUDGE0_API_KEY,
            'Content-Type: application/json'
        ];
    }

    public function submitCode($sourceCode, $language_id) {
        $curl = curl_init();
        
        $postData = [
            'source_code' => $sourceCode,
            'language_id' => $language_id,
            'stdin' => '', // Add input if needed
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://" . JUDGE0_API_HOST . "/submissions?base64_encoded=false&wait=true",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => $this->headers
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['error' => $err];
        }

        return json_decode($response, true);
    }

    public function getLanguages() {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://" . JUDGE0_API_HOST . "/languages",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $this->headers
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ['error' => $err];
        }

        return json_decode($response, true);
    }
} 