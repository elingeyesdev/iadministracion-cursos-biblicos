<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Crear usuario admin y autenticar
$user = \App\Models\User::factory()->create(['email' => 'test_aria@test.com']);
$role = \App\Models\Role::firstOrCreate(
    ['nombre' => 'Administrador'],
    ['estado' => 'Activo', 'nivel_acceso' => 1]
);
$user->roles()->attach($role->id);

$request = \Illuminate\Http\Request::create('/reportes/miembros', 'GET');
$request->setUserResolver(fn () => $user);

$response = $app->handle($request);
$content = $response->getContent();

echo "Status: " . $response->getStatusCode() . PHP_EOL;

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

// Buscar aria-expanded en cualquier parte
if (preg_match('/aria-expanded[^>]*/', $content, $matches)) {
    echo 'aria-expanded found: ' . $matches[0] . PHP_EOL;
} else {
    echo 'aria-expanded NOT found' . PHP_EOL;
}

// Mostrar un fragmento del contenido alrededor de user-menu-wrap
if (preg_match('/user-menu-wrap.{0,500}/s', $content, $matches)) {
    echo 'user-menu-wrap context: ' . $matches[0] . PHP_EOL;
}