<?php

require_once '../NEGOCIO/nMiembro.php';
require_once '../NEGOCIO/nEvento.php';
require_once '../NEGOCIO/nFinanza.php';
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

// Headers para prevenir cacheo en páginas protegidas
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$auth = new AuthController();
$auth->requireLogin();
$currentUser = $auth->getCurrentUser();

// Obtener datos para el dashboard
$nMiembro = new nMiembro();
$nEvento = new nEvento();
$nFinanza = new nFinanza();

$totalMiembros = 0;
$totalEventos = 0;
$balanceMensual = ['total_ingresos' => 0, 'total_egresos' => 0, 'balance' => 0];

if (tieneAcceso('miembros_ver')) {
    $totalMiembros = count($nMiembro->listar());
}

if (tieneAcceso('eventos_ver')) {
    $totalEventos = count($nEvento->listar());
}

if (tieneAcceso('finanzas_ver')) {
    // Obtener finanzas del mes actual
    $fecha_inicio = date('Y-m-01');
    $fecha_fin = date('Y-m-t');
    $balanceMensual = $nFinanza->obtenerBalanceMensual($fecha_inicio, $fecha_fin);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Administración de Iglesia</title>
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
        .card-stats {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
        .card-stats .card-body {
            padding: 2rem;
        }
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
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
        .quick-action-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .quick-action-card:hover {
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0,123,255,0.15);
            transform: translateY(-3px);
        }
        .quick-action-card .btn {
            margin-top: 0.5rem;
        }
        .quick-action-card .description {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        .text-purple {
            color: #6f42c1 !important;
        }
    </style>
    <?php echo generarEstilosNotificaciones(); ?>
</head>
<body>
    <!-- Offcanvas Menu (móviles) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuDashboard" aria-labelledby="offcanvasMenuDashboardLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuDashboardLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('dashboard'); ?>
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
                        <?php echo generarMenu('dashboard'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuDashboard" aria-controls="offcanvasMenuDashboard">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-tachometer-alt text-primary"></i> Dashboard
                        </h4>
                        <div class="d-flex align-items-center">
                            <?php echo generarBotonNotificacionesNavbar(); ?>
                        </div>
                    </div>
                </nav>
                
                <!-- Dashboard Content -->
                <div class="container-fluid py-4">
                    <!-- Debug Info (temporal) -->
                    <?php if (isset($_GET['debug'])): ?>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-warning">
                                    <h5 class="mb-0">Debug - Información de Privilegios</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Usuario ID:</strong> <?php echo $currentUser['id']; ?></p>
                                    <p><strong>Usuario:</strong> <?php echo htmlspecialchars($currentUser['username']); ?></p>
                                    <p><strong>Rol:</strong> <?php echo htmlspecialchars($currentUser['rol'] ?? 'N/A'); ?></p>
                                    <hr>
                                    <p><strong>Privilegios en Sesión:</strong></p>
                                    <ul>
                                        <?php 
                                        $privilegiosSesion = $_SESSION['usuario_privilegios'] ?? [];
                                        if (empty($privilegiosSesion)) {
                                            echo '<li class="text-danger">No hay privilegios en la sesión</li>';
                                        } else {
                                            foreach ($privilegiosSesion as $priv) {
                                                echo '<li>' . htmlspecialchars($priv) . '</li>';
                                            }
                                        }
                                        ?>
                                    </ul>
                                    <hr>
                                    <p><strong>Verificaciones de Acceso:</strong></p>
                                    <ul>
                                        <li>miembros_ver: <?php echo tieneAcceso('miembros_ver') ? '✅ SÍ' : '❌ NO'; ?></li>
                                        <li>eventos_ver: <?php echo tieneAcceso('eventos_ver') ? '✅ SÍ' : '❌ NO'; ?></li>
                                        <li>finanzas_ver: <?php echo tieneAcceso('finanzas_ver') ? '✅ SÍ' : '❌ NO'; ?></li>
                                        <li>cursos_ver: <?php echo tieneAcceso('cursos_ver') ? '✅ SÍ' : '❌ NO'; ?></li>
                                        <li>usuarios_ver: <?php echo tieneAcceso('usuarios_ver') ? '✅ SÍ' : '❌ NO'; ?></li>
                                        <li>roles_ver: <?php echo tieneAcceso('roles_ver') ? '✅ SÍ' : '❌ NO'; ?></li>
                                        <li>reportes_ver: <?php echo tieneAcceso('reportes_ver') ? '✅ SÍ' : '❌ NO'; ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <?php if (tieneAcceso('miembros_ver')): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title text-muted mb-1">Total Miembros</h5>
                                            <h2 class="mb-0 fw-bold text-primary"><?php echo $totalMiembros; ?></h2>
                                        </div>
                                        <div class="stats-icon bg-primary">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (tieneAcceso('eventos_ver')): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title text-muted mb-1">Eventos Activos</h5>
                                            <h2 class="mb-0 fw-bold text-success"><?php echo $totalEventos; ?></h2>
                                        </div>
                                        <div class="stats-icon bg-success">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (tieneAcceso('finanzas_ver')): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title text-muted mb-1">Ingresos Mes</h5>
                                            <h2 class="mb-0 fw-bold text-info">$<?php echo number_format($balanceMensual['total_ingresos'], 2); ?></h2>
                                        </div>
                                        <div class="stats-icon bg-info">
                                            <i class="fas fa-arrow-up"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="card-title text-muted mb-1">Balance Mensual</h5>
                                            <h2 class="mb-0 fw-bold <?php echo $balanceMensual['balance'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                $<?php echo number_format($balanceMensual['balance'], 2); ?>
                                            </h2>
                                        </div>
                                        <div class="stats-icon <?php echo $balanceMensual['balance'] >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                                            <i class="fas fa-balance-scale"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Actions -->
                    <?php 
                    $tieneParametrizacion = tieneAcceso('miembros_crear') || tieneAcceso('ministerios_crear') || tieneAcceso('celulas_crear') || 
                                           tieneAcceso('eventos_crear') || tieneAcceso('finanzas_crear') || 
                                           tieneAcceso('docentes_crear') || tieneAcceso('cursos_crear') || tieneAcceso('estudiantes_crear');
                    $tieneAccesoYSeguridad = tieneAcceso('usuarios_crear') || tieneAcceso('roles_crear') || tieneAcceso('usuarios_editar');
                    $tieneTransaccional = tieneAcceso('asistencias_crear');
                    $tieneReportes = tieneAcceso('reportes_ver');
                    
                    if ($tieneParametrizacion || $tieneAccesoYSeguridad || $tieneTransaccional || $tieneReportes): 
                    ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-bolt"></i> Acciones Rápidas
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($tieneAccesoYSeguridad): ?>
                                    <!-- Acceso y Seguridad -->
                                    <h6 class="text-muted text-uppercase fw-bold mb-3">
                                        <i class="fas fa-shield-alt"></i> Acceso y Seguridad
                                    </h6>
                                    <div class="row mb-4">
                                        <?php if (tieneAcceso('usuarios_crear')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-user-cog fa-2x text-info mb-2"></i>
                                                    <h6 class="mb-2">Nuevo Usuario</h6>
                                                    <p class="description">Crear un nuevo usuario del sistema</p>
                                                </div>
                                                <a href="usuarios.php?tab=usuarios&action=nuevo" class="btn btn-outline-info btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (tieneAcceso('roles_crear')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-user-tag fa-2x text-warning mb-2"></i>
                                                    <h6 class="mb-2">Nuevo Rol</h6>
                                                    <p class="description">Crear un nuevo rol del sistema</p>
                                                </div>
                                                <a href="roles.php?action=nuevo" class="btn btn-outline-warning btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (tieneAcceso('usuarios_editar')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-user-shield fa-2x text-primary mb-2"></i>
                                                    <h6 class="mb-2">Asignar Roles</h6>
                                                    <p class="description">Asignar roles a usuarios</p>
                                                </div>
                                                <a href="usuarios_roles.php" class="btn btn-outline-primary btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($tieneParametrizacion): ?>
                                    <!-- Parametrización -->
                                    <h6 class="text-muted text-uppercase fw-bold mb-3">
                                        <i class="fas fa-cog"></i> Parametrización
                                    </h6>
                                    <div class="row mb-4">
                                        <?php if (tieneAcceso('miembros_crear')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-user-plus fa-2x text-primary mb-2"></i>
                                                    <h6 class="mb-2">Nuevo Miembro</h6>
                                                    <p class="description">Registrar un nuevo miembro de la iglesia</p>
                                                </div>
                                                <a href="miembros.php?action=nuevo" class="btn btn-outline-primary btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (tieneAcceso('ministerios_crear')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-hands-helping fa-2x text-primary mb-2"></i>
                                                    <h6 class="mb-2">Nuevo Ministerio</h6>
                                                    <p class="description">Crear un nuevo ministerio</p>
                                                </div>
                                                <a href="ministerios.php?action=nuevo" class="btn btn-outline-primary btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (tieneAcceso('celulas_crear')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-users fa-2x text-success mb-2"></i>
                                                    <h6 class="mb-2">Nueva Célula</h6>
                                                    <p class="description">Crear una nueva célula</p>
                                                </div>
                                                <a href="celulas.php?action=nuevo" class="btn btn-outline-success btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (tieneAcceso('eventos_crear')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-calendar-plus fa-2x text-success mb-2"></i>
                                                    <h6 class="mb-2">Nuevo Evento</h6>
                                                    <p class="description">Crear un nuevo evento o actividad</p>
                                                </div>
                                                <a href="eventos.php?action=nuevo" class="btn btn-outline-success btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (tieneAcceso('finanzas_crear')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-plus-circle fa-2x text-info mb-2"></i>
                                                    <h6 class="mb-2">Nueva Transacción</h6>
                                                    <p class="description">Registrar ingreso o egreso financiero</p>
                                                </div>
                                                <a href="finanzas.php?action=nuevo" class="btn btn-outline-info btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (tieneAcceso('docentes_crear') || tieneAcceso('cursos_crear') || tieneAcceso('estudiantes_crear')): ?>
                                        <div class="col-12 mb-2">
                                            <h6 class="text-muted fw-bold ms-2">
                                                <i class="fas fa-graduation-cap"></i> Escuelas
                                            </h6>
                                        </div>
                                        <?php if (tieneAcceso('docentes_crear')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-chalkboard-teacher fa-2x text-danger mb-2"></i>
                                                    <h6 class="mb-2">Nuevo Docente</h6>
                                                    <p class="description">Registrar un nuevo docente</p>
                                                </div>
                                                <a href="docentes.php?action=nuevo" class="btn btn-outline-danger btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (tieneAcceso('cursos_crear')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-book-open fa-2x text-warning mb-2"></i>
                                                    <h6 class="mb-2">Nuevo Curso</h6>
                                                    <p class="description">Crear un nuevo curso de enseñanza</p>
                                                </div>
                                                <a href="cursos.php?action=nuevo" class="btn btn-outline-warning btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if (tieneAcceso('estudiantes_crear')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-user-graduate fa-2x text-purple mb-2"></i>
                                                    <h6 class="mb-2">Nuevo Estudiante</h6>
                                                    <p class="description">Registrar un nuevo estudiante</p>
                                                </div>
                                                <a href="estudiantes.php?action=nuevo" class="btn btn-outline-secondary btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($tieneTransaccional): ?>
                                    <!-- Transaccional -->
                                    <h6 class="text-muted text-uppercase fw-bold mb-3">
                                        <i class="fas fa-clipboard-check"></i> Transaccional
                                    </h6>
                                    <div class="row mb-4">
                                        <?php if (tieneAcceso('asistencias_crear')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-clipboard-check fa-2x text-success mb-2"></i>
                                                    <h6 class="mb-2">Registrar Asistencia</h6>
                                                    <p class="description">Registrar asistencia a cursos</p>
                                                </div>
                                                <a href="asistencias.php" class="btn btn-outline-success btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($tieneReportes): ?>
                                    <!-- Reportes -->
                                    <h6 class="text-muted text-uppercase fw-bold mb-3">
                                        <i class="fas fa-chart-bar"></i> Reportes
                                    </h6>
                                    <div class="row">
                                        <?php if (tieneAcceso('reportes_ver')): ?>
                                        <div class="col-md-3 col-lg-2 mb-3">
                                            <div class="quick-action-card">
                                                <div>
                                                    <i class="fas fa-file-alt fa-2x text-dark mb-2"></i>
                                                    <h6 class="mb-2">Reportes</h6>
                                                    <p class="description">Ver y generar reportes del sistema</p>
                                                </div>
                                                <a href="reportes.php" class="btn btn-outline-dark btn-sm w-100">
                                                    Ir
                                                </a>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>