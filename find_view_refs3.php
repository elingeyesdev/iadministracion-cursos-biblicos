<?php

function findFiles($dir, $extensions = ['blade.php']) {
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

$allFiles = findFiles('resources/views', ['blade.php']);

foreach ($allFiles as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'users.show') !== false) {
        echo "Found 'users.show' in: $file" . PHP_EOL;
    }
    if (strpos($content, 'roles.user.edit') !== false) {
        echo "Found 'roles.user.edit' in: $file" . PHP_EOL;
    }
    if (strpos($content, 'roles.user.clear') !== false) {
        echo "Found 'roles.user.clear' in: $file" . PHP_EOL;
    }
    if (strpos($content, 'roles.user') !== false && strpos($content, 'roles.user.edit') === false && strpos($content, 'roles.user.clear') === false) {
        echo "Found 'roles.user' (bare) in: $file" . PHP_EOL;
    }
}