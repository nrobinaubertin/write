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
	$html .= '<meta charset="utf8">';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';

    if(isset($metadata["title"])) {
        $html .= '<title>' . $metadata['title'] . '</title>';
        $html .= '<meta property="og:title" content="' . $metadata["title"] . '">';
    }

    if(isset($metadata["description"])) {
        $html .= '<meta property="og:description" content="' . $metadata["description"] . '">';
    }

    if(isset($metadata['title-font'])) {
        $html .= '<style>@font-face{font-family:"TitleFont";src:url("' . $root_path."/".$dir.$metadata['title-font'] . '");} h1,h2,h3,h4,h5,h6{font-family: "TitleFont", serif;}</style>';
    }
    
    if(isset($metadata['text-font'])) {
        $html .= '<style>@font-face{font-family:"TextFont";src:url("' . $root_path."/".$dir.$metadata['text-font'] . '");} p{font-family: "TextFont", serif;}</style>';
    }

	$html .= '<style>';
	$html .= file_get_contents("style.css");
	$html .= '</style>';

    $html .= '</head><body>';
    
    if(isset($metadata['cover-image'])) {
        $img_url = $dir.$metadata['cover-image'];
        $img_path = $root_path."/".$dir.$metadata['cover-image'];
        $html .= '<div class="cover">';
        $html .= '<picture>';
        for($i = 0; $i < 20; $i++) {
            $screenWidth = 200 + 100 * $i;
            $size = $screenWidth - 100;
            $html .= '<source srcset="'.$root_path.'/_gd?url='.urlencode($img_url).'&w='.$size.'" media="(max-width: '.$size.'px)">';
        }
        $html .= '<img src="'.$img_path.'">';
        $html .= '</picture>';
        $html .= '</div>';
    }

    $html .= '<main><article>';

    $html .= parseMarkDown($markdown, $root_path, $dir);

    $html .= '</article></main>';
    $html .= '</body></html>';
	return $html;
}

?>
