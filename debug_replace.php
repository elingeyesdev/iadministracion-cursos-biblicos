<?php
$content = file_get_contents('resources/views/roles/user_roles.blade.php');
echo 'File length: ' . strlen($content) . PHP_EOL;

// Search for the exact bytes
$needle1 = "route('roles.user.edit')";
$needle2 = "route('roles.user.clear')";

$pos1 = strpos($content, $needle1);
$pos2 = strpos($content, $needle2);

echo "Needle1: $needle1" . PHP_EOL;
echo "Needle1 pos: " . ($pos1 !== false ? $pos1 : 'NOT FOUND') . PHP_EOL;

echo "Needle2: $needle2" . PHP_EOL;
echo "Needle2 pos: " . ($pos2 !== false ? $pos2 : 'NOT FOUND') . PHP_EOL;

// Check for the replacement targets
$target1 = "route('asignar-roles.edit')";
$target2 = "route('asignar-roles.destroy')";

echo "Target1 pos: " . (strpos($content, $target1) !== false ? 'FOUND' : 'NOT FOUND') . PHP_EOL;
echo "Target2 pos: " . (strpos($content, $target2) !== false ? 'FOUND' : 'NOT FOUND') . PHP_EOL;