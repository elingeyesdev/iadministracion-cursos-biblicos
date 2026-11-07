<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$factory = $app->make('Illuminate\View\Factory');
echo 'View factory class: ' . get_class($factory) . PHP_EOL;

$engine = $factory->getEngineFromPath('resources/views/dashboard.blade.php');
echo 'Blade engine class: ' . get_class($engine) . PHP_EOL;

$compiler = $engine->getCompiler();
echo 'Compiler class: ' . get_class($compiler) . PHP_EOL;

$source = file_get_contents('resources/views/dashboard.blade.php');
echo 'Source length: ' . strlen($source) . PHP_EOL;
echo 'Source first 200 chars: ' . substr($source, 0, 200) . PHP_EOL;

$compiled = $compiler->compileString($source);
echo 'Compiled length: ' . strlen($compiled) . PHP_EOL;
echo 'Compiled first 500 chars: ' . substr($compiled, 0, 500) . PHP_EOL;