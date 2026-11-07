<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$request = \Illuminate\Http\Request::create('/reportes/miembros', 'GET');
$response = $app->handle($request);
$content = $response->getContent();

// Buscar aria-expanded
if (preg_match('/aria-expanded[^>]*/', $content, $matches)) {
    echo 'Found aria-expanded: ' . $matches[0] . PHP_EOL;
} else {
    echo 'aria-expanded NOT found in response' . PHP_EOL;
}

// Buscar user-menu-wrap
if (preg_match('/user-menu-wrap[^>]*/', $content, $matches)) {
    echo 'user-menu-wrap: ' . $matches[0] . PHP_EOL;
}

// Buscar el botón del user menu
if (preg_match('/user-menu-toggle[^>]*/', $content, $matches)) {
    echo 'user-menu-toggle: ' . $matches[0] . PHP_EOL;
}