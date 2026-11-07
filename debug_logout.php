<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$testCase = new class($app) extends \Tests\TestCase {
    public function __construct($app) {
        $this->app = $app;
        $this->setUp();
    }
};

$role = \App\Models\Role::firstOrCreate(['nombre' => 'Administrador'], ['estado' => 'Activo', 'nivel_acceso' => 1]);
$admin = \App\Models\User::factory()->create(['email' => 'debug_test@test.com']);
$admin->roles()->attach($role->id);

$response = $testCase->actingAs($admin)->get('/reportes/miembros');
$content = $response->getContent();

// Search for the logout text
if (preg_match('/Cerrar sesi[^<]*/i', $content, $matches)) {
    echo 'Found: ' . $matches[0] . PHP_EOL;
    echo 'Hex: ' . bin2hex($matches[0]) . PHP_EOL;
} else {
    echo 'Not found' . PHP_EOL;
}