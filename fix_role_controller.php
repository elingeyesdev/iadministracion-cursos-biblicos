<?php
$content = file_get_contents('app/Http/Controllers/RoleController.php');
$content = str_replace("route('roles.user')", "route('asignar-roles.index')", $content);
file_put_contents('app/Http/Controllers/RoleController.php', $content);
echo 'Fixed RoleController' . PHP_EOL;