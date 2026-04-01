<?php
require_once '../DATOS/dSolicitudEstudiante.php';

$mensaje = '';
$tipoMensaje = '';
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    $mensaje = 'Token inválido. El enlace no es válido.';
    $tipoMensaje = 'danger';
} else {
    $dSolicitud = new dSolicitudEstudiante();
    $solicitud = $dSolicitud->obtenerPorToken($token);
    
    if (!$solicitud) {
        $mensaje = 'Token no encontrado. El enlace no es válido.';
        $tipoMensaje = 'danger';
    } elseif ($solicitud['usado'] == 1) {
        $mensaje = 'Este formulario ya fue utilizado. Cada enlace solo puede usarse una vez.';
        $tipoMensaje = 'warning';
    }
}

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($token)) {
    if (!isset($solicitud)) {
        $dSolicitud = new dSolicitudEstudiante();
        $solicitud = $dSolicitud->obtenerPorToken($token);
    }
    
    if ($solicitud && $solicitud['usado'] == 0) {
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        
        if (empty($nombres) || empty($apellidos)) {
            $mensaje = 'Los campos Nombres y Apellidos son obligatorios.';
            $tipoMensaje = 'danger';
        } else {
            $resultado = $dSolicitud->actualizarDatos($token, $nombres, $apellidos, $fecha_nacimiento, $telefono, $email, $direccion);
            
            if ($resultado) {
                $mensaje = '¡Formulario enviado exitosamente! Su solicitud será revisada por el administrador.';
                $tipoMensaje = 'success';
                $solicitud['usado'] = 1; // Marcar como usado para no permitir edición
            } else {
                $mensaje = 'Error al enviar el formulario. Por favor, intente nuevamente.';
                $tipoMensaje = 'danger';
            }
        }
    } else {
        $mensaje = 'Este formulario ya fue utilizado.';
        $tipoMensaje = 'warning';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Registro de Estudiante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-header i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        .form-header h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .form-header p {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <i class="fas fa-user-graduate"></i>
            <h2>Registro de Estudiante</h2>
            <p>Complete el siguiente formulario para registrarse como estudiante</p>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($token) && isset($solicitud) && $solicitud['usado'] == 0): ?>
            <form method="POST" id="formEstudiante">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombres" class="form-label">Nombres *</label>
                        <input type="text" class="form-control" id="nombres" name="nombres" 
                               value="<?php echo htmlspecialchars($solicitud['nombres'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="apellidos" class="form-label">Apellidos *</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" 
                               value="<?php echo htmlspecialchars($solicitud['apellidos'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                               value="<?php echo htmlspecialchars($solicitud['fecha_nacimiento'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" 
                               value="<?php echo htmlspecialchars($solicitud['telefono'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($solicitud['email'] ?? ''); ?>">
                </div>
                
                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion" 
                           value="<?php echo htmlspecialchars($solicitud['direccion'] ?? ''); ?>">
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i> Enviar Solicitud
                    </button>
                </div>
            </form>
        <?php elseif (!empty($token) && isset($solicitud) && $solicitud['usado'] == 1): ?>
            <div class="text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p class="lead">Este formulario ya fue enviado exitosamente.</p>
                <p class="text-muted">Su solicitud está siendo revisada por el administrador.</p>
            </div>
        <?php else: ?>
            <div class="text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <p class="lead">Enlace inválido</p>
                <p class="text-muted">El enlace proporcionado no es válido o ha expirado.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

