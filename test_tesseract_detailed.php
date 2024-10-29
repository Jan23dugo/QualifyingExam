<?php
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/tesseract_config.php';
use thiagoalessio\TesseractOCR\TesseractOCR;

function checkTesseractInstallation() {
    echo "=== Tesseract Installation Check ===\n\n";

    // Check if the Tesseract executable exists
    if (file_exists(TESSERACT_PATH)) {
        echo "✓ Tesseract executable found at: " . TESSERACT_PATH . "\n";
    } else {
        echo "✗ ERROR: Tesseract executable NOT found at: " . TESSERACT_PATH . "\n";
        echo "Please verify the installation path in config/tesseract_config.php\n";
        return false;
    }

    // Check if tessdata directory exists
    $tessdataDir = dirname(TESSERACT_PATH) . '/tessdata';
    if (is_dir($tessdataDir)) {
        echo "✓ Tessdata directory found at: " . $tessdataDir . "\n";
    } else {
        echo "✗ ERROR: Tessdata directory NOT found at: " . $tessdataDir . "\n";
        return false;
    }

    // Try to execute tesseract --version
    try {
        $version = shell_exec('"' . TESSERACT_PATH . '" --version 2>&1');
        if ($version) {
            echo "✓ Tesseract version information:\n";
            echo $version . "\n";
        } else {
            echo "✗ ERROR: Could not execute Tesseract\n";
            return false;
        }
    } catch (Exception $e) {
        echo "✗ ERROR executing Tesseract: " . $e->getMessage() . "\n";
        return false;
    }

    // Check PHP extension requirements
    echo "\n=== PHP Extensions Check ===\n\n";
    $required_extensions = ['fileinfo', 'mbstring'];
    foreach ($required_extensions as $ext) {
        if (extension_loaded($ext)) {
            echo "✓ {$ext} extension is loaded\n";
        } else {
            echo "✗ ERROR: {$ext} extension is NOT loaded\n";
            return false;
        }
    }

    return true;
}

try {
    if (checkTesseractInstallation()) {
        echo "\n=== Testing OCR Functionality ===\n\n";
        
        // Create a simple test image with text
        $testImage = __DIR__ . '/test.png';
        if (!file_exists($testImage)) {
            echo "Creating test image...\n";
            $im = imagecreatetruecolor(200, 50);
            $white = imagecolorallocate($im, 255, 255, 255);
            $black = imagecolorallocate($im, 0, 0, 0);
            imagefilledrectangle($im, 0, 0, 200, 50, $white);
            imagestring($im, 5, 10, 10, 'Test OCR 123', $black);
            imagepng($im, $testImage);
            imagedestroy($im);
        }

        // Test OCR
        echo "Performing OCR test...\n";
        $ocr = new TesseractOCR($testImage);
        $ocr->executable(TESSERACT_PATH);
        $text = $ocr->run();
        echo "OCR Output: " . $text . "\n";
        
        if (!empty($text)) {
            echo "✓ OCR test successful!\n";
        } else {
            echo "✗ OCR test failed - no text extracted\n";
        }
    }
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 