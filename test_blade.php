<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$compiler = $app->make('Illuminate\View\Compilers\BladeCompiler');
echo 'Blade compiler class: ' . get_class($compiler) . PHP_EOL;

$source = file_get_contents('resources/views/dashboard.blade.php');
echo 'Source length: ' . strlen($source) . PHP_EOL;
echo 'Source first 200 chars: ' . substr($source, 0, 200) . PHP_EOL;

$compiled = $compiler->compileString($source);
echo 'Compiled length: ' . strlen($compiled) . PHP_EOL;
echo 'Compiled first 500 chars: ' . substr($compiled, 0, 500) . PHP_EOL;