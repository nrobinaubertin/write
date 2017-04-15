<?php

// get informations from image
function loadImage($src)
{
    list($imgWidth, $imgHeight, $type) = getimagesize($src);
    $mimeType = image_type_to_mime_type($type);

    switch ($mimeType) {
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
        $imgSource = false;
    }

    return [$imgWidth, $imgHeight, $mimeType, $imgSource];
}

// calculate new size given a max newWidth and a max newHeight
function calcNewSize($oldWidth, $oldHeight, $maxWidth, $maxHeight)
{
    if ($oldWidth == 0 || $oldHeight == 0) {
        return false;
    }

    // starting point for the width and height
    $width = $oldWidth;
    $height = $oldHeight;

    if ($maxWidth != 0) {
        if ($maxWidth < $oldWidth) {
            $width = $maxWidth;
            $height = $oldHeight * $maxWidth / $oldWidth;
        }
    }

    if ($maxHeight != 0) {
        if ($maxHeight < $height) {
            $width = $width * $maxHeight / $height;
            $height = $maxHeight;
        }
    }

    return [floor($width), floor($height)];
}

// $size_array == [$maxWidth, $maxHeight]
function output_image($src, $size_array)
{
    if ($src == "" || count($size_array) < 2) {
        return false;
    }
    
    header('Content-Type: image/jpeg');

    list($wantedWidth, $wantedHeight) = $size_array;
    $filename = sys_get_temp_dir()."/".sha1($src.$wantedWidth.$wantedHeight);
    if (file_exists($filename)) {
        readfile($filename);
        exit;
    }

    $width = min(2000, intval($wantedWidth));
    $height = min(2000, intval($wantedHeight));

    list($imgWidth, $imgHeight, $mimeType, $imgSource) = loadImage($src);
    list($width, $height) = calcNewSize($imgWidth, $imgHeight, $width, $height);

    $im = imagecreatetruecolor($width, $height) or die('Cannot Initialize new GD image stream');
    imagecopyresampled($im, $imgSource, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);

    imagejpeg($im);
    imagejpeg($im, $filename);

    if (isset($im)) {
        imagedestroy($im);
    }
    if (isset($img_source)) {
        imagedestroy($img_source);
    }
    exit;
}

function base64img($src)
{
    $width = $height = 64;
    $filename = sys_get_temp_dir()."/".sha1($src.$width.$height);
    if (file_exists($filename)) {
        return base64_encode(file_get_contents($filename));
    }

    list($imgWidth, $imgHeight, $mimeType, $imgSource) = loadImage($src);
    list($width, $height) = calcNewSize($imgWidth, $imgHeight, $width, $height);
    $im = imagecreatetruecolor($width, $height) or die('Cannot Initialize new GD image stream');
    imagecopyresampled($im, $imgSource, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);
    imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR);

    ob_start();
    imagejpeg($im);
    $base64img = base64_encode(ob_get_contents());
    ob_end_clean();

    imagejpeg($im, $filename);

    if (isset($im)) {
        imagedestroy($im);
    }
    if (isset($img_source)) {
        imagedestroy($img_source);
    }

    return $base64img;
}
