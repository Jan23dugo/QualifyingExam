<?php
require 'vendor/autoload.php';
use thiagoalessio\TesseractOCR\TesseractOCR;

try {
    echo "Checking Tesseract installation...\n";
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec('where tesseract', $output, $returnVar);
    } else {
        exec('which tesseract', $output, $returnVar);
    }
    
    if ($returnVar === 0) {
        echo "Tesseract is installed.\n";
        echo "Location: " . $output[0] . "\n";
    } else {
        echo "Tesseract is not installed or not in PATH.\n";
    }
    
    echo "\nTesting OCR library...\n";
    if (class_exists('thiagoalessio\TesseractOCR\TesseractOCR')) {
        echo "PHP Tesseract OCR wrapper is properly installed.\n";
    } else {
        echo "PHP Tesseract OCR wrapper is not installed.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 