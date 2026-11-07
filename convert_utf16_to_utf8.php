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

$converted = 0;
$errors = 0;

foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);
    if (strlen($content) >= 2) {
        $bom = substr($content, 0, 2);
        if ($bom === "\xFF\xFE" || $bom === "\xFE\xFF") {
            echo "Converting: $file" . PHP_EOL;
            // Convert from UTF-16LE to UTF-8
            $utf8Content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
            if ($utf8Content === false) {
                echo "ERROR converting: $file" . PHP_EOL;
                $errors++;
                continue;
            }
            // Write back as UTF-8
            if (file_put_contents($file, $utf8Content) === false) {
                echo "ERROR writing: $file" . PHP_EOL;
                $errors++;
                continue;
            }
            $converted++;
        }
    }
}

echo "Converted: $converted files" . PHP_EOL;
echo "Errors: $errors" . PHP_EOL;