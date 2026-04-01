<?php
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once '../DATOS/dSolicitudMiembro.php';

$auth = new AuthController();
$auth->requireLogin();

// Verificar privilegios
if (!auth::tienePrivilegio('miembros_crear')) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No tiene permisos para realizar esta acción']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $dSolicitud = new dSolicitudMiembro();
    
    if (isset($_GET['accion'])) {
        switch ($_GET['accion']) {
            case 'obtener':
                $solicitudes = $dSolicitud->obtenerPendientes();
                echo json_encode(['exito' => true, 'solicitudes' => $solicitudes]);
                break;
                
            case 'contar':
                $total = $dSolicitud->contarPendientes();
                echo json_encode(['exito' => true, 'total' => $total]);
                break;
                
            case 'obtener_por_id':
                if (!isset($_GET['id'])) {
                    http_response_code(400);
                    echo json_encode(['exito' => false, 'mensaje' => 'ID requerido']);
                    exit();
                }
                $solicitud = $dSolicitud->obtenerPorId((int)$_GET['id']);
                if ($solicitud) {
                    echo json_encode(['exito' => true, 'solicitud' => $solicitud]);
                } else {
                    http_response_code(404);
                    echo json_encode(['exito' => false, 'mensaje' => 'Solicitud no encontrada']);
                }
                break;
                
            case 'marcar_procesado':
                if (!isset($_GET['id'])) {
                    http_response_code(400);
                    echo json_encode(['exito' => false, 'mensaje' => 'ID requerido']);
                    exit();
                }
                $resultado = $dSolicitud->marcarComoProcesado((int)$_GET['id']);
                if ($resultado) {
                    echo json_encode(['exito' => true, 'mensaje' => 'Solicitud marcada como procesada']);
                } else {
                    http_response_code(500);
                    echo json_encode(['exito' => false, 'mensaje' => 'Error al marcar la solicitud']);
                }
                break;
                
            case 'rechazar':
                if (!isset($_GET['id'])) {
                    http_response_code(400);
                    echo json_encode(['exito' => false, 'mensaje' => 'ID requerido']);
                    exit();
                }
                $solicitud = $dSolicitud->obtenerPorId((int)$_GET['id']);
                if (!$solicitud) {
                    http_response_code(404);
                    echo json_encode(['exito' => false, 'mensaje' => 'Solicitud no encontrada']);
                    exit();
                }
                
                // Marcar como procesado (rechazado)
                $resultado = $dSolicitud->marcarComoProcesado((int)$_GET['id']);
                if ($resultado) {
                    echo json_encode([
                        'exito' => true, 
                        'mensaje' => 'Solicitud rechazada.',
                        'email' => $solicitud['email']
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['exito' => false, 'mensaje' => 'Error al rechazar la solicitud']);
                }
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Acción requerida']);
    }
} else {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
}

