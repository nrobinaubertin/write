<?php

require_once __DIR__ . '/router.php';

// calculate new size given a max newWidth and a max newHeight
function calcNewSize($oldWidth, $oldHeight, $maxWidth, $maxHeight) {
    if($oldWidth == 0 || $oldHeight == 0) {
        return false;
    }

    if($maxHeight == 0 && $maxWidth == 0) {
        return false;
    }

    // starting point for the width and height
    $width = $oldWidth;
    $height = $oldHeight;


    if($maxWidth != 0) {
        if($maxWidth < $oldWidth) {
            $width = $maxWidth;
            $height = $oldHeight * $maxWidth / $oldWidth;
        } 
    }

    if($maxHeight != 0) {
        if($maxHeight < $height) {
            $width = $width * $maxHeight / $height;
            $height = $maxHeight;
        }
    }

    return [$width, $height];
}

// resize image file
// $size_array == [$maxWidth, $maxHeight]
function resize_image($src, $size_array, $imgInfos) {

    if($src == "" || count($size_array) < 2) {
        exit;
    }

    list($width, $height) = $size_array;

    $width = min(2000, intval($width));
    $height = min(2000, intval($height));

    if(isset($imgInfos)) {
        list($imgWidth, $imgHeight, $type) = $imgInfos;
    } else {
        list($imgWidth, $imgHeight, $type) = getimagesize($src);
    }
    $mimeType = image_type_to_mime_type($type);

    list($width, $height) = calcNewSize($imgWidth, $imgHeight, $width, $height);

    $filename = sys_get_temp_dir()."/".sha1($src.$width.$height);

    if (file_exists($filename)) {
        header('Content-Type: '.$mimeType);
        readfile($filename);
    } else {

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

        $im = imagecreatetruecolor($width, $height) or die('Cannot Initialize new GD image stream');
        imagecopyresampled($im, $imgSource, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);

        header('Content-Type: '.$mimeType);
        switch($mimeType) {
        case "image/jpeg":
            imagejpeg($im);
            imagejpeg($im, $filename);
            break;
        case "image/png":
            imagepng($im);
            imagepng($im, $filename);
            break;
        case "image/bmp":
            imagebmp($im);
            imagejpeg($im, $filename);
            break;
        case "image/webp":
            imagewebp($im);
            imagejpeg($im, $filename);
            break;
        case "image/gif":
            imagegif($im);
            imagejpeg($im, $filename);
            break;
        }

        imagedestroy($im);
        imagedestroy($img_source);
    }
}
