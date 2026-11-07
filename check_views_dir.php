<?php
$dir = 'resources/views';
echo 'Exists: ' . (is_dir($dir) ? 'yes' : 'no') . PHP_EOL;
if (is_dir($dir)) {
    $items = scandir($dir);
    print_r($items);
}