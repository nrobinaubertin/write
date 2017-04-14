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

// resize image file
// $size_array == [$maxWidth, $maxHeight]
function resize_image($src, $size_array)
{
    if ($src == "" || count($size_array) < 2) {
        return false;
    }

    list($width, $height) = $size_array;

    $width = min(2000, intval($width));
    $height = min(2000, intval($height));

    list($imgWidth, $imgHeight, $mimeType, $imgSource) = loadImage($src);
    list($width, $height) = calcNewSize($imgWidth, $imgHeight, $width, $height);
    $filename = sys_get_temp_dir()."/".sha1($src.$width.$height);

    $im = imagecreatetruecolor($width, $height) or die('Cannot Initialize new GD image stream');
    imagecopyresampled($im, $imgSource, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);

    header('Content-Type: image/jpeg');
    imagejpeg($im);
    imagejpeg($im, $filename);

    if (isset($im)) {
        imagedestroy($im);
    }
    if (isset($img_source)) {
        imagedestroy($img_source);
    }
}

function base64img($src)
{
    list($imgWidth, $imgHeight, $mimeType, $imgSource) = loadImage($src);
    list($width, $height) = calcNewSize($imgWidth, $imgHeight, 32, 32);
    $im = imagecreatetruecolor($width, $height) or die('Cannot Initialize new GD image stream');
    imagecopyresampled($im, $imgSource, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);
    imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR);

    ob_start();
    imagejpeg($im);
    $base64img = base64_encode(ob_get_contents());
    ob_end_clean();

    if (isset($im)) {
        imagedestroy($im);
    }
    if (isset($img_source)) {
        imagedestroy($img_source);
    }

    return $base64img;
}
