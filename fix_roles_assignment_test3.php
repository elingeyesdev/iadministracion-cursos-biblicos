<?php
$content = file_get_contents('tests/Feature/RolesAssignmentTest.php');
$content = str_replace("route('roles.user.edit'", "route('asignar-roles.edit'", $content);
$content = str_replace("route('roles.user.clear'", "route('asignar-roles.destroy'", $content);
file_put_contents('tests/Feature/RolesAssignmentTest.php', $content);
echo 'Fixed RolesAssignmentTest' . PHP_EOL;