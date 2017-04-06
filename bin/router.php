<?php

function getUri($root_path, $uri) {
    // remove "/" duplicates
    $uri = preg_replace("/\/+/","/",$uri);

    // Strip query string (?foo=bar)
    if (false !== $pos = strpos($uri, '?')) {
        $uri = substr($uri, 0, $pos);
    }

    // remove the root path from the query (we want it to be a relative path)
    $uri = preg_replace("/^".str_replace("/","\\/",$root_path)."?/","",$uri);

    // remove the first "/"
    if(substr($uri, 0, 1) == "/") {
        $uri = substr($uri, 1);
    }

    return $uri;
}

function getRootPath() {
    // the root_path is the directory of index.php
    return dirname($_SERVER["DOCUMENT_URI"])."/";
}
