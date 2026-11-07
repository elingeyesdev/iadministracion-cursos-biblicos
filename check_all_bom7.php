<?php

$baseDir = __DIR__ . '/resources/views';

function scanBladeFiles($dir) {
    $files = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $files = array_merge($files, scanBladeFiles($path));
        } elseif (preg_match('/\.blade\.php$/i', $item)) {
            $files[] = $path;
        }
    }
    return $files;
}

$bladeFiles = scanBladeFiles(__DIR__ . '/resources/views');
echo "Total blade files: " . count($bladeFiles) . PHP_EOL;

foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);
    if (strlen($content) >= 2) {
        $bom = substr($content, 0, 2);
        if ($bom === "\xFF\xFE" || $bom === "\xFE\xFF") {
            echo 'UTF-16 BOM: ' . $file . PHP_EOL;
        }
    }
}