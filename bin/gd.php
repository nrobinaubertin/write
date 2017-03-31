<?php

require_once __DIR__ . '/router.php';

// resize image file
function resize_image($src, $width, $imgInfos) {
    if($src == "" || intval($width) == 0) {
        exit;
    }

    $width = min(2000, intval($width));
    if(isset($imgInfos)) {
        list($imgWidth, $imgHeight, $type) = $imgInfos;
    } else {
        list($imgWidth, $imgHeight, $type) = getimagesize($src);
    }
    $mimeType = image_type_to_mime_type($type);

    switch($mimeType) {
    case "image/jpeg":
        $imgSource = imagecreatefromjpeg($src);
        break;
    case "image/png":
        $imgSource = imagecreatefrompng($src);
        break;
    case "image/bmp":
        $imgSource = imagecreatefrombmp($src);
        break;
    case "image/webp":
        $imgSource = imagecreatefromwebp($src);
        break;
    case "image/gif":
        $imgSource = imagecreatefromgif($src);
        break;
    default:
        readfile($src);
        exit;
    }

    if($width < $imgWidth) {
        $height = $imgHeight * $width / $imgWidth;
    } else {
        $width = $imgWidth;
        $height = $imgHeight;
    }
    $im = imagecreatetruecolor($width, $height) or die('Cannot Initialize new GD image stream');
    imagecopyresampled($im, $imgSource, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);

    header('Content-Type: '.$mimeType);
    switch($mimeType) {
    case "image/jpeg":
        imagejpeg($im);
        break;
    case "image/png":
        imagepng($im);
        break;
    case "image/bmp":
        imagebmp($im);
        break;
    case "image/webp":
        imagewebp($im);
        break;
    case "image/gif":
        imagegif($im);
        break;
    }

    imagedestroy($im);
    imagedestroy($img_source);
}
