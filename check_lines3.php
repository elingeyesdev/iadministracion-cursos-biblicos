<?php

$content = file_get_contents('app/Http/Controllers/UserController.php');
$lines = preg_split('/\r\n|\r|\n/', $content);
for ($i = 80; $i <= 98; $i++) {
    echo $i . ': ' . $lines[$i] . PHP_EOL;
}