<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/post.php';

$root_path = preg_replace("/[^\/]+\.php$/","",$_SERVER["DOCUMENT_URI"]);
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

if (substr($uri, 0, strlen($root_path)) == $root_path) {
    $uri = substr($uri, strlen($root_path));
} 

switch(pathinfo($uri, PATHINFO_EXTENSION)) {
    case "md":
        echo genHTML($uri, $_SERVER["HTTP_HOST"]);
        break;
    default:
        //header('Content-Type: image/jpeg');
        //echo file_get_contents($uri);
        readfile($uri);
        break;
        
}


?>
