<?php
$content = file_get_contents('resources/views/roles/user_roles.blade.php');
echo 'roles.user.edit: ' . (strpos($content, 'roles.user.edit') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'roles.user.clear: ' . (strpos($content, 'roles.user.clear') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'asignar-roles.edit: ' . (strpos($content, 'asignar-roles.edit') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'asignar-roles.destroy: ' . (strpos($content, 'asignar-roles.destroy') !== false ? 'YES' : 'NO') . PHP_EOL;