<?php

// resize jpeg file
function resize_jpeg($src, $width) {
    list($imgWidth, $imgHeight) = getimagesize($src);
    if($width < $imgWidth) {
        $height = $imgHeight * $width / $imgWidth;
    } else {
        $width = $imgWidth;
        $height = $imgHeight;
    }
    $im = imagecreatetruecolor($width, $height) or die('Cannot Initialize new GD image stream');
    $imgSource = imagecreatefromjpeg($src);
    imagecopyresampled($im, $imgSource, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);
    imagejpeg($im);
    imagedestroy($im);
    imagedestroy($img_source);
}
