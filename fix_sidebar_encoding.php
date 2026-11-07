<?php
$content = file_get_contents('resources/views/layouts/sidebar.blade.php');
$bad = 'Cerrar sesi' . chr(0xE2) . chr(0x94) . chr(0x9C) . chr(0xE2) . chr(0x94) . chr(0x82) . 'n';
$good = 'Cerrar sesión';
$fixed = str_replace($bad, $good, $content);
file_put_contents('resources/views/layouts/sidebar.blade.php', $fixed);
echo 'Fixed' . PHP_EOL;