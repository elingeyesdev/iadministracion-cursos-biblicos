<?php

$baseDir = __DIR__ . '/resources/views';
echo "Base dir: $baseDir" . PHP_EOL;
echo "Exists: " . (is_dir($baseDir) ? 'yes' : 'no') . PHP_EOL;

$items = scandir($baseDir);
print_r($items);

$item = $baseDir . '/dashboard.blade.php';
echo "Dashboard exists: " . (file_exists($item) ? 'yes' : 'no') . PHP_EOL;
echo "Is file: " . (is_file($item) ? 'yes' : 'no') . PHP_EOL;
echo "Extension: " . pathinfo($item, PATHINFO_EXTENSION) . PHP_EOL;