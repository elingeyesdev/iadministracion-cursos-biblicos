<?php

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views'));
$count = 0;
foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'blade.php') {
        $count++;
        $content = file_get_contents($file->getPathname());
        if (strlen($content) >= 2) {
            $bom = substr($content, 0, 2);
            if ($bom === "\xFF\xFE" || $bom === "\xFE\xFF") {
                echo 'UTF-16 BOM: ' . $file->getPathname() . PHP_EOL;
            }
        }
    }
}
echo "Total files checked: $count" . PHP_EOL;