<?php

function getUri($root_path) {

    $uri = $_SERVER['REQUEST_URI'];

    // Strip query string (?foo=bar) and decode URI
    if (false !== $pos = strpos($uri, '?')) {
        $uri = substr($uri, 0, $pos);
    }

    // decode url
    $uri = rawurldecode($uri);

    // remove the root path from the query
    if (substr($uri, 0, strlen($root_path)) == $root_path) {
        $uri = substr($uri, strlen($root_path));
    }

    // remove the first "/"
    if(substr($uri, 0, 1) == "/") {
        $uri = substr($uri, 1);
    }

    // make sure that we point to the post directory
    if (substr($uri, 0, strlen("posts/")) != "posts/") {
        $uri = "posts/" . $uri;
    }

    // remove "/" duplicates
    $uri = preg_replace("/\/+/","/",$uri);

    return $uri;
}

function getRootPath() {
    
    // the root_path is the directory of index.php
    return dirname($_SERVER["DOCUMENT_URI"]);

}
