<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/tesseract_config.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

try {
    // Test class existence
    if (class_exists(TesseractOCR::class)) {
        echo "TesseractOCR class found!\n";
        
        // Try to instantiate
        $ocr = new TesseractOCR(__DIR__ . '/test_ocr.png');
        $ocr->executable(TESSERACT_PATH);
        echo "Successfully created OCR instance\n";
    } else {
        echo "TesseractOCR class not found\n";
        
        // Show loaded classes
        echo "\nLoaded classes:\n";
        print_r(get_declared_classes());
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 