<?php
$content = file_get_contents('resources/views/roles/user_roles_edit.blade.php');
$content = str_replace("route('roles.user')", "route('asignar-roles.index')", $content);
$content = str_replace("route('roles.user.save')", "route('asignar-roles.store')", $content);
file_put_contents('resources/views/roles/user_roles_edit.blade.php', $content);
echo 'Fixed user_roles_edit.blade.php' . PHP_EOL;