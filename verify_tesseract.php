<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/tesseract_config.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

try {
    echo "Testing Tesseract OCR installation...\n";
    
    // Check if class exists
    if (!class_exists(TesseractOCR::class)) {
        throw new Exception("TesseractOCR class not found");
    }
    echo "✓ TesseractOCR class found\n";
    
    // Create a simple test image
    $testImage = __DIR__ . '/test.png';
    $im = imagecreatetruecolor(200, 50);
    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);
    imagefilledrectangle($im, 0, 0, 200, 50, $white);
    imagestring($im, 5, 10, 10, 'Test OCR 123', $black);
    imagepng($im, $testImage);
    imagedestroy($im);
    
    // Test OCR
    $ocr = new TesseractOCR($testImage);
    $ocr->executable(TESSERACT_PATH);
    $text = $ocr->run();
    
    echo "OCR Result: " . $text . "\n";
    echo "✓ OCR test completed successfully\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 