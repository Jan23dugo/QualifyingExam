<?php
// Use forward slashes and escape the path properly
define('TESSERACT_PATH', str_replace('\\', '/', 'C:/Program Files/Tesseract-OCR/tesseract.exe'));

// Add additional Tesseract configurations
define('TESSERACT_DATA_DIR', str_replace('\\', '/', 'C:/Program Files/Tesseract-OCR/tessdata'));