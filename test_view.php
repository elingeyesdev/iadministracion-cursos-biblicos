<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$factory = $app->make('Illuminate\View\Factory');
$view = $factory->make('dashboard');
echo 'View class: ' . get_class($view) . PHP_EOL;
echo 'View path: ' . $view->getPath() . PHP_EOL;

$rendered = $view->render();
echo 'Rendered length: ' . strlen($rendered) . PHP_EOL;
echo 'First 500 chars: ' . substr($rendered, 0, 500) . PHP_EOL;