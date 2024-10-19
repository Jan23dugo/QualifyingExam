<?php
// preprocess_image.php

function preprocessImage($image_path) {
    try {
        // Create an Imagick instance
        $imagick = new Imagick($image_path);
        
        // Ensure the resolution is adequate, setting it to 300 DPI if needed
        $imagick->setImageResolution(300, 300);
        
        // Convert to grayscale to help OCR focus on the text only
        $imagick->setImageType(Imagick::IMGTYPE_GRAYSCALE);

        // Resize image for better OCR accuracy
        $imagick->adaptiveResizeImage(1024, 768);
        
        // Apply a slight deskew to correct orientation
        $imagick->deskewImage(0.4);

        // Apply threshold to binarize the image (black and white)
        $imagick->thresholdImage(0.6 * Imagick::getQuantumRange()['quantumRangeLong']);

        // Overwrite the original image with the processed version
        $imagick->writeImage($image_path);

        // Return the path of the processed image
        return $image_path;

    } catch (Exception $e) {
        return "Error processing image: " . $e->getMessage();
    }
}
