<?php

$content = file_get_contents('app/Http/Controllers/UserController.php');
$lines = explode(PHP_EOL, $content);
for ($i = 47; $i <= 55; $i++) {
    echo $i . ': ' . bin2hex($lines[$i]) . PHP_EOL;
}