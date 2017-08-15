<?php

require_once __DIR__ . '/markdown.php';

function getMetadata($mardown)
{
    $matches = [];
    preg_match_all("/<!--([^>]*)-->/", $mardown, $matches);

    $metadata = [];
    foreach ($matches[1] as $str) {
        $a = explode(":", $str);
        $key = trim($a[0]);
        $value = array_reduce(array_slice($a, 2), function ($carry, $item) {
            return $carry.":".$item;
        }, $a[1]);
        $metadata[$key] = trim($value);
    }
    return $metadata;
}

function searchFile($file, $dir, $root_path)
{
    while (!file_exists($dir.$file)) {
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

function locateFont($font, $dir, $root_path)
{
    $formats = ["woff2", "woff", "ttf", "otf"];

    // if the extension is given, serve this format only
    if (in_array(pathinfo($font, PATHINFO_EXTENSION), $formats)) {
        $location = searchFile($font, $dir, $root_path);
        if (file_exists($location)) {
            return $location;
        } else {
            return "";
        }
    }

    // if the extension is not given, try different formats
    foreach ($formats as $ext) {
        $location = searchFile($font.'.'.$ext, $dir, $root_path);
        if (file_exists($location)) {
            return $location;
        }
    }

    return "";
}

function genCoverImageHTML($src, $root_path, $dir, $root_url)
{
    if ($src == "") {
        return "";
    }

    $coverPicture = "";

    if (parse_url($src, PHP_URL_HOST) != null && parse_url($src, PHP_URL_HOST) !== $_SERVER['HTTP_HOST']) {
        $coverImg_path = $coverImg_url = $src;
    } else {
        $coverImg_url = $dir."/".$src;
        $coverImg_path = $root_path."/".$dir."/".$src;
    }

    $coverPicture .= '<div class="cover" style="background-image:url(\'data:image/jpeg;base64,'.base64img($coverImg_url).'\')">';
    $coverPicture .= '<picture>';

    for ($i = 0; $i < 20; $i++) {
        $screenWidth = 100 + 100 * $i;
        $width = $screenWidth;
        $height = floor($screenWidth * 0.70);
        $coverPicture .= '<source srcset="'.$root_url.'_gd?url='.urlencode($coverImg_url).'&w='.$width.'&h='.$height.'" media="(max-width: '.$width.'px) and (max-height: '.$height.'px)">';
    }

    $coverPicture .= '<img onerror="document.body.removeChild(document.body.firstChild)" onload="this.style.opacity=1" src="'.$coverImg_path.'">';
    $coverPicture .= '</picture>';
    $coverPicture .= '</div>';
    // We add a bit of js to ensure a proper height for the cover
    // (vh is not an option due to this issue : http://stackoverflow.com/questions/24944925/background-image-jumps-when-address-bar-hides-ios-android-mobile-chrome)
    $coverPicture .= '
    <script>
        document.querySelectorAll(".cover, .cover > picture").forEach(
            e => e.style.height = Math.floor(window.innerHeight * .70) + "px"
        );
    </script>
    ';

    return $coverPicture;
}

function genPostHTML($dir, $root_path, $root_url)
{



    foreach (scandir($dir) as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) == "md") {
            $path = $dir.$file;
            break;
        }
    }
    if($path == null) {
        echo "No markdown file here !";
        exit;
    }

    $markdown = file_get_contents($path);

    $filename = sys_get_temp_dir()."/".sha1($markdown);
    if (file_exists($filename)) {
        return file_get_contents($filename);
    }

    $metadata = getMetadata($markdown);

    $coverPictureHTML = genCoverImageHTML($metadata['cover-image'], $root_path, $dir, $root_url);

    $html = "";
    $html .= '<!DOCTYPE html><html><head>';
    $html .= '<meta charset="utf8">';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';

    if (!empty($metadata["title"])) {
        $html .= '<title>'.$metadata['title'].'</title>';
        $html .= '<meta property="og:title" content="'.$metadata["title"].'">';
    }

    if (!empty($metadata["description"])) {
        $html .= '<meta property="og:description" content="'.$metadata["description"].'">';
    }
    
    if (!empty($metadata["cover-image"])) {
        $html .= '<meta property="og:image" content="'.$_SERVER['HTTP_X_FORWARDED_PROTO'].':'.$root_url.$dir.$metadata["cover-image"].'">';
    }

    if (isset($metadata['title-font'])) {
        $font = locateFont($metadata['title-font'], $dir, $root_path);
        $html .= '<style>@font-face{font-family:"TitleFont";src:'.'url("'.$root_url.$font.'");} h1,h2,h3,h4,h5,h6{font-family: "TitleFont", serif;}</style>';
    }
    
    if (isset($metadata['text-font'])) {
        $font = locateFont($metadata['text-font'], $dir, $root_path);
        $html .= '<style>@font-face{font-family:"TextFont";src:'.'url("'.$root_url.$font.'");} body{font-family: "TextFont", sans-serif;}</style>';
    }

    $html .= '<style>';
    $html .= file_get_contents("style.css");
    $html .= '</style>';
    $html .= '</head><body>';
    $html .= '<div id="progress"></div>';
    $html .= '
        <script>
            document.addEventListener("scroll", function() {
                document.getElementById("progress").style.width = window.scrollY/(document.body.scrollHeight - window.innerHeight)*100 + "%";
            });
        </script>';
    $html .= $coverPictureHTML;
    $html .= '<main><article>';
    $html .= parseMarkDown($markdown, $root_url, $dir);
    $html .= '</article></main>';
    $html .= '</body>';
    $html .= '</html>';
    
    $filename = sys_get_temp_dir()."/".sha1($html);
    file_put_contents($filename, $html);

    return $html;
}
