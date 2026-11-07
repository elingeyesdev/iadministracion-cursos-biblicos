<?php

$content = file_get_contents('tests/Feature/SidebarNavigationTest.php');
$lines = preg_split('/\r\n|\r|\n/', $content);
for ($i = 90; $i <= 95; $i++) {
    echo $i . ': ' . $lines[$i] . PHP_EOL;
    echo '  bytes: ' . bin2hex($lines[$i]) . PHP_EOL;
}