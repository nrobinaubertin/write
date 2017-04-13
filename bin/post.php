<?php

require_once __DIR__ . '/markdown.php';

function getMetadata($mardown) {
    $matches = [];
    preg_match_all("/<!--([^>]*)-->/",$mardown,$matches);

    $metadata = [];
    foreach($matches[1] as $str) {
        $a = explode(":",$str);
        $key = trim($a[0]);
        $value = trim($a[1]);
        $metadata[$key] = $value;
    }
    return $metadata;
}

function searchFile($file, $dir, $root_path) {
    while(!file_exists($dir.$file)) {
        if (realpath($dir) == realpath($root_path)) {
            break;
        }
        $dir = $dir."../";
    }

    if (file_exists($dir.$file)) {
        return $dir.$file;
    } else {
        return "";
    }
}

function locateFont($font, $dir, $root_path) {
    $formats = ["woff2", "woff", "ttf", "otf"];

    // if the extension is given, serve this format only
    if (in_array(pathinfo($font, PATHINFO_EXTENSION), $formats)) {
        $location = searchFile($font, $dir, $root_path);
        if (file_exists($location)) {
            return 'url("'.$root_path.$location.'")';
        } else {
            return "";
        }
    }

    // if the extension is not given, try different formats
    foreach($formats as $ext) {
        $location = searchFile($font.'.'.$ext, $dir, $root_path);
        if (file_exists($location)) {
            return 'url("'.$root_path.$location.'")';
        }
    }

    return "";
}

function genPostHTML($dir, $root_path) {
    
    foreach(scandir($dir) as $file) {
        if(pathinfo($file, PATHINFO_EXTENSION) == "md") {
            $path = $dir."/".$file;
            break;
        }
    }

    $markdown = file_get_contents($path);
    $metadata = getMetadata($markdown); 

	$html = "";

	$html .= '<!DOCTYPE html><html><head>';
	$html .= '<meta charset="utf8">';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';

    if(isset($metadata["title"])) {
        $html .= '<title>'.$metadata['title'].'</title>';
        $html .= '<meta property="og:title" content="'.$metadata["title"].'">';
    }

    if(isset($metadata["description"])) {
        $html .= '<meta property="og:description" content="'.$metadata["description"].'">';
    }

    if(isset($metadata['title-font'])) {
        $html .= '<style>@font-face{font-family:"TitleFont";src:'.locateFont($metadata['title-font'], $dir, $root_path).';} h1,h2,h3,h4,h5,h6{font-family: "TitleFont", serif;}</style>';
    }
    
    if(isset($metadata['text-font'])) {
        $html .= '<style>@font-face{font-family:"TextFont";src:'.locateFont($metadata['text-font'], $dir, $root_path).';} body{font-family: "TextFont", serif;}</style>';
    }

	$html .= '<style>';
	$html .= file_get_contents("style.css");
	$html .= '</style>';
    
    if(isset($metadata['cover-image'])) {
        $html .= '<style>';
        for($i = 0; $i < 10; $i++) {
            $screenWidth = 250 + 250 * $i;
            $size = floor($screenWidth * 0.75);
            $html .= '@media (min-height: '.$screenWidth.'px) { .cover{height: '.$size.'px} .cover + main{top: '.$size.'px} }';
        }
        $html .= '</style>';
    }

    $html .= '</head><body>';
    
    if(isset($metadata['cover-image'])) {
        $img_url = $dir.$metadata['cover-image'];
        $img_path = $root_path.$dir.$metadata['cover-image'];

        $html .= '<div class="cover" style="background-image:url(\'data:image/jpeg;base64,'.base64img($img_url).'\')">';
        $html .= '<picture>';
        for($i = 0; $i < 10; $i++) {
            $screenWidth = 250 + 250 * $i;
            $width = $screenWidth;
            $height = floor($screenWidth * 0.75);
            $html .= '<source srcset="'.$root_path.'_gd?url='.urlencode($img_url).'&w='.$width.'&h='.$height.'" media="(max-width: '.$width.'px) and (max-height: '.$height.'px)">';
        }
        $html .= '<img onload="this.style.opacity=1" src="'.$img_path.'">';
        $html .= '</picture>';
        $html .= '</div>';
    }

    $html .= '<main><article>';

    $html .= parseMarkDown($markdown, $root_path, $dir);

    $html .= '</article></main>';
    $html .= '</body></html>';
	return $html;
}
