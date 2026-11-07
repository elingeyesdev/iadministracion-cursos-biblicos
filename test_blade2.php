<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$compiler = $app->make('Illuminate\View\Compilers\BladeCompiler');

// Verificar directivas registradas
$reflection = new ReflectionClass($compiler);
$property = $reflection->getProperty('directives');
$property->setAccessible(true);
$directives = $property->getValue($compiler);

echo 'Directivas registradas: ' . count($directives) . PHP_EOL;
foreach (array_keys($directives) as $directive) {
    echo "  @$directive" . PHP_EOL;
}