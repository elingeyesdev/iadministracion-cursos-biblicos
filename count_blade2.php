<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));
$count = 0;
foreach ($files as $file) {
    if ($file->isFile()) {
        $ext = $file->getExtension();
        if ($ext === 'blade.php' || $ext === 'blade') {
            $count++;
        }
    }
}
echo 'Total blade files: ' . $count . PHP_EOL;