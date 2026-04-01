<?php
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once '../NEGOCIO/nFinanza.php';
require_once '../NEGOCIO/nTipoTransaccion.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

$auth = new AuthController();
$auth->requireLogin();

// Verificar privilegios
if (!auth::tienePrivilegio('finanzas_ver')) {
    header('Location: dashboard.php');
    exit();
}

$nFinanza = new nFinanza();
$nTipoTransaccion = new nTipoTransaccion();
$message = '';
$messageType = '';

// Obtener mensaje de URL si existe (después de redirección)
if (isset($_GET['mensaje'])) {
    $message = urldecode($_GET['mensaje']);
    $messageType = $_GET['tipo'] ?? 'success';
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'insert':
                    if (!auth::tienePrivilegio('finanzas_crear')) {
                        header('Location: finanzas.php');
                        exit();
                    }
                    // Mapear campos: descripcion del formulario se usa como concepto
                    $datos = $_POST;
                    $datos['concepto'] = $_POST['descripcion'] ?? '';
                    // Usar directamente el id_tipo_transaccion del formulario
                    $datos['tipo_transaccion_id'] = $_POST['id_tipo_transaccion'] ?? 0;
                    
                    $resultado = $nFinanza->insertar($datos);
                    if ($resultado && isset($resultado['exito']) && $resultado['exito']) {
                        // Redirigir para evitar reenvío del formulario y cerrar el modal
                        header('Location: finanzas.php?mensaje=' . urlencode($resultado['mensaje'] ?? 'Transacción registrada exitosamente') . '&tipo=success');
                        exit;
                    } else {
                        $message = isset($resultado['mensaje']) ? $resultado['mensaje'] : 'Error al registrar la transacción. Verifique que todos los campos obligatorios estén completos.';
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'update':
                    if (!auth::tienePrivilegio('finanzas_editar')) {
                        header('Location: finanzas.php');
                        exit();
                    }
                    // Mapear campos: descripcion del formulario se usa como concepto
                    $datos = $_POST;
                    $datos['concepto'] = $_POST['descripcion'] ?? '';
                    // Usar directamente el id_tipo_transaccion del formulario
                    $datos['tipo_transaccion_id'] = $_POST['id_tipo_transaccion'] ?? 0;
                    
                    $resultado = $nFinanza->actualizar($datos);
                    if ($resultado && isset($resultado['exito']) && $resultado['exito']) {
                        // Redirigir para evitar reenvío del formulario y cerrar el modal
                        header('Location: finanzas.php?mensaje=' . urlencode($resultado['mensaje'] ?? 'Transacción actualizada exitosamente') . '&tipo=success');
                        exit;
                    } else {
                        $message = isset($resultado['mensaje']) ? $resultado['mensaje'] : 'Error al actualizar la transacción. Verifique que todos los campos obligatorios estén completos.';
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'delete':
                    if (!auth::tienePrivilegio('finanzas_eliminar')) {
                        header('Location: finanzas.php');
                        exit();
                    }
                    $resultado = $nFinanza->eliminar($_POST['id']);
                    if ($resultado && isset($resultado['exito']) && $resultado['exito']) {
                        // Redirigir para evitar reenvío del formulario
                        header('Location: finanzas.php?mensaje=' . urlencode($resultado['mensaje'] ?? 'Transacción eliminada exitosamente') . '&tipo=success');
                        exit;
                    } else {
                        $message = isset($resultado['mensaje']) ? $resultado['mensaje'] : 'Error al eliminar la transacción';
                        $messageType = 'danger';
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Obtener listas
$transacciones = $nFinanza->listar();
$tipos_transaccion = $nTipoTransaccion->listar();

// Calcular resumen financiero
$fecha_inicio = date('Y-m-01');
$fecha_fin = date('Y-m-t');
$resumen = $nFinanza->obtenerBalanceMensual($fecha_inicio, $fecha_fin);

// Si el resumen está vacío o devuelve 0, calcular manualmente desde las transacciones
if (empty($resumen) || ($resumen['total_ingresos'] == 0 && $resumen['total_egresos'] == 0)) {
    $ingresosMes = 0;
    $egresosMes = 0;
    
    // Calcular desde las transacciones del mes actual
    foreach ($transacciones as $transaccion) {
        $fecha_trans = strtotime($transaccion['fecha']);
        $fecha_inicio_ts = strtotime($fecha_inicio);
        $fecha_fin_ts = strtotime($fecha_fin);
        
        if ($fecha_trans >= $fecha_inicio_ts && $fecha_trans <= $fecha_fin_ts) {
            if (($transaccion['tipo_movimiento'] ?? '') == 'ingreso') {
                $ingresosMes += (float)($transaccion['monto'] ?? 0);
            } else {
                $egresosMes += (float)($transaccion['monto'] ?? 0);
            }
        }
    }
    
    $balanceMensual = $ingresosMes - $egresosMes;
} else {
    $ingresosMes = (float)($resumen['total_ingresos'] ?? 0);
    $egresosMes = (float)($resumen['total_egresos'] ?? 0);
    $balanceMensual = (float)($resumen['balance'] ?? ($ingresosMes - $egresosMes));
}

// Obtener datos para editar si se solicita
$transaccionEditar = null;
if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $transaccionEditar = $nFinanza->obtenerPorId($_GET['id']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Finanzas - Sistema de Administración de Iglesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 16.666667%; /* col-lg-2 = 2/12 = 16.666667% */
            overflow-y: auto;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        .sidebar .nav {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        @media (max-width: 991.98px) { .sidebar { display: none; } }
        .sidebar-container {
            margin-left: 16.666667%;
        }
        @media (max-width: 991.98px) { .sidebar-container { margin-left: 0; } }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.2);
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .btn-logout {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            border: none;
            border-radius: 8px;
            padding: 8px 20px;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .dropdown-item {
            border-radius: 8px;
            margin: 2px 0;
        }
        .dropdown-item:hover {
            background: #f8f9fa;
        }
        .table-responsive {
            border-radius: 10px;
        }
        .btn-group-sm .btn {
            border-radius: 8px;
            margin: 0 2px;
        }
        .resumen-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .ingresos-card {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
        }
        .egresos-card {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: white;
        }
        .balance-card {
            background: linear-gradient(135deg, #ff8c00 0%, #ffd700 100%);
            color: white;
        }
    </style>
    <?php echo generarEstilosNotificaciones(); ?>
</head>
<body>
    <!-- Offcanvas Menu (móviles) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuFinanzas" aria-labelledby="offcanvasMenuFinanzasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuFinanzasLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('finanzas'); ?>
            </nav>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="d-none d-lg-block col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-church fa-3x text-white mb-2"></i>
                        <h5 class="text-white fw-bold">Iglesia Admin</h5>
                    </div>
                    
                    <nav class="nav flex-column">
                        <?php echo generarMenu('finanzas'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuFinanzas" aria-controls="offcanvasMenuFinanzas">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-dollar-sign text-primary"></i> Gestión de Finanzas
                        </h4>
                        <div class="d-flex align-items-center">
                            <?php echo generarBotonNotificacionesNavbar(); ?>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="container-fluid py-4">
                    <!-- Message Alert -->
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Financial Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card resumen-card text-center">
                                <div class="card-body">
                                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                                    <h5 class="card-title">Resumen Mensual</h5>
                                    <h3 class="mb-0">$<?php echo number_format($balanceMensual, 2); ?></h3>
                                    <small>Balance actual</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card ingresos-card text-center">
                                <div class="card-body">
                                    <i class="fas fa-arrow-up fa-2x mb-2"></i>
                                    <h5 class="card-title">Ingresos del Mes</h5>
                                    <h3 class="mb-0">$<?php echo number_format($ingresosMes, 2); ?></h3>
                                    <small>Total ingresos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card egresos-card text-center">
                                <div class="card-body">
                                    <i class="fas fa-arrow-down fa-2x mb-2"></i>
                                    <h5 class="card-title">Egresos del Mes</h5>
                                    <h3 class="mb-0">$<?php echo number_format($egresosMes, 2); ?></h3>
                                    <small>Total egresos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card balance-card text-center">
                                <div class="card-body">
                                    <i class="fas fa-balance-scale fa-2x mb-2"></i>
                                    <h5 class="card-title">Balance</h5>
                                    <h3 class="mb-0"><?php echo $balanceMensual >= 0 ? '+' : ''; ?>$<?php echo number_format($balanceMensual, 2); ?></h3>
                                    <small><?php echo $balanceMensual >= 0 ? 'Superávit' : 'Déficit'; ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botón de Estadísticas Detalladas -->
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <button type="button" class="btn btn-info btn-sm" id="btnVerEstadisticas">
                                <i class="fas fa-chart-bar"></i> Ver Estadísticas Detalladas
                            </button>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Lista de Transacciones</h5>
                                <?php if (tieneAcceso('finanzas_crear')): ?>
                                <a href="finanzas.php" class="btn btn-primary" id="btnNuevaTransaccion">
                                    <i class="fas fa-plus"></i> Nueva Transacción
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Transactions Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Fecha</th>
                                                    <th>Tipo</th>
                                                    <th>Descripción</th>
                                                    <th>Monto</th>
                                                    <th>Categoría</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($transacciones as $transaccion): ?>
                                                    <tr class="<?php echo ($transaccion['tipo_movimiento'] ?? '') == 'ingreso' ? 'table-success' : 'table-danger'; ?>">
                                                        <td><?php echo $transaccion['id']; ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($transaccion['fecha'])); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo ($transaccion['tipo_movimiento'] ?? '') == 'ingreso' ? 'success' : 'danger'; ?>">
                                                                <?php echo ucfirst($transaccion['tipo_movimiento'] ?? ''); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($transaccion['descripcion']); ?></td>
                                                        <td>$<?php echo number_format($transaccion['monto'], 2); ?></td>
                                                        <td><?php echo htmlspecialchars($transaccion['tipo_transaccion'] ?? ''); ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <?php if (tieneAcceso('finanzas_editar')): ?>
                                                                <a href="finanzas.php?action=editar&id=<?php echo $transaccion['id']; ?>" 
                                                                   class="btn btn-warning" 
                                                                   title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <?php endif; ?>
                                                                <?php if (tieneAcceso('finanzas_eliminar')): ?>
                                                                <button type="button" 
                                                                        class="btn btn-danger" 
                                                                        onclick="confirmarEliminar(<?php echo $transaccion['id']; ?>)"
                                                                        title="Eliminar">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Modal -->
    <div class="modal fade" id="modalEstadisticas" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-chart-bar"></i> Estadísticas Financieras Detalladas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="periodo_estadisticas" class="form-label">Período</label>
                            <select class="form-select" id="periodo_estadisticas" onchange="actualizarSelectoresPeriodo()">
                                <option value="dia">Día</option>
                                <option value="semana">Semana</option>
                                <option value="mes" selected>Mes</option>
                                <option value="año">Año</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="selector_fecha" style="display: none;">
                            <label for="fecha_estadisticas" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha_estadisticas" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-3" id="selector_mes" style="display: block;">
                            <label for="mes_estadisticas" class="form-label">Mes</label>
                            <select class="form-select" id="mes_estadisticas">
                                <?php 
                                $mes_actual = date('m');
                                $meses = [
                                    '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
                                    '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
                                    '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
                                ];
                                foreach ($meses as $num => $nombre): 
                                ?>
                                    <option value="<?php echo $num; ?>" <?php echo $num == $mes_actual ? 'selected' : ''; ?>>
                                        <?php echo $nombre; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3" id="selector_año" style="display: block;">
                            <label for="año_estadisticas" class="form-label">Año</label>
                            <select class="form-select" id="año_estadisticas">
                                <?php 
                                $año_actual = date('Y');
                                for ($i = $año_actual; $i >= $año_actual - 5; $i--): 
                                ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i == $año_actual ? 'selected' : ''; ?>>
                                        <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" onclick="cargarEstadisticas()">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                    
                    <div id="contenido_estadisticas">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando estadísticas...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Modal -->
    <div class="modal fade" id="modalTransaccion" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $transaccionEditar ? 'Editar Transacción' : 'Nueva Transacción'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $transaccionEditar ? 'update' : 'insert'; ?>">
                        <?php if ($transaccionEditar): ?>
                            <input type="hidden" name="id" value="<?php echo $transaccionEditar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="fecha" class="form-label">Fecha *</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" 
                                       value="<?php echo $transaccionEditar ? $transaccionEditar['fecha'] : date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tipo" class="form-label">Tipo *</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="ingreso" <?php echo $transaccionEditar && ($transaccionEditar['tipo_movimiento'] ?? '') == 'ingreso' ? 'selected' : ''; ?>>Ingreso</option>
                                    <option value="egreso" <?php echo $transaccionEditar && ($transaccionEditar['tipo_movimiento'] ?? '') == 'egreso' ? 'selected' : ''; ?>>Egreso</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="monto" class="form-label">Monto *</label>
                                <input type="number" step="0.01" class="form-control" id="monto" name="monto" 
                                       value="<?php echo $transaccionEditar ? $transaccionEditar['monto'] : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="id_tipo_transaccion" class="form-label">Categoría *</label>
                                <select class="form-select" id="id_tipo_transaccion" name="id_tipo_transaccion" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($tipos_transaccion as $tipo): ?>
                                        <option value="<?php echo $tipo['id']; ?>" 
                                                <?php echo $transaccionEditar && ($transaccionEditar['tipo_transaccion_id'] ?? null) == $tipo['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tipo['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="referencia" class="form-label">Referencia</label>
                                <input type="text" class="form-control" id="referencia" name="referencia" 
                                       value="<?php echo $transaccionEditar ? htmlspecialchars($transaccionEditar['referencia']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="descripcion" class="form-label">Descripción *</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?php echo $transaccionEditar ? htmlspecialchars($transaccionEditar['concepto'] ?? $transaccionEditar['descripcion'] ?? '') : ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $transaccionEditar ? 'Actualizar' : 'Guardar'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de que desea eliminar esta transacción?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Función para limpiar y abrir el modal
        function limpiarYAbrirModalTransaccion() {
            const modalTransaccion = document.getElementById('modalTransaccion');
            if (!modalTransaccion) return;
            const form = modalTransaccion.querySelector('form');
            if (!form) return;
            
            ['monto', 'referencia', 'descripcion'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            
            const fechaInput = form.querySelector('#fecha');
            if (fechaInput) fechaInput.value = new Date().toISOString().split('T')[0];
            
            const hiddenId = form.querySelector('input[name="id"]');
            if (hiddenId) hiddenId.remove();
            const hiddenAction = form.querySelector('input[name="action"]');
            if (hiddenAction) hiddenAction.value = 'insert';
            const modalTitle = modalTransaccion.querySelector('.modal-title');
            if (modalTitle) modalTitle.textContent = 'Nueva Transacción';
            const btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                const icon = btnSubmit.querySelector('i');
                if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                else btnSubmit.textContent = 'Guardar';
            }
            const selectTipo = form.querySelector('#tipo');
            if (selectTipo && selectTipo.options.length > 0) {
                Array.from(selectTipo.options).forEach(option => option.selected = false);
                selectTipo.options[0].selected = true;
            }
            const selectCategoria = form.querySelector('#id_tipo_transaccion');
            if (selectCategoria && selectCategoria.options.length > 0) {
                Array.from(selectCategoria.options).forEach(option => option.selected = false);
                selectCategoria.options[0].selected = true;
            }
            
            form.reset();
            
            ['monto', 'referencia', 'descripcion'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            if (fechaInput) fechaInput.value = new Date().toISOString().split('T')[0];
            if (selectTipo && selectTipo.options.length > 0) {
                Array.from(selectTipo.options).forEach(option => option.selected = false);
                selectTipo.options[0].selected = true;
            }
            if (selectCategoria && selectCategoria.options.length > 0) {
                Array.from(selectCategoria.options).forEach(option => option.selected = false);
                selectCategoria.options[0].selected = true;
            }
            
            const modal = new bootstrap.Modal(modalTransaccion);
            modal.show();
        }
        
        const btnNuevaTransaccion = document.getElementById('btnNuevaTransaccion');
        if (btnNuevaTransaccion) {
            btnNuevaTransaccion.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.location.search) window.history.replaceState({}, '', 'finanzas.php');
                limpiarYAbrirModalTransaccion();
            });
        }
        
        const modalTransaccion = document.getElementById('modalTransaccion');
        if (modalTransaccion) {
            modalTransaccion.addEventListener('hidden.bs.modal', function() {
                const form = modalTransaccion.querySelector('form');
                if (form) {
                    form.reset();
                    const hiddenId = form.querySelector('input[name="id"]');
                    if (hiddenId) hiddenId.remove();
                    const hiddenAction = form.querySelector('input[name="action"]');
                    if (hiddenAction) hiddenAction.value = 'insert';
                    const modalTitle = modalTransaccion.querySelector('.modal-title');
                    if (modalTitle) modalTitle.textContent = 'Nueva Transacción';
                    const btnSubmit = form.querySelector('button[type="submit"]');
                    if (btnSubmit) {
                        const icon = btnSubmit.querySelector('i');
                        if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                        else btnSubmit.textContent = 'Guardar';
                    }
                }
            });
        }
        
        // Estadísticas detalladas
        const btnVerEstadisticas = document.getElementById('btnVerEstadisticas');
        if (btnVerEstadisticas) {
            btnVerEstadisticas.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('modalEstadisticas'));
                modal.show();
                // Establecer mes y año actuales
                const ahora = new Date();
                document.getElementById('mes_estadisticas').value = String(ahora.getMonth() + 1).padStart(2, '0');
                document.getElementById('año_estadisticas').value = ahora.getFullYear();
                actualizarSelectoresPeriodo();
                cargarEstadisticas();
            });
        }
        
        function actualizarSelectoresPeriodo() {
            const periodo = document.getElementById('periodo_estadisticas').value;
            const selectorFecha = document.getElementById('selector_fecha');
            const selectorMes = document.getElementById('selector_mes');
            const selectorAño = document.getElementById('selector_año');
            
            // Ocultar todos primero
            selectorFecha.style.display = 'none';
            selectorMes.style.display = 'none';
            selectorAño.style.display = 'none';
            
            // Mostrar según el período
            if (periodo === 'dia' || periodo === 'semana') {
                selectorFecha.style.display = 'block';
                selectorFecha.className = 'col-md-3';
            } else if (periodo === 'mes') {
                selectorMes.style.display = 'block';
                selectorAño.style.display = 'block';
                selectorMes.className = 'col-md-3';
                selectorAño.className = 'col-md-3';
            } else if (periodo === 'año') {
                selectorAño.style.display = 'block';
                selectorAño.className = 'col-md-6'; // Más ancho cuando solo es año
            }
        }
        
        function obtenerFechaSegunPeriodo() {
            const periodo = document.getElementById('periodo_estadisticas').value;
            
            if (periodo === 'dia' || periodo === 'semana') {
                return document.getElementById('fecha_estadisticas').value;
            } else if (periodo === 'mes') {
                const año = document.getElementById('año_estadisticas').value;
                const mes = document.getElementById('mes_estadisticas').value;
                // Usar el día 15 del mes como referencia
                return año + '-' + mes + '-15';
            } else if (periodo === 'año') {
                const año = document.getElementById('año_estadisticas').value;
                // Usar el 1 de enero como referencia
                return año + '-01-01';
            }
            
            return new Date().toISOString().split('T')[0];
        }
        
        function cargarEstadisticas() {
            const periodo = document.getElementById('periodo_estadisticas').value;
            const fecha = obtenerFechaSegunPeriodo();
            const contenido = document.getElementById('contenido_estadisticas');
            
            contenido.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p class="mt-2">Cargando estadísticas...</p></div>';
            
            fetch(`ajax_estadisticas_finanzas.php?periodo=${periodo}&fecha=${fecha}`)
                .then(response => response.json())
                .then(data => {
                    let html = `
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="card ingresos-card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-arrow-up fa-2x mb-2"></i>
                                        <h5 class="card-title">Ingresos</h5>
                                        <h3 class="mb-0">$${parseFloat(data.resumen.total_ingresos).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card egresos-card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-arrow-down fa-2x mb-2"></i>
                                        <h5 class="card-title">Egresos</h5>
                                        <h3 class="mb-0">$${parseFloat(data.resumen.total_egresos).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card balance-card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-balance-scale fa-2x mb-2"></i>
                                        <h5 class="card-title">Balance</h5>
                                        <h3 class="mb-0">${data.resumen.balance >= 0 ? '+' : ''}$${parseFloat(data.resumen.balance).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card resumen-card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-list fa-2x mb-2"></i>
                                        <h5 class="card-title">Transacciones</h5>
                                        <h3 class="mb-0">${data.total_transacciones}</h3>
                                        <small>Total registradas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Resumen por Categoría</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Categoría</th>
                                                        <th class="text-end">Ingresos</th>
                                                        <th class="text-end">Egresos</th>
                                                        <th class="text-end">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;
                    
                    for (const [categoria, datos] of Object.entries(data.por_categoria)) {
                        html += `
                                                    <tr>
                                                        <td><strong>${categoria}</strong></td>
                                                        <td class="text-end text-success">$${parseFloat(datos.ingresos).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                                        <td class="text-end text-danger">$${parseFloat(datos.egresos).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                                        <td class="text-end"><strong>$${parseFloat(datos.total).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                                                    </tr>`;
                    }
                    
                    html += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Resumen por Día (${data.fecha_inicio} - ${data.fecha_fin})</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th class="text-end">Ingresos</th>
                                                        <th class="text-end">Egresos</th>
                                                        <th class="text-end">Balance del Día</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;
                    
                    for (const [dia, datos] of Object.entries(data.por_dia)) {
                        const balanceDia = datos.ingresos - datos.egresos;
                        const fechaFormateada = new Date(dia + 'T00:00:00').toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                        html += `
                                                    <tr>
                                                        <td><strong>${fechaFormateada}</strong></td>
                                                        <td class="text-end text-success">$${parseFloat(datos.ingresos).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                                        <td class="text-end text-danger">$${parseFloat(datos.egresos).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                                        <td class="text-end ${balanceDia >= 0 ? 'text-success' : 'text-danger'}"><strong>${balanceDia >= 0 ? '+' : ''}$${parseFloat(balanceDia).toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                                                    </tr>`;
                    }
                    
                    if (Object.keys(data.por_dia).length === 0) {
                        html += '<tr><td colspan="4" class="text-center text-muted">No hay transacciones en este período</td></tr>';
                    }
                    
                    html += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    
                    contenido.innerHTML = html;
                })
                .catch(error => {
                    contenido.innerHTML = '<div class="alert alert-danger">Error al cargar las estadísticas. Por favor, intente nuevamente.</div>';
                    console.error('Error:', error);
                });
        }
        
        // Auto-abrir modal si hay datos para editar
        <?php if ($transaccionEditar): ?>
            const modal = new bootstrap.Modal(document.getElementById('modalTransaccion'));
            modal.show();
        <?php endif; ?>
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>