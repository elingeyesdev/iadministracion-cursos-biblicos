<?php

require_once 'auth.php';
require_once '../NEGOCIO/nMiembro.php';

$auth = new AuthController();
$auth->requireLogin();

header('Content-Type: application/json');

$termino = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($termino) || strlen($termino) < 2) {
    echo json_encode([]);
    exit;
}

$nMiembro = new nMiembro();
$miembros = $nMiembro->buscarPorNombre($termino);

// Limitar a 10 resultados
$miembros = array_slice($miembros, 0, 10);

$resultados = [];
foreach ($miembros as $miembro) {
    $resultados[] = [
        'id' => $miembro['id'],
        'nombre' => ($miembro['nombres'] ?? '') . ' ' . ($miembro['apellidos'] ?? ''),
        'nombres' => $miembro['nombres'] ?? '',
        'apellidos' => $miembro['apellidos'] ?? '',
        'email' => $miembro['email'] ?? '',
        'telefono' => $miembro['telefono'] ?? '',
        'direccion' => $miembro['direccion'] ?? '',
        'fecha_nacimiento' => $miembro['fecha_nacimiento'] ?? ''
    ];
}

echo json_encode($resultados);
?>

