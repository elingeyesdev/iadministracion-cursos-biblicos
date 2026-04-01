<?php
require_once 'auth.php';
require_once '../NEGOCIO/nAsistencia.php';

$auth = new AuthController();
$auth->requireLogin();

header('Content-Type: application/json');

$estudiante_id = isset($_GET['estudiante_id']) ? (int)$_GET['estudiante_id'] : 0;
$curso_id = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : 0;

if (empty($estudiante_id) || empty($curso_id)) {
    echo json_encode([]);
    exit;
}

$nAsistencia = new nAsistencia();
$registros = $nAsistencia->obtenerEstadisticasDetalladasPorEstudiante($estudiante_id, $curso_id);

// Formatear fechas
foreach ($registros as &$registro) {
    if (isset($registro['fecha'])) {
        $registro['fecha'] = date('d/m/Y', strtotime($registro['fecha']));
    }
}

echo json_encode($registros);
?>

