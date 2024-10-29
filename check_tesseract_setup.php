<?php
echo "=== Tesseract OCR Setup Diagnostic Tool ===\n\n";

// 1. Check PHP Version
echo "1. PHP Version Check:\n";
echo "Current PHP version: " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '7.4', '>=')) {
    echo "✓ PHP version is compatible\n";
} else {
    echo "✗ PHP 7.4 or higher is required\n";
}
echo "\n";

// 2. Check Composer and Autoloader
echo "2. Composer Check:\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✓ Composer autoloader found\n";
    require __DIR__ . '/vendor/autoload.php';
} else {
    echo "✗ Composer autoloader not found. Run 'composer install'\n";
}

if (file_exists(__DIR__ . '/composer.json')) {
    echo "✓ composer.json found\n";
    $composerJson = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);
    if (isset($composerJson['require']['thiagoalessio/tesseract_ocr'])) {
        echo "✓ tesseract_ocr package is listed in composer.json\n";
    } else {
        echo "✗ tesseract_ocr package not found in composer.json\n";
    }
} else {
    echo "✗ composer.json not found\n";
}
echo "\n";

// 3. Check Tesseract Configuration
echo "3. Tesseract Configuration Check:\n";
if (file_exists(__DIR__ . '/config/tesseract_config.php')) {
    echo "✓ tesseract_config.php found\n";
    require_once __DIR__ . '/config/tesseract_config.php';
    
    if (defined('TESSERACT_PATH')) {
        echo "✓ TESSERACT_PATH is defined: " . TESSERACT_PATH . "\n";
        if (file_exists(TESSERACT_PATH)) {
            echo "✓ Tesseract executable exists\n";
        } else {
            echo "✗ Tesseract executable not found at specified path\n";
        }
    } else {
        echo "✗ TESSERACT_PATH is not defined\n";
    }
} else {
    echo "✗ tesseract_config.php not found\n";
}
echo "\n";

// 4. Check Required PHP Extensions
echo "4. PHP Extensions Check:\n";
$required_extensions = ['fileinfo', 'mbstring', 'gd'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ {$ext} extension is loaded\n";
    } else {
        echo "✗ {$ext} extension is missing\n";
    }
}
echo "\n";

// 5. Check Directory Permissions
echo "5. Directory Permissions Check:\n";
$directories = [
    'uploads/tor' => __DIR__ . '/uploads/tor',
    'uploads/school_id' => __DIR__ . '/uploads/school_id',
    'tessdata' => dirname(TESSERACT_PATH) . '/tessdata'
];

foreach ($directories as $name => $path) {
    if (file_exists($path)) {
        echo "✓ {$name} directory exists: {$path}\n";
        if (is_writable($path)) {
            echo "✓ {$name} directory is writable\n";
        } else {
            echo "✗ {$name} directory is not writable\n";
        }
    } else {
        echo "✗ {$name} directory does not exist: {$path}\n";
    }
}
echo "\n";

// 6. Check Tesseract Version and Functionality
echo "6. Tesseract Version Check:\n";
try {
    $version = shell_exec('"' . TESSERACT_PATH . '" --version 2>&1');
    if ($version) {
        echo "✓ Tesseract version information:\n";
        echo $version;
    } else {
        echo "✗ Could not get Tesseract version\n";
    }
} catch (Exception $e) {
    echo "✗ Error executing Tesseract: " . $e->getMessage() . "\n";
}
echo "\n";

// 7. Check Language Data
echo "7. Tesseract Language Data Check:\n";
$tessdata_dir = dirname(TESSERACT_PATH) . '/tessdata';
$required_lang_files = ['eng.traineddata'];

foreach ($required_lang_files as $lang_file) {
    $lang_path = $tessdata_dir . '/' . $lang_file;
    if (file_exists($lang_path)) {
        echo "✓ Language file found: {$lang_file}\n";
    } else {
        echo "✗ Language file missing: {$lang_file}\n";
    }
}
echo "\n";

// 8. Test OCR Functionality
echo "8. OCR Functionality Test:\n";
try {
    if (class_exists('thiagoalessio\TesseractOCR\TesseractOCR')) {
        echo "✓ TesseractOCR class is available\n";
        
        // Create test image
        $testImage = __DIR__ . '/test_ocr.png';
        if (!file_exists($testImage)) {
            $im = imagecreatetruecolor(200, 50);
            $white = imagecolorallocate($im, 255, 255, 255);
            $black = imagecolorallocate($im, 0, 0, 0);
            imagefilledrectangle($im, 0, 0, 200, 50, $white);
            imagestring($im, 5, 10, 10, 'Test OCR 123', $black);
            imagepng($im, $testImage);
            imagedestroy($im);
        }
        
        $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($testImage);
        $ocr->executable(TESSERACT_PATH);
        $text = $ocr->run();
        
        if (!empty($text)) {
            echo "✓ OCR test successful! Output: {$text}\n";
        } else {
            echo "✗ OCR test failed - no text extracted\n";
        }
    } else {
        echo "✗ TesseractOCR class not found\n";
    }
} catch (Exception $e) {
    echo "✗ OCR test error: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Complete ===\n"; 