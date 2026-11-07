<?php
$content = file_get_contents('resources/views/users/index.blade.php');
echo 'Contains users.show: ' . (strpos($content, "route('users.show'") !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Contains usuarios.show: ' . (strpos($content, "route('usuarios.show'") !== false ? 'YES' : 'NO') . PHP_EOL;