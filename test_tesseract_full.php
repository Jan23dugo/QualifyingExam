<?php
require 'vendor/autoload.php';
require_once 'config/tesseract_config.php';
use thiagoalessio\TesseractOCR\TesseractOCR;

try {
    echo "Checking Tesseract installation...\n";
    
    // Check if tesseract executable exists
    if (file_exists(TESSERACT_PATH)) {
        echo "Tesseract executable found at: " . TESSERACT_PATH . "\n";
    } else {
        echo "ERROR: Tesseract executable not found at: " . TESSERACT_PATH . "\n";
    }
    
    // Test OCR on a sample image
    $sampleImagePath = __DIR__ . '/test.png'; // Create a simple test image
    
    if (file_exists($sampleImagePath)) {
        echo "\nTesting OCR with sample image...\n";
        $ocr = new TesseractOCR($sampleImagePath);
        $ocr->executable(TESSERACT_PATH);
        $text = $ocr->run();
        echo "OCR Output: " . $text . "\n";
    }
    
    echo "\nTesseract version:\n";
    echo shell_exec('"' . TESSERACT_PATH . '" --version');
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 