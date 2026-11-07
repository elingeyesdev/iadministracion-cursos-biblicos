<?php
$content = file_get_contents('tests/Feature/RestauracionUnicosTest.php');
$content = str_replace("route('roles.user.save')", "route('asignar-roles.store')", $content);
$content = str_replace("route('roles.user')", "route('asignar-roles.index')", $content);
file_put_contents('tests/Feature/RestauracionUnicosTest.php', $content);
echo 'Fixed RestauracionUnicosTest' . PHP_EOL;