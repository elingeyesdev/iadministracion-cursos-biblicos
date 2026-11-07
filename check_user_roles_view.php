<?php
$content = file_get_contents('resources/views/roles/user_roles.blade.php');
echo 'Contains roles.user.edit: ' . (strpos($content, 'roles.user.edit') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Contains roles.user.clear: ' . (strpos($content, 'roles.user.clear') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Contains asignar-roles.edit: ' . (strpos($content, 'asignar-roles.edit') !== false ? 'YES' : 'NO') . PHP_EOL;
echo 'Contains asignar-roles.destroy: ' . (strpos($content, 'asignar-roles.destroy') !== false ? 'YES' : 'NO') . PHP_EOL;