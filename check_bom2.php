<?php

$content = file_get_contents('resources/views/dashboard.blade.php');
echo 'First 10 bytes: ' . bin2hex(substr($content, 0, 10)) . PHP_EOL;
echo 'Length: ' . strlen($content) . PHP_EOL;