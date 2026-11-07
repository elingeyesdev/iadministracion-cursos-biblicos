<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));
$count = 0;
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'blade.php') {
        $count++;
    }
}
echo 'Total blade files: ' . $count . PHP_EOL;