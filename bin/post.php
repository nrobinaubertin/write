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

function locateFile($file, $dir, $root_path, $formats = [])
{
    if (count($formats) === 0) {
        $location = searchFile($file, $dir, $root_path);
        if (file_exists($location)) {
            return $location;
        } else {
            return "";
        }
    } else {
        foreach ($formats as $ext) {
            $location = searchFile($file.'.'.$ext, $dir, $root_path);
            if (file_exists($location)) {
                return $location;
            }
        }
    }

    return "";
}

function genCoverImageHTML($root_path, $dir, $root_url, $metadata)
{
    $src = $metadata["cover-image"];
    if (!$src) {
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

    $coverPicture .= '<img onerror="document.body.removeChild(document.body.firstChild)" onload="this.style.opacity=1" src="'.$coverImg_url.'">';
    $coverPicture .= '</picture>';

    // cover credits
    if ($metadata["cover-credit-url"] && $metadata["cover-credit-title"]) {
        $coverPicture .= '<a class="credit-badge" href="'.$metadata["cover-credit-url"].'" target="_blank" rel="noopener noreferrer">';
        $coverPicture .= '<span><svg viewBox="0 0 32 32">';
        $coverPicture .= '<path d="M20.8 18.1c0 2.7-2.2 4.8-4.8 4.8s-4.8-2.1-4.8-4.8c0-2.7 2.2-4.8 4.8-4.8 2.7.1 4.8 2.2 4.8 4.8zm11.2-7.4v14.9c0 2.3-1.9 4.3-4.3 4.3h-23.4c-2.4 0-4.3-1.9-4.3-4.3v-15c0-2.3 1.9-4.3 4.3-4.3h3.7l.8-2.3c.4-1.1 1.7-2 2.9-2h8.6c1.2 0 2.5.9 2.9 2l.8 2.4h3.7c2.4 0 4.3 1.9 4.3 4.3zm-8.6 7.5c0-4.1-3.3-7.5-7.5-7.5-4.1 0-7.5 3.4-7.5 7.5s3.3 7.5 7.5 7.5c4.2-.1 7.5-3.4 7.5-7.5z"></path>';
        $coverPicture .= '</svg></span><span>'.$metadata["cover-credit-title"].'</span></a>';
    }

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

function genMeta($metadata, $dir, $root_path, $root_url)
{
    $canonical_url = $root_url.preg_replace("/^posts\//","",$dir);

    $html = "";
    $html .= '<meta charset="utf8">';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
    $html .= '<meta property="og:url" content="'.$_SERVER['HTTP_X_FORWARDED_PROTO'].':'.$canonical_url.'">';
    $html .= '<meta property="og:type" content="article">';

    if (!empty($metadata["title"])) {
        $html .= '<title>'.$metadata['title'].'</title>';
        $html .= '<meta property="og:title" content="'.$metadata["title"].'">';
    }

    if (!empty($metadata["description"])) {
        $html .= '<meta property="og:description" content="'.$metadata["description"].'">';
    }

    if (!empty($metadata["cover-image"])) {
        //$html .= '<meta property="og:image" content="'.$_SERVER['HTTP_X_FORWARDED_PROTO'].':'.$root_url.$dir.$metadata["cover-image"].'">';
        $html .= '<meta property="og:image" content="'.$_SERVER['HTTP_X_FORWARDED_PROTO'].':'.$root_url.'_gd?url='.urlencode($dir.$metadata["cover-image"]).'&w=1024&h=768">';
    }
    return $html;
}

function genPostHTML($dir, $root_path, $root_url)
{
    if (file_exists($dir."index.md")) {
        $path = $dir."index.md";
    } else {
        foreach (scandir($dir) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == "md") {
                $path = $dir.$file;
                break;
            }
        }
    }
    if(!isset($path)) {
        echo "No markdown file here !";
        exit;
    }

    $markdown = file_get_contents($path);

    $filename = sys_get_temp_dir()."/".sha1($markdown);
    if (file_exists($filename)) {
        return file_get_contents($filename);
    }

    $metadata = getMetadata($markdown);

    $coverPictureHTML = "";
    if (isset($metadata['cover-image'])) {
        $coverPictureHTML = genCoverImageHTML($root_path, $dir, $root_url, $metadata);
    }

    $html = "";
    $html .= '<!DOCTYPE html><html><head>';
    $html .= genMeta($metadata, $dir, $root_path, $root_url);

    $fontFormats = ["woff2", "woff", "ttf", "otf"];

    if (isset($metadata['title-font'])) {
        $font = locateFile($metadata['title-font'], $dir, $root_path, $fontFormats);
        $html .= '<style>@font-face{font-family:"TitleFont";src:'.'url("'.$root_url.$font.'");} h1,h2,h3,h4,h5,h6{font-family: "TitleFont", serif;}</style>';
    }

    if (isset($metadata['text-font'])) {
        $font = locateFile($metadata['text-font'], $dir, $root_path, $fontFormats);
        $html .= '<style>@font-face{font-family:"TextFont";src:'.'url("'.$root_url.$font.'");} body{font-family: "TextFont", sans-serif;}</style>';
    }

    if (isset($metadata['style-file'])) {
        $cssFile = locateFile($metadata['style-file'], $dir, $root_path);
    }
    if (empty($cssFile)) {
        $cssFile = "default.css";
    }

    $html .= '<style>';
    $html .= file_get_contents($cssFile);
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
