<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$request = \Illuminate\Http\Request::create('/reportes/miembros', 'GET');
$response = $app->handle($request);
echo 'Status: ' . $response->getStatusCode() . PHP_EOL;
echo 'Content-Type: ' . $response->headers->get('Content-Type') . PHP_EOL;
echo 'Encoding: ' . mb_detect_encoding($response->getContent()) . PHP_EOL;
echo 'First 100 bytes: ' . bin2hex(substr($response->getContent(), 0, 50)) . PHP_EOL;