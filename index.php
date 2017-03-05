<?php

require __DIR__ . '/vendor/autoload.php';

echo('<!DOCTYPE html><html><head>');

echo('<meta charset="utf8">');

echo('<style>');
echo(file_get_contents("style.css"));
echo('</style>');

echo('</head><body><article>');

use League\CommonMark\CommonMarkConverter;
$converter = new CommonMarkConverter();
$p = $converter->convertToHtml(file_get_contents("2017.md"));
echo($p);

echo('<script>');
echo(file_get_contents("script.js"));
echo('</script>');
echo('</article></body></html>');

?>
