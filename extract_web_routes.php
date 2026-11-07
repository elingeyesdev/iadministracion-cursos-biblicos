<?php

$content = file_get_contents('routes/web.php');
preg_match_all("/->name\(['\"]([^'\"]+)['\"]\)/", $content, $matches);
$routes = array_unique($matches[1]);
sort($routes);

echo "Total named routes in web.php: " . count($routes) . PHP_EOL;
foreach ($routes as $name) {
    echo $name . PHP_EOL;
}

// Also find resource routes
preg_match_all("/Route::resource\(['\"]([^'\"]+)['\"]/", $content, $matches);
$resources = array_unique($matches[1]);
sort($resources);

echo PHP_EOL . "Resource routes: " . count($resources) . PHP_EOL;
foreach ($resources as $name) {
    echo $name . PHP_EOL;
}