<?php
$content = file_get_contents('tests/Feature/DocenteAsistenciaTest.php');
$content = str_replace("route('users.index')", "route('usuarios.index')", $content);
file_put_contents('tests/Feature/DocenteAsistenciaTest.php', $content);
echo 'Fixed DocenteAsistenciaTest' . PHP_EOL;