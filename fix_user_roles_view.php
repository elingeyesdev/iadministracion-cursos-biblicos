<?php
$content = file_get_contents('resources/views/roles/user_roles.blade.php');
$content = str_replace("route('roles.user.edit')", "route('asignar-roles.edit')", $content);
$content = str_replace("route('roles.user.clear')", "route('asignar-roles.destroy')", $content);
file_put_contents('resources/views/roles/user_roles.blade.php', $content);
echo 'Fixed user_roles.blade.php' . PHP_EOL;