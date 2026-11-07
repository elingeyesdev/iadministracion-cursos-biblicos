<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$factory = $app->make('Illuminate\View\Factory');
echo 'View factory class: ' . get_class($factory) . PHP_EOL;

$engine = $factory->getEngine('blade');
echo 'Blade engine class: ' . get_class($engine) . PHP_EOL;

$compiler = $engine->getCompiler();
echo 'Compiler class: ' . get_class($compiler) . PHP_EOL;