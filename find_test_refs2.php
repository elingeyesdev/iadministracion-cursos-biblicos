<?php

function findFiles($dir, $extensions = ['php']) {
    $files = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $files = array_merge($files, findFiles($path, $extensions));
        } elseif (in_array(pathinfo($path, PATHINFO_EXTENSION), $extensions)) {
            $files[] = $path;
        }
    }
    return $files;
}

$searchTerms = [
    'roles.user.edit',
    'roles.user.clear',
];

$allFiles = findFiles('tests', ['php']);

foreach ($allFiles as $file) {
    $content = file_get_contents($file);
    foreach ($searchTerms as $term) {
        if (strpos($content, $term) !== false) {
            echo "Found '$term' in: $file" . PHP_EOL;
        }
    }
}