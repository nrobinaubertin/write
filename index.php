<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/post.php';
require_once __DIR__ . '/router.php';
require_once __DIR__ . '/gd.php';

if ($_SERVER['REQUEST_METHOD'] != "GET" || $_SERVER["DOCUMENT_URI"] == "") {
    exit;
}

$root_path = getRootPath();
$uri = getUri($root_path);


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

            header('Content-Type: '.mime_content_type($uri));
            switch(mime_content_type($uri)) {
                case "image/jpeg":
                    if(isset($_GET["w"])) {
                        $width = min(2000, intval($_GET["w"]));
                        resize_jpeg($uri, $width);
                    } else {
                        readfile($uri);
                    }
                    break;
                default:
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
