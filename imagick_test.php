<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (class_exists('Imagick')) {
    try {
        $imagick = new Imagick();
        $imagick->newImage(100, 100, new ImagickPixel('red'));
        $imagick->setImageFormat('png');

        header("Content-Type: image/png");
        echo $imagick;

    } catch (Exception $e) {
        echo "Error creating image: " . $e->getMessage();
    }
} else {
    echo "Imagick is not installed";
}
?>
