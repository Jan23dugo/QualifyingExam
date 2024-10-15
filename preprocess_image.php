<?php
// preprocess_image.php

function preprocessImage($image_path, $upload_dir) {
    try {
        // Create an Imagick instance
        $imagick = new Imagick($image_path);
        
        // Ensure the resolution is adequate, setting it to 300 DPI if needed
        $imagick->setImageResolution(300, 300);
        
        // Convert to grayscale to help OCR focus on the text only
        $imagick->setImageType(Imagick::IMGTYPE_GRAYSCALE);

        // Save the processed image
        $processedImagePath = $upload_dir . 'processed_' . basename($image_path);
        $imagick->writeImage($processedImagePath);
        
        // Return the path of the processed image
        return $processedImagePath;

    } catch (Exception $e) {
        return "Error processing image: " . $e->getMessage();
    }
}
