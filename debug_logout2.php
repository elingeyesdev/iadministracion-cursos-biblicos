<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Use the app directly to make a request
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::create('/reportes/miembros', 'GET');

// We need to authenticate as admin first
$role = \App\Models\Role::firstOrCreate(['nombre' => 'Administrador'], ['estado' => 'Activo', 'nivel_acceso' => 1]);
$admin = \App\Models\User::factory()->create(['email' => 'debug_test2@test.com']);
$admin->roles()->attach($role->id);

// Use the test client approach
$client = $app->make(\Illuminate\Testing\TestResponse::class);
// Actually let's just use the app directly with a request that has the user set
$request->setUserResolver(fn () => $admin);

$response = $kernel->handle($request);
$content = $response->getContent();

// Search for the logout text
if (preg_match('/Cerrar sesi[^<]*/i', $content, $matches)) {
    echo 'Found: ' . $matches[0] . PHP_EOL;
    echo 'Hex: ' . bin2hex($matches[0]) . PHP_EOL;
} else {
    echo 'Not found' . PHP_EOL;
    // Show a snippet around user-menu
    if (preg_match('/user-menu[^<]{0,200}/i', $content, $matches)) {
        echo 'user-menu context: ' . $matches[0] . PHP_EOL;
    }
}