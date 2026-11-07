<?php
$content = file_get_contents('tests/Feature/AuditoriaAccionesTest.php');
$content = str_replace("route('roles.user.save')", "route('asignar-roles.store')", $content);
$content = str_replace("route('roles.user')", "route('asignar-roles.index')", $content);
$content = str_replace("'roles.user.save'", "'asignar-roles.store'", $content);
file_put_contents('tests/Feature/AuditoriaAccionesTest.php', $content);
echo 'Fixed AuditoriaAccionesTest' . PHP_EOL;