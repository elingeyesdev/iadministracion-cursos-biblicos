<?php

$content = file_get_contents('app/Http/Controllers/UserController.php');
$lines = preg_split('/\r\n|\r|\n/', $content);
echo "Total lines: " . count($lines) . PHP_EOL;
for ($i = 45; $i <= min(55, count($lines)-1); $i++) {
    echo $i . ': ' . $lines[$i] . PHP_EOL;
}