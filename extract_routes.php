<?php

function findBladeFiles($dir) {
    $files = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $fullPath = $dir . '/' . $item;
        if (is_dir($fullPath)) {
            $files = array_merge($files, findBladeFiles($fullPath));
        } elseif (preg_match('/\.blade\.php$/i', $item)) {
            $files[] = $fullPath;
        }
    }
    return $files;
}

$bladeFiles = findBladeFiles('resources/views');
$routeNames = [];

foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);
    preg_match_all('/route\([\'"]([^\'")]+)[\'"]\)/', $content, $matches);
    if (!empty($matches[1])) {
        $routeNames = array_merge($routeNames, $matches[1]);
    }
}

$routeNames = array_unique($routeNames);
sort($routeNames);

echo "Total unique route names in views: " . count($routeNames) . PHP_EOL;
foreach ($routeNames as $name) {
    echo $name . PHP_EOL;
}