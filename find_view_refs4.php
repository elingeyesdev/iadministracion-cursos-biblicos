<?php

function findBladeFiles($dir) {
    $files = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $files = array_merge($files, findBladeFiles($path));
        } elseif (preg_match('/\.blade\.php$/i', $item)) {
            $files[] = $path;
        }
    }
    return $files;
}

$allFiles = findBladeFiles('resources/views');
echo 'Total blade files: ' . count($allFiles) . PHP_EOL;

$searchTerms = [
    'users.show',
    'roles.user.edit',
    'roles.user.clear',
];

foreach ($allFiles as $file) {
    $content = file_get_contents($file);
    foreach ($searchTerms as $term) {
        if (strpos($content, $term) !== false) {
            echo "Found '$term' in: $file" . PHP_EOL;
        }
    }
}