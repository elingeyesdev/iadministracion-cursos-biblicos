<?php
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once '../NEGOCIO/nAsistencia.php';
require_once '../NEGOCIO/nCurso.php';
require_once '../NEGOCIO/nDocente.php';
require_once '../NEGOCIO/nEstudiante.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

$auth = new AuthController();
$auth->requireLogin();

// Verificar privilegios
if (!auth::tienePrivilegio('asistencias_ver')) {
    header('Location: dashboard.php');
    exit();
}

$nAsistencia = new nAsistencia();
$nCurso = new nCurso();
$nDocente = new nDocente();
$nEstudiante = new nEstudiante();
// Obtener mensaje de URL si existe (después de redirección)
$message = '';
$messageType = '';
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
                    if (!auth::tienePrivilegio('asistencias_crear')) {
                        header('Location: asistencias.php');
                        exit();
                    }
                    // Validar fechas del curso
                    $curso = $nCurso->obtenerPorId($_POST['curso_id']);
                    if ($curso) {
                        $fecha_asistencia = $_POST['fecha'];
                        if ($fecha_asistencia < $curso['fecha_inicio']) {
                            $message = 'Este curso no ha iniciado sus clases. La fecha de inicio es: ' . date('d/m/Y', strtotime($curso['fecha_inicio']));
                            $messageType = 'warning';
                            break;
                        }
                        if ($fecha_asistencia > $curso['fecha_fin']) {
                            $message = 'Curso finalizado. La fecha de finalización fue: ' . date('d/m/Y', strtotime($curso['fecha_fin']));
                            $messageType = 'warning';
                            break;
                        }
                    }
                    
                    $detalle_estudiantes = [];
                    if (isset($_POST['estudiantes']) && is_array($_POST['estudiantes'])) {
                        foreach ($_POST['estudiantes'] as $estudiante_id => $data) {
                            $detalle_estudiantes[] = [
                                'estudiante_id' => $estudiante_id,
                                'estado_asistencia' => $data['estado_asistencia'] ?? 'ausente',
                                'observacion' => $data['observacion'] ?? ''
                            ];
                        }
                    }
                    $resultado = $nAsistencia->insertar(
                        $_POST['curso_id'],
                        $_POST['fecha'],
                        $_POST['docente_id'],
                        $_POST['observaciones'] ?? '',
                        $detalle_estudiantes
                    );
                    if ($resultado['exito']) {
                        $message = $resultado['mensaje'];
                        $messageType = 'success';
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'update':
                    if (!auth::tienePrivilegio('asistencias_editar')) {
                        header('Location: asistencias.php');
                        exit();
                    }
                    // Validar fechas del curso
                    $curso = $nCurso->obtenerPorId($_POST['curso_id']);
                    if ($curso) {
                        $fecha_asistencia = $_POST['fecha'];
                        if ($fecha_asistencia < $curso['fecha_inicio']) {
                            $message = 'Este curso no ha iniciado sus clases. La fecha de inicio es: ' . date('d/m/Y', strtotime($curso['fecha_inicio']));
                            $messageType = 'warning';
                            break;
                        }
                        if ($fecha_asistencia > $curso['fecha_fin']) {
                            $message = 'Curso finalizado. La fecha de finalización fue: ' . date('d/m/Y', strtotime($curso['fecha_fin']));
                            $messageType = 'warning';
                            break;
                        }
                    }
                    
                    $detalle_estudiantes = [];
                    if (isset($_POST['estudiantes']) && is_array($_POST['estudiantes'])) {
                        foreach ($_POST['estudiantes'] as $estudiante_id => $data) {
                            $detalle_estudiantes[] = [
                                'estudiante_id' => $estudiante_id,
                                'estado_asistencia' => $data['estado_asistencia'] ?? 'ausente',
                                'observacion' => $data['observacion'] ?? ''
                            ];
                        }
                    }
                    $resultado = $nAsistencia->actualizar(
                        $_POST['id'],
                        $_POST['curso_id'],
                        $_POST['fecha'],
                        $_POST['docente_id'],
                        $_POST['observaciones'] ?? '',
                        $detalle_estudiantes
                    );
                    if ($resultado['exito']) {
                        $message = $resultado['mensaje'];
                        $messageType = 'success';
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'delete':
                    if (!auth::tienePrivilegio('asistencias_eliminar')) {
                        header('Location: asistencias.php');
                        exit();
                    }
                    // Eliminar toda la asistencia (registro principal)
                    $resultado = $nAsistencia->eliminar($_POST['id']);
                    if ($resultado['exito']) {
                        $message = $resultado['mensaje'];
                        $messageType = 'success';
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'delete_detalle':
                    // Eliminar solo un registro individual (detalle)
                    $resultado = $nAsistencia->eliminarDetalle($_POST['detalle_id']);
                    if ($resultado['exito']) {
                        $message = $resultado['mensaje'];
                        $messageType = 'success';
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'insert_individual':
                    // Validar fechas del curso
                    $curso = $nCurso->obtenerPorId($_POST['curso_id_individual']);
                    if ($curso) {
                        $fecha_asistencia = $_POST['fecha_individual'];
                        if ($fecha_asistencia < $curso['fecha_inicio']) {
                            $message = 'Este curso no ha iniciado sus clases. La fecha de inicio es: ' . date('d/m/Y', strtotime($curso['fecha_inicio']));
                            $messageType = 'warning';
                            break;
                        }
                        if ($fecha_asistencia > $curso['fecha_fin']) {
                            $message = 'Curso finalizado. La fecha de finalización fue: ' . date('d/m/Y', strtotime($curso['fecha_fin']));
                            $messageType = 'warning';
                            break;
                        }
                    }
                    
                    // Buscar o crear asistencia para la fecha
                    $asistencia_id = $nAsistencia->obtenerAsistenciaIdPorCursoYFecha($_POST['curso_id_individual'], $_POST['fecha_individual']);
                    
                    if (!$asistencia_id) {
                        // Crear nueva asistencia
                        $resultado_asistencia = $nAsistencia->insertar(
                            $_POST['curso_id_individual'],
                            $_POST['fecha_individual'],
                            $_POST['docente_id_individual'],
                            'Registro individual',
                            [] // Sin estudiantes por ahora
                        );
                        if ($resultado_asistencia['exito']) {
                            // Obtener el ID de la asistencia recién creada
                            $asistencia_id = $nAsistencia->obtenerAsistenciaIdPorCursoYFecha($_POST['curso_id_individual'], $_POST['fecha_individual']);
                        }
                    }
                    
                    if ($asistencia_id) {
                        // Insertar registro individual
                        $resultado = $nAsistencia->insertarIndividual(
                            $asistencia_id,
                            $_POST['estudiante_id'],
                            $_POST['estado_asistencia'] ?? 'ausente',
                            $_POST['observacion'] ?? ''
                        );
                        if ($resultado['exito']) {
                            $message = $resultado['mensaje'];
                            $messageType = 'success';
                            // Redirigir para evitar reenvío
                            header('Location: asistencias.php?curso_id=' . $_POST['curso_id_individual'] . '&fecha=' . $_POST['fecha_individual'] . '&mensaje=' . urlencode($message) . '&tipo=' . $messageType);
                            exit;
                        } else {
                            $message = $resultado['mensaje'];
                            $messageType = 'danger';
                        }
                    } else {
                        $message = 'Error al crear o encontrar la asistencia';
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

$cursos = $nCurso->listarActivos();
$docentes = $nDocente->listar();

$cursoId = $_GET['curso_id'] ?? null;
$fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : '';
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';
$asistencias = [];
$estudiantesCurso = [];
$estadisticas = null;

if ($cursoId) {
    // Normalizar valores de fechas - convertir strings vacíos a null
    $fecha_desde_valida = (!empty($fecha_desde) && trim($fecha_desde) !== '') ? trim($fecha_desde) : null;
    $fecha_hasta_valida = (!empty($fecha_hasta) && trim($fecha_hasta) !== '') ? trim($fecha_hasta) : null;
    $fecha_valida = (!empty($fecha) && trim($fecha) !== '') ? trim($fecha) : null;
    
    // Verificar si hay rango de fechas (desde o hasta) - tiene máxima prioridad
    $tieneRangoFechas = ($fecha_desde_valida !== null) || ($fecha_hasta_valida !== null);
    
    if ($tieneRangoFechas) {
        // Si hay rango de fechas, usar búsqueda por rango (ignorar completamente fecha específica)
        $asistencias = $nAsistencia->buscar(
            !empty($buscar) ? $buscar : '', 
            $cursoId, 
            $fecha_desde_valida, 
            $fecha_hasta_valida
        );
    }
    // Si hay búsqueda de texto sin rango de fechas, usar búsqueda general
    elseif (!empty($buscar)) {
        $asistencias = $nAsistencia->buscar($buscar, $cursoId, null, null);
    }
    // Si hay fecha específica (y NO hay rango de fechas), usar búsqueda por fecha específica
    elseif ($fecha_valida !== null) {
        $asistencias = $nAsistencia->listarPorCursoYFecha($cursoId, $fecha_valida);
    }
    // Si no hay filtros de fecha, mostrar fecha actual por defecto
    else {
        $fecha = date('Y-m-d');
        $asistencias = $nAsistencia->listarPorCursoYFecha($cursoId, $fecha);
    }
    
    // Obtener estudiantes del curso
    $curso = $nCurso->obtenerPorId($cursoId);
    if ($curso) {
        // Obtener estudiantes inscritos en el curso
        require_once '../DATOS/dEstudiante.php';
        $dEstudiante = new dEstudiante();
        $estudiantesCurso = $dEstudiante->listarPorCurso($cursoId);
    }
    
    // Obtener estadísticas del curso
    $estadisticas = $nAsistencia->obtenerEstadisticasPorCurso($cursoId);
}

// Obtener datos para editar si se solicita
$asistenciaEditar = null;
if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $asistenciaEditar = $nAsistencia->obtenerPorId($_GET['id']);
    if ($asistenciaEditar) {
        $cursoId = $asistenciaEditar['curso_id'];
        $fecha = $asistenciaEditar['fecha'];
        // Obtener estudiantes del curso
        require_once '../DATOS/dEstudiante.php';
        $dEstudiante = new dEstudiante();
        $estudiantesCurso = $dEstudiante->listarPorCurso($cursoId);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencias - Sistema de Administración de Iglesia</title>
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
    </style>
    <?php echo generarEstilosNotificaciones(); ?>
</head>
<body>
    <!-- Offcanvas Menu (móviles) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuAsistencias" aria-labelledby="offcanvasMenuAsistenciasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuAsistenciasLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('asistencias'); ?>
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
                        <?php echo generarMenu('asistencias'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuAsistencias" aria-controls="offcanvasMenuAsistencias">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-user-check text-primary"></i> Asistencias
                        </h4>
                        <div class="d-flex align-items-center">
                            <?php echo generarBotonNotificacionesNavbar(); ?>
                        </div>
                    </div>
                </nav>
                
                <div class="container-fluid py-4">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Registro de Asistencias</h5>
                                <div class="btn-group">
                                    <?php if ($cursoId && tieneAcceso('asistencias_crear')): ?>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAsistencia">
                                        <i class="fas fa-plus"></i> Registrar Asistencia
                                    </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalIndividual">
                                        <i class="fas fa-user-plus"></i> Registro Individual
                                    </button>
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalEstadisticas">
                                        <i class="fas fa-chart-bar"></i> Estadísticas
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="fas fa-search"></i> Buscar Registros de Asistencias</h6>
                            <p class="text-muted small mb-3">
                                <strong>Instrucciones:</strong> Seleccione un curso para ver las asistencias. Puede buscar por fecha específica, rango de fechas o por nombre de estudiante/curso. 
                                Use el campo de búsqueda de texto para encontrar registros por nombre de estudiante o nombre del curso.
                            </p>
                            <div id="alerta_fecha_busqueda" class="alert alert-warning d-none mb-3" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> <span id="mensaje_alerta_busqueda"></span>
                            </div>
                            <form method="GET" class="row g-3" id="formBusqueda">
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label small fw-bold">Curso *</label>
                                    <select name="curso_id" class="form-select" id="curso_id_busqueda" required>
                                        <option value="">Seleccione curso...</option>
                                        <?php foreach ($cursos as $curso): ?>
                                            <option value="<?php echo $curso['id']; ?>" 
                                                    data-fecha-inicio="<?php echo $curso['fecha_inicio']; ?>"
                                                    data-fecha-fin="<?php echo $curso['fecha_fin']; ?>"
                                                    <?php echo $cursoId == $curso['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($curso['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 col-lg-2">
                                    <label class="form-label small fw-bold">Fecha Específica</label>
                                    <input type="date" name="fecha" class="form-control" value="<?php 
                                        // Solo mostrar fecha si NO hay rango de fechas
                                        $tieneRango = (!empty($fecha_desde) && $fecha_desde !== '') || (!empty($fecha_hasta) && $fecha_hasta !== '');
                                        if ($tieneRango) {
                                            echo '';
                                        } else {
                                            echo htmlspecialchars($fecha ?: date('Y-m-d'));
                                        }
                                    ?>" placeholder="dd/mm/aaaa" id="fecha_especifica">
                                    <small class="text-muted d-none d-lg-block">Ver asistencias de una fecha (se ignora si usa rango)</small>
                                </div>
                                <div class="col-12 col-md-6 col-lg-2">
                                    <label class="form-label small fw-bold">Fecha Desde</label>
                                    <div class="input-group">
                                        <input type="date" name="fecha_desde" class="form-control" value="<?php echo htmlspecialchars($fecha_desde ?? ''); ?>" placeholder="dd/mm/aaaa" id="fecha_desde">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAutoFechaDesde" title="Usar fecha de inicio del curso" style="border-left: 0;">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted d-none d-lg-block">Inicio del rango</small>
                                </div>
                                <div class="col-12 col-md-6 col-lg-2">
                                    <label class="form-label small fw-bold">Fecha Hasta</label>
                                    <div class="input-group">
                                        <input type="date" name="fecha_hasta" class="form-control" value="<?php echo htmlspecialchars($fecha_hasta ?? ''); ?>" placeholder="dd/mm/aaaa" id="fecha_hasta">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAutoFechaHasta" title="Usar fecha de fin del curso" style="border-left: 0;">
                                            <i class="fas fa-magic"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted d-none d-lg-block">Fin del rango </small>
                                </div>
                                <div class="col-12 col-md-6 col-lg-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-info btn-sm w-100" id="btnAutoFechas" title="Llenar ambas fechas con el rango del curso">
                                        <i class="fas fa-calendar-alt"></i> Auto
                                    </button>
                                </div>
                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label small fw-bold">Buscar por Nombre</label>
                                    <input type="text" name="buscar" class="form-control" value="<?php echo htmlspecialchars($buscar); ?>" placeholder="Nombre estudiante o curso...">
                                    
                                </div>
                                <div class="col-sm-2 d-flex align-items-end">
                                    <button class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                </div>
                                <?php if ($cursoId || $buscar): ?>
                                <div class="col-sm-2 d-flex align-items-end">
                                    <a href="asistencias.php" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    
                    <?php if ($estadisticas && $cursoId): ?>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Clases</h6>
                                    <h3><?php echo $estadisticas['total_clases'] ?? 0; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Presentes</h6>
                                    <h3><?php echo $estadisticas['total_presentes'] ?? 0; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Ausentes</h6>
                                    <h3><?php echo $estadisticas['total_ausentes'] ?? 0; ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">% Asistencia</h6>
                                    <h3><?php 
                                        $total = ($estadisticas['total_presentes'] ?? 0) + ($estadisticas['total_ausentes'] ?? 0);
                                        $porcentaje = $total > 0 ? round((($estadisticas['total_presentes'] ?? 0) / $total) * 100, 1) : 0;
                                        echo $porcentaje . '%';
                                    ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Estudiante</th>
                                            <th>Presente</th>
                                            <th>Observación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($asistencias as $a): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(trim(($a['estudiante_nombre'] ?? '') . ' ' . ($a['estudiante_apellido'] ?? ''))); ?></td>
                                                <td>
                                                    <?php 
                                                    $estado_asist = $a['estado_asistencia'] ?? 'ausente';
                                                    $badge_class = 'secondary';
                                                    $texto = 'Ausente';
                                                    if ($estado_asist === 'presente') {
                                                        $badge_class = 'success';
                                                        $texto = 'Presente';
                                                    } elseif ($estado_asist === 'retraso') {
                                                        $badge_class = 'warning';
                                                        $texto = 'Retraso';
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                                        <?php echo $texto; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($a['observacion'] ?? ''); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if (tieneAcceso('asistencias_editar')): ?>
                                                        <a href="asistencias.php?curso_id=<?php echo $cursoId; ?>&fecha=<?php echo $fecha; ?>&action=editar&id=<?php echo $a['id']; ?>" 
                                                           class="btn btn-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                        <?php if (tieneAcceso('asistencias_eliminar')): ?>
                                                        <button type="button" class="btn btn-danger" 
                                                                onclick="confirmarEliminarDetalle(<?php echo $a['id']; ?>)" title="Eliminar registro individual">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($asistencias) && $cursoId): ?>
                                            <tr><td colspan="4" class="text-center text-muted">No hay registros de asistencia para esta fecha. <a href="#" data-bs-toggle="modal" data-bs-target="#modalAsistencia">Registrar asistencia</a></td></tr>
                                        <?php elseif (empty($asistencias)): ?>
                                            <tr><td colspan="4" class="text-center text-muted">Seleccione un curso y fecha para ver las asistencias</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Asistencia -->
    <?php if ($cursoId): ?>
    <div class="modal fade" id="modalAsistencia" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $asistenciaEditar ? 'Editar Asistencia' : 'Registrar Asistencia'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $asistenciaEditar ? 'update' : 'insert'; ?>">
                        <?php if ($asistenciaEditar): ?>
                            <input type="hidden" name="id" value="<?php echo $asistenciaEditar['id']; ?>">
                        <?php endif; ?>
                        
                        <div id="alerta_fecha_modal" class="alert alert-warning d-none mb-3" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <span id="mensaje_alerta_modal"></span>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="curso_id" class="form-label">Curso *</label>
                                <select class="form-select" id="curso_id" name="curso_id" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($cursos as $curso): ?>
                                        <option value="<?php echo $curso['id']; ?>" 
                                                data-fecha-inicio="<?php echo $curso['fecha_inicio']; ?>"
                                                data-fecha-fin="<?php echo $curso['fecha_fin']; ?>"
                                                data-docente-id="<?php echo $curso['docente_id'] ?? ''; ?>"
                                                <?php echo ($cursoId == $curso['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($curso['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="fecha" class="form-label">Fecha *</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" 
                                       value="<?php echo htmlspecialchars($fecha); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="docente_id" class="form-label">Docente *</label>
                                <select class="form-select" id="docente_id" name="docente_id" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($docentes as $docente): ?>
                                        <option value="<?php echo $docente['id']; ?>" 
                                                <?php echo ($asistenciaEditar && ($asistenciaEditar['docente_id'] ?? null) == $docente['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(trim(($docente['nombres'] ?? '') . ' ' . ($docente['apellidos'] ?? ''))); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="2"><?php echo $asistenciaEditar ? htmlspecialchars($asistenciaEditar['observaciones'] ?? '') : ''; ?></textarea>
                        </div>
                        
                        <?php if (!empty($estudiantesCurso)): ?>
                        <div class="mb-3">
                            <label class="form-label">Estudiantes del Curso</label>
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Estudiante</th>
                                            <th class="text-center">Estado</th>
                                            <th>Observación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $detalleEditar = [];
                                        if ($asistenciaEditar && isset($asistenciaEditar['detalle'])) {
                                            foreach ($asistenciaEditar['detalle'] as $det) {
                                                $detalleEditar[$det['estudiante_id']] = $det;
                                            }
                                        }
                                        foreach ($estudiantesCurso as $estudiante): 
                                            $detalle = $detalleEditar[$estudiante['id']] ?? null;
                                        ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars(trim(($estudiante['nombres'] ?? '') . ' ' . ($estudiante['apellidos'] ?? ''))); ?>
                                                </td>
                                                <td class="text-center">
                                                    <select class="form-select form-select-sm" 
                                                            name="estudiantes[<?php echo $estudiante['id']; ?>][estado_asistencia]">
                                                        <option value="ausente" <?php echo ($detalle && ($detalle['estado_asistencia'] ?? 'ausente') === 'ausente') ? 'selected' : ''; ?>>Ausente</option>
                                                        <option value="presente" <?php echo ($detalle && ($detalle['estado_asistencia'] ?? '') === 'presente') ? 'selected' : ''; ?>>Presente</option>
                                                        <option value="retraso" <?php echo ($detalle && ($detalle['estado_asistencia'] ?? '') === 'retraso') ? 'selected' : ''; ?>>Retraso</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" 
                                                           class="form-control form-control-sm" 
                                                           name="estudiantes[<?php echo $estudiante['id']; ?>][observacion]"
                                                           value="<?php echo $detalle ? htmlspecialchars($detalle['observacion'] ?? '') : ''; ?>"
                                                           placeholder="Observación...">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                No hay estudiantes inscritos en este curso.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" <?php echo empty($estudiantesCurso) ? 'disabled' : ''; ?>>Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal Registro Individual -->
    <?php if ($cursoId): ?>
    <div class="modal fade" id="modalIndividual" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registro Individual de Asistencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="insert_individual">
                        
                        <div id="alerta_fecha_individual" class="alert alert-warning d-none mb-3" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> <span id="mensaje_alerta_individual"></span>
                        </div>
                        
                        <div class="mb-3">
                            <label for="fecha_individual" class="form-label">Fecha *</label>
                            <input type="date" class="form-control" id="fecha_individual" name="fecha_individual" 
                                   value="<?php echo htmlspecialchars($fecha); ?>" required>
                            <small class="text-muted">Se creará automáticamente la asistencia si no existe para esta fecha</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="docente_id_individual" class="form-label">Docente *</label>
                            <select class="form-select" id="docente_id_individual" name="docente_id_individual" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($docentes as $docente): ?>
                                    <option value="<?php echo $docente['id']; ?>">
                                        <?php echo htmlspecialchars(trim(($docente['nombres'] ?? '') . ' ' . ($docente['apellidos'] ?? ''))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <input type="hidden" name="curso_id_individual" value="<?php echo $cursoId; ?>">
                        
                        <div class="mb-3">
                            <label for="estudiante_id_individual" class="form-label">Estudiante *</label>
                            <select class="form-select" id="estudiante_id_individual" name="estudiante_id" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($estudiantesCurso as $estudiante): ?>
                                    <option value="<?php echo $estudiante['id']; ?>">
                                        <?php echo htmlspecialchars(trim(($estudiante['nombres'] ?? '') . ' ' . ($estudiante['apellidos'] ?? ''))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Estado de Asistencia</label>
                            <select class="form-select" id="estado_asistencia_individual" name="estado_asistencia" required>
                                <option value="presente" selected>Presente</option>
                                <option value="ausente">Ausente</option>
                                <option value="retraso">Retraso</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observacion_individual" class="form-label">Observación</label>
                            <textarea class="form-control" id="observacion_individual" name="observacion" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Modal Estadísticas -->
    <?php if ($cursoId): ?>
    <div class="modal fade" id="modalEstadisticas" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Estadísticas de Asistencia por Estudiante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Estudiante</th>
                                    <th class="text-center">Total Clases</th>
                                    <th class="text-center">Presentes</th>
                                    <th class="text-center">Ausentes</th>
                                    <th class="text-center">% Asistencia</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($estudiantesCurso as $estudiante): 
                                    $stats = $nAsistencia->obtenerEstadisticasPorEstudiante($estudiante['id'], $cursoId);
                                    $porcentaje = $stats['porcentaje_asistencia'] ?? 0;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(trim(($estudiante['nombres'] ?? '') . ' ' . ($estudiante['apellidos'] ?? ''))); ?></td>
                                        <td class="text-center"><?php echo $stats['total_clases'] ?? 0; ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><?php echo $stats['total_presentes'] ?? 0; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger"><?php echo $stats['total_ausentes'] ?? 0; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo $porcentaje >= 80 ? 'success' : ($porcentaje >= 60 ? 'warning' : 'danger'); ?>">
                                                <?php echo number_format($porcentaje, 1); ?>%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    onclick="verDetalleEstudiante(<?php echo $estudiante['id']; ?>, '<?php echo htmlspecialchars(trim(($estudiante['nombres'] ?? '') . ' ' . ($estudiante['apellidos'] ?? ''))); ?>')">
                                                <i class="fas fa-eye"></i> Ver Detalle
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Detalle Estudiante -->
    <div class="modal fade" id="modalDetalleEstudiante" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloDetalleEstudiante">Detalle de Asistencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="contenidoDetalleEstudiante">
                    <!-- Se llenará con JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de eliminar este registro de asistencia completo?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function confirmarEliminarDetalle(detalle_id) {
            if (confirm('¿Está seguro de eliminar solo este registro individual?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete_detalle"><input type="hidden" name="detalle_id" value="' + detalle_id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function verDetalleEstudiante(estudiante_id, nombreEstudiante) {
            const cursoId = <?php echo $cursoId ?: 'null'; ?>;
            if (!cursoId) return;
            
            document.getElementById('tituloDetalleEstudiante').textContent = 'Detalle de Asistencia - ' + nombreEstudiante;
            document.getElementById('contenidoDetalleEstudiante').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Cargando...</p></div>';
            
            const modal = new bootstrap.Modal(document.getElementById('modalDetalleEstudiante'));
            modal.show();
            
            // Cargar datos via AJAX
            fetch('ajax_estadisticas_estudiante.php?estudiante_id=' + estudiante_id + '&curso_id=' + cursoId)
                .then(response => response.json())
                .then(data => {
                    let html = '<table class="table table-sm">';
                    html += '<thead><tr><th>Fecha</th><th>Estado</th><th>Observación</th></tr></thead><tbody>';
                    
                    if (data.length > 0) {
                        data.forEach(function(registro) {
                            html += '<tr>';
                            html += '<td>' + registro.fecha + '</td>';
                            var estado = registro.estado_asistencia || 'ausente';
                            var badgeClass = 'secondary';
                            var texto = 'Ausente';
                            if (estado === 'presente') {
                                badgeClass = 'success';
                                texto = 'Presente';
                            } else if (estado === 'retraso') {
                                badgeClass = 'warning';
                                texto = 'Retraso';
                            }
                            html += '<td><span class="badge bg-' + badgeClass + '">' + texto + '</span></td>';
                            html += '<td>' + (registro.observacion || '-') + '</td>';
                            html += '</tr>';
                        });
                    } else {
                        html += '<tr><td colspan="3" class="text-center text-muted">No hay registros</td></tr>';
                    }
                    
                    html += '</tbody></table>';
                    document.getElementById('contenidoDetalleEstudiante').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('contenidoDetalleEstudiante').innerHTML = 
                        '<div class="alert alert-danger">Error al cargar los datos</div>';
                });
        }
        
        <?php if ($asistenciaEditar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalAsistencia'));
            modal.show();
        });
        <?php endif; ?>
        
        // Actualizar estudiantes al cambiar curso
        document.getElementById('curso_id')?.addEventListener('change', function() {
            const cursoId = this.value;
            const fecha = document.getElementById('fecha_especifica')?.value;
            if (cursoId && fecha) {
                window.location.href = 'asistencias.php?curso_id=' + cursoId + '&fecha=' + fecha;
            }
        });
        
        // Limpiar fecha específica cuando se use rango de fechas
        const fechaDesde = document.getElementById('fecha_desde');
        const fechaHasta = document.getElementById('fecha_hasta');
        const fechaEspecifica = document.getElementById('fecha_especifica');
        
        if (fechaDesde && fechaEspecifica) {
            fechaDesde.addEventListener('change', function() {
                if (this.value) {
                    fechaEspecifica.value = '';
                }
            });
        }
        
        if (fechaHasta && fechaEspecifica) {
            fechaHasta.addEventListener('change', function() {
                if (this.value) {
                    fechaEspecifica.value = '';
                }
            });
        }
        
        // Limpiar rango cuando se use fecha específica
        if (fechaEspecifica && fechaDesde && fechaHasta) {
            fechaEspecifica.addEventListener('change', function() {
                if (this.value) {
                    fechaDesde.value = '';
                    fechaHasta.value = '';
                }
            });
        }
        
        // Validación de fechas del curso en tiempo real
        function validarFechaCurso(selectCurso, inputFecha, alertaDiv, mensajeSpan) {
            if (!selectCurso || !inputFecha || !alertaDiv || !mensajeSpan) return;
            
            const cursoId = selectCurso.value;
            const fechaAsistencia = inputFecha.value;
            
            if (!cursoId || !fechaAsistencia) {
                alertaDiv.classList.add('d-none');
                return;
            }
            
            const option = selectCurso.querySelector(`option[value="${cursoId}"]`);
            if (!option) {
                alertaDiv.classList.add('d-none');
                return;
            }
            
            const fechaInicio = option.getAttribute('data-fecha-inicio');
            const fechaFin = option.getAttribute('data-fecha-fin');
            
            if (!fechaInicio || !fechaFin) {
                alertaDiv.classList.add('d-none');
                return;
            }
            
            if (fechaAsistencia < fechaInicio) {
                const fechaInicioFormateada = new Date(fechaInicio + 'T00:00:00').toLocaleDateString('es-ES', {day: '2-digit', month: '2-digit', year: 'numeric'});
                mensajeSpan.textContent = 'Este curso no ha iniciado sus clases. La fecha de inicio es: ' + fechaInicioFormateada;
                alertaDiv.classList.remove('d-none');
                alertaDiv.classList.remove('alert-success');
                alertaDiv.classList.add('alert-warning');
                return false;
            } else if (fechaAsistencia > fechaFin) {
                const fechaFinFormateada = new Date(fechaFin + 'T00:00:00').toLocaleDateString('es-ES', {day: '2-digit', month: '2-digit', year: 'numeric'});
                mensajeSpan.textContent = 'Curso finalizado. La fecha de finalización fue: ' + fechaFinFormateada;
                alertaDiv.classList.remove('d-none');
                alertaDiv.classList.remove('alert-success');
                alertaDiv.classList.add('alert-warning');
                return false;
            } else {
                alertaDiv.classList.add('d-none');
                return true;
            }
        }
        
        // Validación para modal de asistencia principal
        const cursoSelectModal = document.getElementById('curso_id');
        const fechaInputModal = document.getElementById('fecha');
        const docenteSelectModal = document.getElementById('docente_id');
        const alertaModal = document.getElementById('alerta_fecha_modal');
        const mensajeModal = document.getElementById('mensaje_alerta_modal');
        
        // Función para seleccionar automáticamente el docente según el curso
        function seleccionarDocentePorCurso() {
            if (!cursoSelectModal || !docenteSelectModal) return;
            
            const cursoSeleccionado = cursoSelectModal.options[cursoSelectModal.selectedIndex];
            if (!cursoSeleccionado || !cursoSeleccionado.value) {
                return;
            }
            
            const docenteId = cursoSeleccionado.getAttribute('data-docente-id');
            if (docenteId && docenteSelectModal) {
                // Buscar la opción del docente y seleccionarla
                for (let i = 0; i < docenteSelectModal.options.length; i++) {
                    if (docenteSelectModal.options[i].value == docenteId) {
                        docenteSelectModal.selectedIndex = i;
                        break;
                    }
                }
            }
        }
        
        if (cursoSelectModal && fechaInputModal) {
            cursoSelectModal.addEventListener('change', function() {
                validarFechaCurso(this, fechaInputModal, alertaModal, mensajeModal);
                seleccionarDocentePorCurso();
            });
            
            fechaInputModal.addEventListener('change', function() {
                validarFechaCurso(cursoSelectModal, this, alertaModal, mensajeModal);
            });
            
            // Seleccionar docente automáticamente al cargar si hay un curso seleccionado
            if (cursoSelectModal.value) {
                seleccionarDocentePorCurso();
            }
        }
        
        // Validación para modal de registro individual
        const fechaInputIndividual = document.getElementById('fecha_individual');
        const docenteSelectIndividual = document.getElementById('docente_id_individual');
        const alertaIndividual = document.getElementById('alerta_fecha_individual');
        const mensajeIndividual = document.getElementById('mensaje_alerta_individual');
        const cursoIdIndividual = <?php echo $cursoId ?: 'null'; ?>;
        
        if (fechaInputIndividual && alertaIndividual && cursoIdIndividual) {
            // Obtener datos del curso actual
            const cursoActual = <?php 
                if ($cursoId) {
                    $curso = $nCurso->obtenerPorId($cursoId);
                    if ($curso) {
                        echo json_encode(['fecha_inicio' => $curso['fecha_inicio'], 'fecha_fin' => $curso['fecha_fin'], 'docente_id' => $curso['docente_id'] ?? null]);
                    } else {
                        echo 'null';
                    }
                } else {
                    echo 'null';
                }
            ?>;
            
            // Seleccionar docente automáticamente si existe
            if (cursoActual && cursoActual.docente_id && docenteSelectIndividual) {
                for (let i = 0; i < docenteSelectIndividual.options.length; i++) {
                    if (docenteSelectIndividual.options[i].value == cursoActual.docente_id) {
                        docenteSelectIndividual.selectedIndex = i;
                        break;
                    }
                }
            }
            
            if (cursoActual) {
                fechaInputIndividual.addEventListener('change', function() {
                    const fechaAsistencia = this.value;
                    
                    if (!fechaAsistencia) {
                        alertaIndividual.classList.add('d-none');
                        return;
                    }
                    
                    if (fechaAsistencia < cursoActual.fecha_inicio) {
                        const fechaInicioFormateada = new Date(cursoActual.fecha_inicio + 'T00:00:00').toLocaleDateString('es-ES', {day: '2-digit', month: '2-digit', year: 'numeric'});
                        mensajeIndividual.textContent = 'Este curso no ha iniciado sus clases. La fecha de inicio es: ' + fechaInicioFormateada;
                        alertaIndividual.classList.remove('d-none');
                        alertaIndividual.classList.remove('alert-success');
                        alertaIndividual.classList.add('alert-warning');
                    } else if (fechaAsistencia > cursoActual.fecha_fin) {
                        const fechaFinFormateada = new Date(cursoActual.fecha_fin + 'T00:00:00').toLocaleDateString('es-ES', {day: '2-digit', month: '2-digit', year: 'numeric'});
                        mensajeIndividual.textContent = 'Curso finalizado. La fecha de finalización fue: ' + fechaFinFormateada;
                        alertaIndividual.classList.remove('d-none');
                        alertaIndividual.classList.remove('alert-success');
                        alertaIndividual.classList.add('alert-warning');
                    } else {
                        alertaIndividual.classList.add('d-none');
                    }
                });
                
                // Validar al cargar si ya hay una fecha
                if (fechaInputIndividual.value) {
                    fechaInputIndividual.dispatchEvent(new Event('change'));
                }
            }
        }
        
        // Seleccionar docente automáticamente cuando se abre el modal individual
        const modalIndividual = document.getElementById('modalIndividual');
        if (modalIndividual && cursoIdIndividual) {
            modalIndividual.addEventListener('show.bs.modal', function() {
                const cursoActual = <?php 
                    if ($cursoId) {
                        $curso = $nCurso->obtenerPorId($cursoId);
                        if ($curso) {
                            echo json_encode(['docente_id' => $curso['docente_id'] ?? null]);
                        } else {
                            echo 'null';
                        }
                    } else {
                        echo 'null';
                    }
                ?>;
                
                if (cursoActual && cursoActual.docente_id && docenteSelectIndividual) {
                    for (let i = 0; i < docenteSelectIndividual.options.length; i++) {
                        if (docenteSelectIndividual.options[i].value == cursoActual.docente_id) {
                            docenteSelectIndividual.selectedIndex = i;
                            break;
                        }
                    }
                }
            });
        }
        
        // Validación para formulario de búsqueda
        const cursoSelectBusqueda = document.getElementById('curso_id_busqueda');
        const fechaEspecificaBusqueda = document.getElementById('fecha_especifica');
        const fechaDesdeBusqueda = document.getElementById('fecha_desde');
        const fechaHastaBusqueda = document.getElementById('fecha_hasta');
        const alertaBusqueda = document.getElementById('alerta_fecha_busqueda');
        const mensajeBusqueda = document.getElementById('mensaje_alerta_busqueda');
        const formBusqueda = document.getElementById('formBusqueda');
        
        // Función para obtener las fechas del curso seleccionado
        function obtenerFechasCurso() {
            if (!cursoSelectBusqueda || !cursoSelectBusqueda.value) {
                return null;
            }
            const cursoSeleccionado = cursoSelectBusqueda.options[cursoSelectBusqueda.selectedIndex];
            if (!cursoSeleccionado) {
                return null;
            }
            return {
                fechaInicio: cursoSeleccionado.getAttribute('data-fecha-inicio'),
                fechaFin: cursoSeleccionado.getAttribute('data-fecha-fin')
            };
        }
        
        // Botón para llenar fecha desde automáticamente
        const btnAutoFechaDesde = document.getElementById('btnAutoFechaDesde');
        if (btnAutoFechaDesde && fechaDesdeBusqueda) {
            btnAutoFechaDesde.addEventListener('click', function() {
                const fechas = obtenerFechasCurso();
                if (fechas && fechas.fechaInicio) {
                    fechaDesdeBusqueda.value = fechas.fechaInicio;
                    // Limpiar fecha específica si se llena el rango
                    if (fechaEspecificaBusqueda) {
                        fechaEspecificaBusqueda.value = '';
                    }
                } else {
                    alert('Por favor, seleccione un curso primero');
                }
            });
        }
        
        // Botón para llenar fecha hasta automáticamente
        const btnAutoFechaHasta = document.getElementById('btnAutoFechaHasta');
        if (btnAutoFechaHasta && fechaHastaBusqueda) {
            btnAutoFechaHasta.addEventListener('click', function() {
                const fechas = obtenerFechasCurso();
                if (fechas && fechas.fechaFin) {
                    fechaHastaBusqueda.value = fechas.fechaFin;
                    // Limpiar fecha específica si se llena el rango
                    if (fechaEspecificaBusqueda) {
                        fechaEspecificaBusqueda.value = '';
                    }
                } else {
                    alert('Por favor, seleccione un curso primero');
                }
            });
        }
        
        // Botón para llenar ambas fechas automáticamente
        const btnAutoFechas = document.getElementById('btnAutoFechas');
        if (btnAutoFechas && fechaDesdeBusqueda && fechaHastaBusqueda) {
            btnAutoFechas.addEventListener('click', function() {
                const fechas = obtenerFechasCurso();
                if (fechas && fechas.fechaInicio && fechas.fechaFin) {
                    fechaDesdeBusqueda.value = fechas.fechaInicio;
                    fechaHastaBusqueda.value = fechas.fechaFin;
                    // Limpiar fecha específica si se llena el rango
                    if (fechaEspecificaBusqueda) {
                        fechaEspecificaBusqueda.value = '';
                    }
                } else {
                    alert('Por favor, seleccione un curso primero');
                }
            });
        }
        
        // Limpiar fecha específica cuando se cambian las fechas desde/hasta manualmente
        if (fechaDesdeBusqueda && fechaEspecificaBusqueda) {
            fechaDesdeBusqueda.addEventListener('change', function() {
                if (this.value) {
                    fechaEspecificaBusqueda.value = '';
                }
            });
        }
        
        if (fechaHastaBusqueda && fechaEspecificaBusqueda) {
            fechaHastaBusqueda.addEventListener('change', function() {
                if (this.value) {
                    fechaEspecificaBusqueda.value = '';
                }
            });
        }
        
        // Limpiar fechas desde/hasta cuando se cambia la fecha específica
        if (fechaEspecificaBusqueda && fechaDesdeBusqueda && fechaHastaBusqueda) {
            fechaEspecificaBusqueda.addEventListener('change', function() {
                if (this.value) {
                    fechaDesdeBusqueda.value = '';
                    fechaHastaBusqueda.value = '';
                }
            });
        }
        
        if (cursoSelectBusqueda && fechaEspecificaBusqueda && alertaBusqueda) {
            cursoSelectBusqueda.addEventListener('change', function() {
                validarFechaCurso(this, fechaEspecificaBusqueda, alertaBusqueda, mensajeBusqueda);
            });
            
            fechaEspecificaBusqueda.addEventListener('change', function() {
                validarFechaCurso(cursoSelectBusqueda, this, alertaBusqueda, mensajeBusqueda);
            });
            
            // Prevenir envío si la fecha no es válida
            // PERO solo validar fecha específica si NO hay rango de fechas
            if (formBusqueda) {
                formBusqueda.addEventListener('submit', function(e) {
                    // Verificar si hay rango de fechas (desde o hasta)
                    const tieneRangoFechas = (fechaDesdeBusqueda && fechaDesdeBusqueda.value) || 
                                            (fechaHastaBusqueda && fechaHastaBusqueda.value);
                    
                    // Solo validar fecha específica si NO hay rango de fechas
                    if (!tieneRangoFechas) {
                        if (!validarFechaCurso(cursoSelectBusqueda, fechaEspecificaBusqueda, alertaBusqueda, mensajeBusqueda)) {
                            e.preventDefault();
                            alertaBusqueda.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            return false;
                        }
                    } else {
                        // Si hay rango de fechas, ocultar cualquier alerta de fecha específica
                        if (alertaBusqueda) {
                            alertaBusqueda.classList.add('d-none');
                        }
                    }
                });
            }
        }
        
        // Prevenir envío de formularios de modales si la fecha no es válida
        const formModalAsistencia = document.querySelector('#modalAsistencia form');
        if (formModalAsistencia) {
            formModalAsistencia.addEventListener('submit', function(e) {
                if (!validarFechaCurso(cursoSelectModal, fechaInputModal, alertaModal, mensajeModal)) {
                    e.preventDefault();
                    alertaModal.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    return false;
                }
            });
        }
        
        const formModalIndividual = document.querySelector('#modalIndividual form');
        if (formModalIndividual && fechaInputIndividual && cursoIdIndividual) {
            formModalIndividual.addEventListener('submit', function(e) {
                const cursoActual = <?php 
                    if ($cursoId) {
                        $curso = $nCurso->obtenerPorId($cursoId);
                        if ($curso) {
                            echo json_encode(['fecha_inicio' => $curso['fecha_inicio'], 'fecha_fin' => $curso['fecha_fin']]);
                        } else {
                            echo 'null';
                        }
                    } else {
                        echo 'null';
                    }
                ?>;
                
                if (cursoActual && fechaInputIndividual.value) {
                    const fechaAsistencia = fechaInputIndividual.value;
                    if (fechaAsistencia < cursoActual.fecha_inicio || fechaAsistencia > cursoActual.fecha_fin) {
                        e.preventDefault();
                        alertaIndividual.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        return false;
                    }
                }
            });
        }
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
<?php /* eof */ ?>


