<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$request = \Illuminate\Http\Request::create('/reportes/miembros', 'GET');
$response = $app->handle($request);
$content = $response->getContent();

// Buscar el botón del user menu
if (preg_match('/user-menu-toggle[^>]*/', $content, $matches)) {
    echo 'user-menu-toggle button: ' . $matches[0] . PHP_EOL;
}

// Buscar data-sidebar-tooltip
if (preg_match('/data-sidebar-tooltip[^>]*/', $content, $matches)) {
    echo 'data-sidebar-tooltip: ' . $matches[0] . PHP_EOL;
}

// Buscar aria-haspopup
if (preg_match('/aria-haspopup[^>]*/', $content, $matches)) {
    echo 'aria-haspopup: ' . $matches[0] . PHP_EOL;
}

// Buscar x-ref
if (preg_match('/x-ref[^>]*/', $content, $matches)) {
    echo 'x-ref: ' . $matches[0] . PHP_EOL;
}

// Buscar @click
if (preg_match('/@click[^>]*/', $content, $matches)) {
    echo '@click: ' . $matches[0] . PHP_EOL;
}

// Buscar class del botón
if (preg_match('/class="[^"]*user-menu-toggle[^"]*"/', $content, $matches)) {
    echo 'class: ' . $matches[0] . PHP_EOL;
}