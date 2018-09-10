#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;

function compilePosts($target, $rootDir, $dist)
{
    $postDir = realpath($rootDir);
    if (!$postDir) {
        exit(1);
    }
    $distDir = realpath($dist);
    if (!$distDir) {
        mkdir($dist);
    }
    $distDir = realpath($dist);
    foreach(scandir($postDir.$target) as $f) {
        if ($f === "." || $f === "..") {
            continue;
        }
        if (is_dir($postDir.$target."/".$f)) {
            if (!file_exists($distDir.$target."/".$f)) {
                mkdir($distDir.$target."/".$f);
            }
            compilePosts($target."/".$f, $rootDir, $distDir);
            continue;
        }
        switch(pathinfo($f, PATHINFO_EXTENSION)) {
            case "md":
                $basename = pathinfo($f, PATHINFO_FILENAME);
                $html = genPostHTML($postDir.$target."/".$f);
                file_put_contents($distDir.$target."/".$basename.".html", $html);
                break;
            default:
                copy($postDir.$target."/".$f, $distDir.$target."/".$f);
                break;
        }
    }
}

function getMetadata(string $mardown): array
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

function getRelativePath(string $fileDir, string $targetPath): string
{
    $targetDir = dirname(realpath($targetPath));
    $relativePath = ltrim(str_replace($targetDir, "", $fileDir), "/");
    $relativePath = preg_replace("/[\w-]+/","..", $relativePath);
    $relativePath = rtrim($relativePath, "/") . "/";
    $relativePath = ltrim($relativePath, "/");
    $relativePath .= basename($targetPath);
    return $relativePath;
}

function searchFile(string $file, string $dir): string
{
    while (!file_exists($dir.$file) && $dir != "/") {
        $dir = preg_replace("/[\w-]+\/?$/", "", $dir);
    }
    if (file_exists($dir.$file)) {
        return $dir.$file;
    } else {
        return "";
    }
}

function locateFile(string $file, string $dir, array $formats = []): string
{
    $dir = rtrim($dir, "/") . "/";
    if (count($formats) === 0) {
        return searchFile($file, $dir);
    }

    foreach ($formats as $ext) {
        $location = searchFile($file.".".$ext, $dir);
        if ($location != "") {
            return $location;
        }
    }

    return "";
}

function genCoverImageHTML(array $metadata): string
{
    $src = $metadata["cover-image"];
    $coverPicture = "";
    $coverPicture .= '<div class="cover"><div class="cover-img" style="background-image:url('.$src.')"></div>';

    // cover credits
    if (isset($metadata["cover-credit-url"]) && isset($metadata["cover-credit-title"])) {
        $coverPicture .= '<a class="credit-badge" href="'.$metadata["cover-credit-url"].'" target="_blank" rel="noopener noreferrer">';
        $coverPicture .= '<span><svg viewBox="0 0 32 32">';
        $coverPicture .= '<path d="M20.8 18.1c0 2.7-2.2 4.8-4.8 4.8s-4.8-2.1-4.8-4.8c0-2.7 2.2-4.8 4.8-4.8 2.7.1 4.8 2.2 4.8 4.8zm11.2-7.4v14.9c0 2.3-1.9 4.3-4.3 4.3h-23.4c-2.4 0-4.3-1.9-4.3-4.3v-15c0-2.3 1.9-4.3 4.3-4.3h3.7l.8-2.3c.4-1.1 1.7-2 2.9-2h8.6c1.2 0 2.5.9 2.9 2l.8 2.4h3.7c2.4 0 4.3 1.9 4.3 4.3zm-8.6 7.5c0-4.1-3.3-7.5-7.5-7.5-4.1 0-7.5 3.4-7.5 7.5s3.3 7.5 7.5 7.5c4.2-.1 7.5-3.4 7.5-7.5z"></path>';
        $coverPicture .= '</svg></span><span>'.$metadata["cover-credit-title"].'</span></a>';
    }

    $coverPicture .= '</div>';
    return $coverPicture;
}

function genMeta(array $metadata): string
{
    $html = "";
    $html .= '<meta charset="utf8">';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
    $html .= '<meta property="og:type" content="article">';

    if (!empty($metadata["title"])) {
        $html .= '<title>'.$metadata["title"].'</title>';
        $html .= '<meta property="og:title" content="'.$metadata["title"].'">';
    }

    if (!empty($metadata["description"])) {
        $html .= '<meta property="og:description" content="'.$metadata["description"].'">';
    }

    if (!empty($metadata["cover-image"])) {
        $html .= '<meta property="og:image" content="'.$metadata["cover-image"].'">';
    }
    return $html;
}

function genPostHTML(string $target): string
{
    $path = realpath($target);
    if (!isset($path) || !file_exists($path)) {
        return "";
    }
    $dir = dirname($path);

    $markdown = file_get_contents($path);
    $metadata = getMetadata($markdown);

    $coverPictureHTML = "";
    if (isset($metadata["cover-image"])) {
        $coverPictureHTML = genCoverImageHTML($metadata);
    }

    $html = "";
    $html .= "<!DOCTYPE html><html><head>";
    $html .= genMeta($metadata);

    $fontFormats = ["woff2", "woff", "ttf", "otf"];

    if (isset($metadata["title-font"])) {
        $font = locateFile($metadata["title-font"], $dir, $fontFormats);
        $html .= '<style>@font-face{font-family:"TitleFont";src:'.'url("'.getRelativePath($dir, $font).'");} h1,h2,h3,h4,h5,h6{font-family: "TitleFont", serif;}</style>';
    }

    if (isset($metadata["text-font"])) {
        $font = locateFile($metadata['text-font'], $dir, $fontFormats);
        $html .= '<style>@font-face{font-family:"TextFont";src:'.'url("'.getRelativePath($dir, $font).'");} body{font-family: "TextFont", sans-serif;}</style>';
    }

    if (isset($metadata["style-file"])) {
        $cssFile = locateFile($metadata["style-file"], $dir);
    } else {
        $cssFile = locateFile("default.css", $dir);
    }

    $html .= '<link rel="stylesheet" href="'.getRelativePath($dir, $cssFile).'"/>';
    $html .= '</head><body>';
    $html .= $coverPictureHTML;
    $html .= '<main><article>';
    $html .= parseStaticMarkDown($markdown);
    $html .= '</article></main>';

    if (isset($metadata["script-file"])) {
        $scriptFile = locateFile($metadata["script-file"], $dir);
    } else {
        $scriptFile = locateFile("default.js", $dir);
    }
    $html .= '<script src="'.getRelativePath($dir, $scriptFile).'"></script>';
    $html .= '</body></html>';
    return $html;
}

function parseStaticMarkDown($markdown)
{
    $config = [
        'renderer' => [
            'block_separator' => "\n",
            'inner_separator' => "\n",
            'soft_break'      => "\n",
        ],
        'enable_em' => true,
        'enable_strong' => true,
        'use_asterisk' => true,
        'use_underscore' => true,
    ];

    $environment = Environment::createCommonMarkEnvironment();
    $environment->setConfig($config);
    $parser = new DocParser($environment);
    $htmlRenderer = new HtmlRenderer($environment);
    $document = $parser->parse($markdown);
    return $htmlRenderer->renderBlock($document);
}

compilePosts("/", $argv[1], $argv[2]);
