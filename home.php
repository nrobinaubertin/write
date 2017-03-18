<?php

function createPostList($path) {

    $posts = [];
    foreach(scandir($path) as $e) {
        if($e != "." && $e != "..") {
            $post = [];
            $post['dirname'] = $e;

            // looking for markdown file
            foreach(scandir($path."/".$e) as $file) {
                if(pathinfo($file, PATHINFO_EXTENSION) == "md") {
                    $post['path'] = $path.$e."/".$file;
                    $post['filename'] = $file;
                    $post['url'] = $e;
                    break;
                }
            }

            $post['markdown'] = file_get_contents($post['path']);
            $post['metadata'] = getMetadata($post['markdown']);
            $post['title'] = (isset($post['metadata']['title'])) ? $post['metadata']['title'] : pathinfo($post['filename'], PATHINFO_FILENAME);
            $post['date'] = (isset($post['metadata']['date'])) ? strtotime($post['metadata']['date']) : filemtime($post['path']);
            $post['words'] = count(explode(" ",$post['markdown']));
            unset($post['markdown']);

            $posts[] = $post;
        }
    }

    usort($posts, function($a, $b) {
        if(
            isset($a["metadata"]["date"]) 
            && isset($b["metadata"]["date"])
            && $a_date = strtotime($a["metadata"]["date"])
            && $b_date = strtotime($b["metadata"]["date"])
        ) {
            return $a_date > $b_date;
        } else {
            return strncmp($a['dirname'],$b['dirname'],30);
        }
    });

    return $posts;
}

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

function genLinkToPostHTML($post, $root_path) {
    $html = "";
    $html .= '<div>';
    $html .= '<h4>';
    $html .= '<small>' . date('Y-m-d', $post['date']) . '</small>';
    $html .= '<br>';
    $html .= '<a href="' . $root_path . '/' . $post['url'] . '">'; 
    $html .= $post['title'];
    $html .= '</a>';
    $html .= '</h4>';
    $html .= '</div>';
    return $html;
}

function genHomeHTML($root_path) {
    $html = "";
    $html .= '<!DOCTYPE html><html><head>';
    $html .= '<meta charset="utf8">';

    $html .= '<style>';
    $html .= file_get_contents("style.css");
    $html .= '</style>';

    $html .= '<script>';
    $html .= file_get_contents("script.js");
    $html .= '</script>';

    $html .= '</head><body><article>';

    $html .= '<h1>Title of the blog</h1>';

    $posts = createPostList("posts/");
    foreach($posts as $post) {
        $html .= genLinkToPostHTML($post, $root_path);
    }

    $html .= '</article></body></html>';
    return $html;
}
?>
