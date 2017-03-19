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
    $html .= '<title>' . $metadata['title'] . '</title>';
	$html .= '<meta charset="utf8">';

    if(isset($metadata['title-font'])) {
        $html .= '<style>@font-face{font-family:"TitleFont";src:url("' . $root_path."/".$metadata['title-font'] . '");} h1,h2,h3,h4,h5,h6{font-family: "TitleFont", serif;}</style>';
    }
    
    if(isset($metadata['text-font'])) {
        $html .= '<style>@font-face{font-family:"TextFont";src:url("' . $metadata['text-font'] . '");} p{font-family: "TextFont", serif;}</style>';
    }

	$html .= '<style>';
	$html .= file_get_contents("style.css");
	$html .= '</style>';

	//$html .= '<script>';
	//$html .= file_get_contents("script.js");
	//$html .= '</script>';

    $html .= '</head><body>';
    
    if(isset($metadata['cover-image'])) {
        $html .= '<div class="cover" style="background-image:url(\'' . $metadata['cover-image'] . '\')"></div>';
    }

    $html .= '<article>';

    $html .= parseMarkDown($markdown);

    $html .= '</article>';
    $html .= '</body></html>';
	return $html;
}

?>
