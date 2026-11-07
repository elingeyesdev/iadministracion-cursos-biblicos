<?php
$content = file_get_contents('resources/views/users/index.blade.php');
$content = str_replace("route('users.show'", "route('usuarios.show'", $content);
file_put_contents('resources/views/users/index.blade.php', $content);
echo 'Fixed users/index.blade.php' . PHP_EOL;