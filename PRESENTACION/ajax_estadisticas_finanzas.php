<?php
require_once 'auth.php';
require_once '../NEGOCIO/nFinanza.php';
require_once '../DATOS/dFinanza.php';

$auth = new AuthController();
$auth->requireLogin();

header('Content-Type: application/json');

$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'mes';
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

$nFinanza = new nFinanza();

// Calcular fechas según el período
$fecha_inicio = '';
$fecha_fin = '';

switch ($periodo) {
    case 'dia':
        $fecha_inicio = $fecha;
        $fecha_fin = $fecha;
        break;
    case 'semana':
        $timestamp = strtotime($fecha);
        $dia_semana = date('w', $timestamp); // 0 = domingo, 6 = sábado
        $inicio_semana = date('Y-m-d', strtotime('-' . $dia_semana . ' days', $timestamp));
        $fin_semana = date('Y-m-d', strtotime('+' . (6 - $dia_semana) . ' days', $timestamp));
        $fecha_inicio = $inicio_semana;
        $fecha_fin = $fin_semana;
        break;
    case 'mes':
        $fecha_inicio = date('Y-m-01', strtotime($fecha));
        $fecha_fin = date('Y-m-t', strtotime($fecha));
        break;
    case 'año':
        $fecha_inicio = date('Y-01-01', strtotime($fecha));
        $fecha_fin = date('Y-12-31', strtotime($fecha));
        break;
    default:
        $fecha_inicio = date('Y-m-01');
        $fecha_fin = date('Y-m-t');
}

// Obtener balance del período
$resumen = $nFinanza->obtenerBalanceMensual($fecha_inicio, $fecha_fin);

// Obtener transacciones del período
$dFinanza = new dFinanza();
$transacciones = $dFinanza->obtenerReporteTransacciones($fecha_inicio, $fecha_fin);

// Agrupar por día para gráfico
$por_dia = [];
foreach ($transacciones as $trans) {
    $dia = date('Y-m-d', strtotime($trans['fecha']));
    if (!isset($por_dia[$dia])) {
        $por_dia[$dia] = ['ingresos' => 0, 'egresos' => 0];
    }
    if (($trans['tipo'] ?? '') == 'ingreso') {
        $por_dia[$dia]['ingresos'] += (float)($trans['monto'] ?? 0);
    } else {
        $por_dia[$dia]['egresos'] += (float)($trans['monto'] ?? 0);
    }
}

// Agrupar por categoría
$por_categoria = [];
foreach ($transacciones as $trans) {
    $categoria = $trans['tipo_transaccion'] ?? 'Sin categoría';
    if (!isset($por_categoria[$categoria])) {
        $por_categoria[$categoria] = ['ingresos' => 0, 'egresos' => 0, 'total' => 0];
    }
    $monto = (float)($trans['monto'] ?? 0);
    if (($trans['tipo'] ?? '') == 'ingreso') {
        $por_categoria[$categoria]['ingresos'] += $monto;
    } else {
        $por_categoria[$categoria]['egresos'] += $monto;
    }
    $por_categoria[$categoria]['total'] += $monto;
}

// Ordenar por día
ksort($por_dia);

// Ordenar categorías por total descendente
arsort($por_categoria);

echo json_encode([
    'periodo' => $periodo,
    'fecha_inicio' => $fecha_inicio,
    'fecha_fin' => $fecha_fin,
    'resumen' => $resumen,
    'total_transacciones' => count($transacciones),
    'por_dia' => $por_dia,
    'por_categoria' => $por_categoria,
    'transacciones' => array_slice($transacciones, 0, 50) // Limitar a 50 para no sobrecargar
]);
?>

