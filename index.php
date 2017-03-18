<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/post.php';
require_once __DIR__ . '/home.php';

if ($_SERVER['REQUEST_METHOD'] != "GET" || $_SERVER["DOCUMENT_URI"] == "") {
    exit;
}

$root_path = preg_replace("/\/?[^\/]+\.php$/","",$_SERVER["DOCUMENT_URI"]);
$uri = $_SERVER['REQUEST_URI'];


// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// remove the root path from the query
if (substr($uri, 0, strlen($root_path)) == $root_path) {
    $uri = substr($uri, strlen($root_path));
}

// remove the posts directory from the query
if (substr($uri, 0, strlen("posts/")) == "posts/") {
    $uri = substr($uri, strlen("posts/"));
}

// do something when we query the root...
if($uri == "" || $uri == "/") {
    echo genHomeHTML($root_path);
    exit;
}

if(!file_exists($uri)) {
    // if it's the name of a directory, display the markdown file
    if(is_dir("posts/".$uri)) {
        // redirect to the path with a trailing slash if it's not present (this is necessary to get local files for the post)
        if(substr($uri, -1) != "/") {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".$root_path.$uri."/"); 
        }
        foreach(scandir("posts/".$uri) as $file) {
            if(pathinfo($file, PATHINFO_EXTENSION) == "md") {
                $uri = "posts/".$uri."/".$file;
                break;
            }
        }
    } else {
        // check if the file exists in the posts directory
        if(file_exists("posts/".$uri)) {
            $uri = "posts/".$uri;
        }
    }
}

switch(pathinfo($uri, PATHINFO_EXTENSION)) {
    case "md":
        echo genPostHTML($uri, $_SERVER["HTTP_HOST"]);
        break;
    default:
        readfile($uri);
        break;
}

?>
