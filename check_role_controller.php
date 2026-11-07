<?php
$content = file_get_contents('app/Http/Controllers/RoleController.php');
$lines = preg_split('/\r\n|\r|\n/', $content);
for ($i = 105; $i <= 125; $i++) {
    if (isset($lines[$i])) {
        echo $i . ': ' . bin2hex($lines[$i]) . PHP_EOL;
    }
}