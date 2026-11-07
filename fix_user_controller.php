<?php
$content = file_get_contents('app/Http/Controllers/UserController.php');
$content = str_replace("route('users.index')", "route('usuarios.index')", $content);
file_put_contents('app/Http/Controllers/UserController.php', $content);
echo 'Fixed UserController' . PHP_EOL;