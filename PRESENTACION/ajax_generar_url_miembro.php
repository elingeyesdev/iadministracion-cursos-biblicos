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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dSolicitud = new dSolicitudMiembro();
    
    // Crear una solicitud vacía con token único
    $resultado = $dSolicitud->crear('', '', null, '', '', '', '', '', '');
    
    if ($resultado['exito']) {
        $token = $resultado['token'];
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
                   '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
        $url = $baseUrl . '/formulario_miembro.php?token=' . $token;
        
        // URL para WhatsApp
        $mensajeWhatsApp = urlencode("Hola, te invito a completar el formulario de registro como miembro: " . $url);
        $urlWhatsApp = "https://wa.me/?text=" . $mensajeWhatsApp;
        
        echo json_encode([
            'exito' => true,
            'url' => $url,
            'urlWhatsApp' => $urlWhatsApp,
            'token' => $token
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['exito' => false, 'mensaje' => 'Error al generar la URL']);
    }
} else {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
}

