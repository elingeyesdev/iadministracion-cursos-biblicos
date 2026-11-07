<?php
$content = file_get_contents('resources/views/layouts/sidebar.blade.php');
if (preg_match('/Cerrar sesi[^<]*/i', $content, $matches)) {
    echo 'Found in source: ' . $matches[0] . PHP_EOL;
    echo 'Hex: ' . bin2hex($matches[0]) . PHP_EOL;
}