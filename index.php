<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once __DIR__ . '/bin/post.php';
require_once __DIR__ . '/bin/gd.php';

$root_path = dirname($_SERVER["DOCUMENT_URI"]);
$uri = current(explode("?", dirname($_SERVER['REQUEST_URI'])));

if ($root_path == $uri) {
    $uri = "/".current(explode("?", basename($_SERVER['REQUEST_URI'])));
}

// check if this is a job for gd
if ($uri == "/_gd") {
    $imgInfos = getimagesize($_GET["url"]);
    if (preg_match("/^image/", $imgInfos["mime"])) {
        $w = isset($_GET["w"]) ? intval($_GET["w"]) : 0;
        $h = isset($_GET["h"]) ? intval($_GET["h"]) : 0;
        output_image(urldecode($_GET["url"]), [$w, $h], $imgInfos);
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo "400 Bad Request";
    }
    exit;
}


// make sure that we point to the post directory
if (substr($uri, 0, strlen("posts")) != "posts") {
    $uri = "posts" . $uri;
}

if (file_exists($uri)) {

    // if it's the name of a directory, display the markdown file
    if (is_dir($uri)) {

        // redirect to the path with a trailing slash if it's not present (this is necessary to get local files for the post)
        if (substr($_SERVER["REQUEST_URI"], -1) != "/") {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".$_SERVER["REQUEST_URI"]."/");
            exit;
        }

        echo genPostHTML($uri, $root_path);
        exit;
    } else {
        if (is_readable($uri)) {
            header('Content-Type: '.mime_content_type($uri));
            readfile($uri);
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
