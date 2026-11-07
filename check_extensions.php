<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));
foreach ($files as $file) {
    if ($file->isFile()) {
        echo $file->getFilename() . ' -> ext: ' . $file->getExtension() . PHP_EOL;
    }
}