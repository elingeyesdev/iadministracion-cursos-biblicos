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
    'roles.user.save',
    'users.index',
    'roles.user\b',
    'roles.user.edit',
    'roles.user.clear',
];

$allFiles = findFiles('app', ['php']);
$allFiles = array_merge($allFiles, findFiles('tests', ['php']));
$allFiles = array_merge($allFiles, findFiles('resources/views', ['blade.php']));

foreach ($allFiles as $file) {
    $content = file_get_contents($file);
    foreach ($searchTerms as $term) {
        if (preg_match('/' . $term . '/', $content)) {
            echo "Found '$term' in: $file" . PHP_EOL;
        }
    }
}