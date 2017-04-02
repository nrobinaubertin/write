<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once __DIR__ . '/bin/post.php';
require_once __DIR__ . '/bin/router.php';
require_once __DIR__ . '/bin/gd.php';

if ($_SERVER['REQUEST_METHOD'] != "GET" || $_SERVER["DOCUMENT_URI"] == "") {
    header("HTTP/1.1 400 Bad Request");
    echo "400 Bad Request";
    exit;
}

$root_path = getRootPath();
$uri = getUri($root_path, $_SERVER['REQUEST_URI']);

// check if this is a job for gd
if (substr($_SERVER["REQUEST_URI"], 0, strlen($root_path."/_gd")) == $root_path."/_gd") {
    $imgInfos = getimagesize($_GET["url"]);
    if(preg_match("/^image/", $imgInfos["mime"])) {
        if(isset($_GET["w"])) {
            $w = intval($_GET["w"]);
        } else {
            $w = 0;
        }
        if(isset($_GET["h"])) {
            $h = intval($_GET["h"]);
        } else {
            $h = 0;
        }
        resize_image(urldecode($_GET["url"]), [$w, $h], $imgInfos);
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo "400 Bad Request";
    }
    exit;
}

if(file_exists($uri)) {

    // if it's the name of a directory, display the markdown file
    if(is_dir($uri)) {

        // redirect to the path with a trailing slash if it's not present (this is necessary to get local files for the post)
        if(substr($_SERVER["REQUEST_URI"], -1) != "/") {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".$_SERVER["REQUEST_URI"]."/"); 
            exit;
        }

        echo genPostHTML($uri, $root_path);
        exit;

    } else {

        if(is_readable($uri)) {
            $imgInfos = getimagesize($_GET["url"]);
            if(preg_match("/^image/", $imgInfos["mime"])) {
                if(isset($_GET["w"])) {
                    $w = intval($_GET["w"]);
                } else {
                    $w = 0;
                }
                if(isset($_GET["h"])) {
                    $h = intval($_GET["h"]);
                } else {
                    $h = 0;
                }
                resize_image($uri, [$w, $h], $imgInfos);
            } else {
                readfile($uri);
            }
            exit;
        } 

        header("HTTP/1.1 403 Forbidden");
        echo "403 Forbidden";
        exit;

    }

} else {

    header("HTTP/1.1 404 Not Found");
    echo "404 Not Found";
    exit;

}

?>
