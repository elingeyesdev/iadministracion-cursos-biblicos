<?php
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once '../NEGOCIO/nReporte.php';
require_once '../NEGOCIO/nFinanza.php';
require_once '../NEGOCIO/nTipoTransaccion.php';
require_once '../NEGOCIO/nEstudiante.php';
require_once '../NEGOCIO/nDocente.php';
require_once '../NEGOCIO/nCurso.php';
require_once '../NEGOCIO/nAsistencia.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

// Headers para prevenir cacheo
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$auth = new AuthController();
$auth->requireLogin();

// Verificar privilegios - al menos uno de los privilegios de reportes
if (!auth::tienePrivilegio('reportes_ver') && !auth::tienePrivilegio('reportes_finanzas') && 
    !auth::tienePrivilegio('reportes_escuelas') && !auth::tienePrivilegio('reportes_miembros')) {
    header('Location: dashboard.php');
    exit();
}

$categoria = $_GET['categoria'] ?? ''; // Categoría principal: escuelas, finanzas, miembros, general
$tipo = $_GET['tipo'] ?? ''; // Tipo específico dentro de la categoría
$busqueda = $_GET['busqueda'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$tipo_transaccion_id = $_GET['tipo_transaccion_id'] ?? '';

// Si no hay categoría pero hay tipo, mantener compatibilidad con URLs antiguas
if (empty($categoria) && !empty($tipo)) {
    // Mapear tipos antiguos a categorías
    if (in_array($tipo, ['estudiantes', 'docentes', 'cursos', 'asistencias'])) {
        $categoria = 'escuelas';
    } elseif (in_array($tipo, ['transacciones', 'busqueda'])) {
        $categoria = 'finanzas';
    } elseif ($tipo === 'miembros') {
        $categoria = 'miembros';
    } else {
        $categoria = 'general';
    }
}

$nReporte = new nReporte();
$nFinanza = new nFinanza();
$nTipoTransaccion = new nTipoTransaccion();
$nEstudiante = new nEstudiante();
$nDocente = new nDocente();
$nCurso = new nCurso();
$nAsistencia = new nAsistencia();

// Para búsqueda por curso en asistencias
$curso_id_busqueda = $_GET['curso_id'] ?? '';

$data = [];
$tipos_transacciones = $nTipoTransaccion->listar();
$cursos_lista = $nCurso->listar(); // Para el dropdown de búsqueda por curso

// Obtener datos según la categoría y tipo
if ($categoria === 'general' || (empty($categoria) && empty($tipo))) {
    $data = $nReporte->obtenerEstadisticasGenerales();
    $tipo = 'general';
} elseif ($categoria === 'finanzas') {
    if ($tipo === 'transacciones') {
        $data = $nFinanza->obtenerReporteTransacciones(
            $fecha_inicio ?: null,
            $fecha_fin ?: null,
            $tipo_transaccion_id ?: null
        );
    } elseif ($tipo === 'busqueda' && !empty($busqueda)) {
        $data = $nFinanza->buscarPorConcepto($busqueda, $fecha_inicio ?: null, $fecha_fin ?: null);
    }
} elseif ($categoria === 'escuelas') {
    if ($tipo === 'estudiantes') {
        if (!empty($busqueda)) {
            $data = $nEstudiante->buscarEstudianteConCursos($busqueda);
        } else {
            $data = $nEstudiante->buscarEstudianteConCursos('%');
        }
    } elseif ($tipo === 'docentes') {
        if (!empty($busqueda)) {
            $data = $nDocente->buscarDocenteConCursos($busqueda);
        } else {
            $docentes = $nDocente->listar();
            $data = [];
            foreach ($docentes as $doc) {
                $docente_id = (int)$doc['id'];
                $cursos = $nCurso->listarPorDocente($docente_id);
                $doc['cursos'] = $cursos;
                $data[] = $doc;
            }
        }
    } elseif ($tipo === 'cursos') {
        if (!empty($busqueda)) {
            $data = $nCurso->buscarCursoConEstudiantes($busqueda);
        } else {
            $cursos = $nCurso->listar();
            $data = [];
            foreach ($cursos as $curso) {
                $curso_id = (int)$curso['id'];
                $estudiantes = $nEstudiante->listarPorCurso($curso_id);
                $curso['estudiantes'] = $estudiantes;
                $data[] = $curso;
            }
        }
    } elseif ($tipo === 'asistencias') {
        if (!empty($curso_id_busqueda)) {
            $data = $nAsistencia->obtenerReporteDetalladoPorCurso($curso_id_busqueda);
        } elseif (!empty($busqueda)) {
            $data = $nAsistencia->buscarAsistenciasPorEstudiante($busqueda);
        } else {
            $data = $nReporte->obtenerAsistenciaPorCurso();
        }
    }
} elseif ($categoria === 'miembros') {
    $data = $nReporte->obtenerCrecimientoMiembrosPorMes();
    $tipo = 'miembros';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Sistema de Administración de Iglesia</title>
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
        @media (max-width: 991.98px) {
            .sidebar { display: none; }
        }
        .sidebar-container {
            margin-left: 16.666667%;
        }
        @media (max-width: 991.98px) {
            .sidebar-container { margin-left: 0; }
        }
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
        .btn-pdf {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
    </style>
    <?php echo generarEstilosNotificaciones(); ?>
</head>
<body>
    <!-- Offcanvas Menu (móviles) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuReportes" aria-labelledby="offcanvasMenuReportesLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuReportesLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('reportes'); ?>
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
                        <?php echo generarMenu('reportes'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuReportes" aria-controls="offcanvasMenuReportes">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-chart-line text-primary"></i> Reportes
                        </h4>
                        <div class="d-flex align-items-center">
                            <?php echo generarBotonNotificacionesNavbar(); ?>
                        </div>
                    </div>
                </nav>
                
                <div class="container-fluid py-4">
                    <!-- Filtros y Búsqueda -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-filter"></i> Filtros y Búsqueda
                            </h5>
                            <form method="GET" action="reportes.php" class="row g-3" id="formReportes">
                                <div class="col-md-3">
                                    <label class="form-label">Categoría</label>
                                    <select name="categoria" id="categoria" class="form-select" onchange="actualizarTipoReporte()">
                                        <option value="general" <?php echo $categoria === 'general' || empty($categoria) ? 'selected' : ''; ?>>General</option>
                                        <option value="escuelas" <?php echo $categoria === 'escuelas' ? 'selected' : ''; ?>>Escuelas</option>
                                        <option value="finanzas" <?php echo $categoria === 'finanzas' ? 'selected' : ''; ?>>Finanzas</option>
                                        <option value="miembros" <?php echo $categoria === 'miembros' ? 'selected' : ''; ?>>Miembros</option>
                                    </select>
                                </div>
                                
                                <?php if ($categoria === 'escuelas'): ?>
                                <div class="col-md-3" id="tipoEscuelas">
                                    <label class="form-label">Tipo de Reporte</label>
                                    <select name="tipo" class="form-select" onchange="this.form.submit()">
                                        <option value="">Seleccione...</option>
                                        <option value="estudiantes" <?php echo $tipo === 'estudiantes' ? 'selected' : ''; ?>>Estudiantes</option>
                                        <option value="docentes" <?php echo $tipo === 'docentes' ? 'selected' : ''; ?>>Docentes</option>
                                        <option value="cursos" <?php echo $tipo === 'cursos' ? 'selected' : ''; ?>>Cursos</option>
                                        <option value="asistencias" <?php echo $tipo === 'asistencias' ? 'selected' : ''; ?>>Asistencias</option>
                                    </select>
                                </div>
                                <?php elseif ($categoria === 'finanzas'): ?>
                                <div class="col-md-3" id="tipoFinanzas">
                                    <label class="form-label">Tipo de Reporte</label>
                                    <select name="tipo" class="form-select" onchange="this.form.submit()">
                                        <option value="">Seleccione...</option>
                                        <option value="transacciones" <?php echo $tipo === 'transacciones' ? 'selected' : ''; ?>>Transacciones</option>
                                        <option value="busqueda" <?php echo $tipo === 'busqueda' ? 'selected' : ''; ?>>Búsqueda por Concepto</option>
                                    </select>
                                </div>
                                <?php else: ?>
                                <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>">
                                <?php endif; ?>
                                
                                <?php if ($categoria === 'escuelas' && !empty($tipo)): ?>
                                <?php if ($tipo === 'asistencias'): ?>
                                <div class="col-md-3">
                                    <label class="form-label">Buscar por Curso</label>
                                    <select name="curso_id" class="form-select">
                                        <option value="">Todos los cursos</option>
                                        <?php foreach ($cursos_lista as $curso): ?>
                                            <option value="<?php echo $curso['id']; ?>" 
                                                    <?php echo $curso_id_busqueda == $curso['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($curso['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Buscar por Nombre de Estudiante</label>
                                    <input type="text" name="busqueda" class="form-control" 
                                           value="<?php echo htmlspecialchars($busqueda); ?>" 
                                           placeholder="Ingrese nombre...">
                                </div>
                                <?php else: ?>
                                <div class="col-md-3">
                                    <label class="form-label">Buscar por Nombre</label>
                                    <input type="text" name="busqueda" class="form-control" 
                                           value="<?php echo htmlspecialchars($busqueda); ?>" 
                                           placeholder="Ingrese nombre...">
                                </div>
                                <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if ($categoria === 'finanzas' && ($tipo === 'transacciones' || $tipo === 'busqueda')): ?>
                                <div class="col-md-2">
                                    <label class="form-label">Fecha Inicio</label>
                                    <input type="date" name="fecha_inicio" class="form-control" 
                                           value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Fecha Fin</label>
                                    <input type="date" name="fecha_fin" class="form-control" 
                                           value="<?php echo htmlspecialchars($fecha_fin); ?>">
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($tipo === 'transacciones'): ?>
                                <div class="col-md-3">
                                    <label class="form-label">Tipo de Transacción</label>
                                    <select name="tipo_transaccion_id" class="form-select">
                                        <option value="">Todos</option>
                                        <?php foreach ($tipos_transacciones as $tt): ?>
                                            <option value="<?php echo $tt['id']; ?>" 
                                                    <?php echo $tipo_transaccion_id == $tt['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tt['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                </div>
                                
                                <div class="col-md-2 d-flex align-items-end">
                                    <a href="generar_pdf.php?tipo=<?php echo urlencode($tipo); ?>&busqueda=<?php echo urlencode($busqueda); ?>&fecha_inicio=<?php echo urlencode($fecha_inicio); ?>&fecha_fin=<?php echo urlencode($fecha_fin); ?>&tipo_transaccion_id=<?php echo urlencode($tipo_transaccion_id); ?>" 
                                       class="btn btn-pdf w-100" target="_blank">
                                        <i class="fas fa-file-pdf"></i> PDF General
                                    </a>
                                </div>
                                
                                <?php if (!empty($busqueda) && in_array($tipo, ['estudiantes', 'docentes', 'cursos', 'asistencias'])): ?>
                                <div class="col-md-2 d-flex align-items-end">
                                    <a href="generar_pdf.php?tipo=<?php echo urlencode($tipo); ?>&busqueda=<?php echo urlencode($busqueda); ?>&modo=busqueda" 
                                       class="btn btn-info w-100" target="_blank" style="color: white;">
                                        <i class="fas fa-search"></i> PDF Búsqueda
                                    </a>
                                </div>
                                <?php endif; ?>
                                <?php if ($tipo === 'asistencias' && !empty($curso_id_busqueda)): ?>
                                <div class="col-md-2 d-flex align-items-end">
                                    <a href="generar_pdf.php?tipo=<?php echo urlencode($tipo); ?>&curso_id=<?php echo urlencode($curso_id_busqueda); ?>&modo=busqueda" 
                                       class="btn btn-info w-100" target="_blank" style="color: white;">
                                        <i class="fas fa-search"></i> PDF Búsqueda
                                    </a>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($tipo === 'asistencias'): ?>
                                <div class="col-md-2 d-flex align-items-end">
                                    <a href="generar_pdf.php?tipo=transaccional<?php echo !empty($curso_id_busqueda) ? '&curso_id=' . urlencode($curso_id_busqueda) : ''; ?><?php echo !empty($busqueda) ? '&busqueda=' . urlencode($busqueda) : ''; ?>" 
                                       class="btn btn-warning w-100" target="_blank" style="color: white;">
                                        <i class="fas fa-file-invoice"></i> PDF Transaccional
                                    </a>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Contenido del Reporte -->
                    <?php if ($categoria === 'general' || (empty($categoria) && empty($tipo))): ?>
                        <div class="row g-3 mb-4">
                            <?php
                                $cards = [
                                    ['label' => 'Miembros Totales', 'value' => $data['total_miembros'] ?? 0, 'icon' => 'fa-users', 'bg' => 'primary'],
                                    ['label' => 'Miembros Activos', 'value' => $data['miembros_activos'] ?? 0, 'icon' => 'fa-user-check', 'bg' => 'success'],
                                    ['label' => 'Células Activas', 'value' => $data['celulas_activas'] ?? 0, 'icon' => 'fa-people-arrows', 'bg' => 'info'],
                                    ['label' => 'Cursos Activos', 'value' => $data['cursos_activos'] ?? 0, 'icon' => 'fa-book', 'bg' => 'warning'],
                                    ['label' => 'Docentes Activos', 'value' => $data['docentes_activos'] ?? 0, 'icon' => 'fa-chalkboard-teacher', 'bg' => 'danger'],
                                    ['label' => 'Estudiantes Activos', 'value' => $data['estudiantes_activos'] ?? 0, 'icon' => 'fa-graduation-cap', 'bg' => 'secondary'],
                                ];
                            ?>
                            <?php foreach ($cards as $card): ?>
                                <div class="col-sm-6 col-lg-4">
                                    <div class="card">
                                        <div class="card-body d-flex align-items-center">
                                            <div class="me-3">
                                                <span class="badge bg-<?php echo $card['bg']; ?> p-3 rounded-circle">
                                                    <i class="fas <?php echo $card['icon']; ?>"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-muted mb-1 small text-uppercase"><?php echo $card['label']; ?></p>
                                                <h4 class="mb-0"><?php echo number_format((int)$card['value']); ?></h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                    <?php elseif ($categoria === 'finanzas' && ($tipo === 'transacciones' || $tipo === 'busqueda')): ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="fas fa-money-bill-wave text-primary"></i> 
                                        <?php echo $tipo === 'transacciones' ? 'Reporte de Transacciones' : 'Búsqueda: ' . htmlspecialchars($busqueda); ?>
                                    </h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Concepto</th>
                                                <th>Tipo</th>
                                                <th>Monto</th>
                                                <th>Miembro/Evento</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total_ingresos = 0;
                                            $total_egresos = 0;
                                            foreach ($data as $row): 
                                                if ($row['tipo'] === 'ingreso') {
                                                    $total_ingresos += $row['monto'];
                                                } else {
                                                    $total_egresos += $row['monto'];
                                                }
                                            ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                                    <td><?php echo htmlspecialchars($row['concepto']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $row['tipo'] === 'ingreso' ? 'success' : 'danger'; ?>">
                                                            <?php echo $row['tipo'] === 'ingreso' ? 'Ingreso' : 'Egreso'; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-end">$<?php echo number_format($row['monto'], 2); ?></td>
                                                    <td>
                                                        <?php 
                                                        if ($row['miembro_nombre']) {
                                                            echo htmlspecialchars($row['miembro_nombre'] . ' ' . ($row['miembro_apellido'] ?? ''));
                                                        } elseif ($row['evento_nombre']) {
                                                            echo htmlspecialchars($row['evento_nombre']);
                                                        } else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($data)): ?>
                                                <tr><td colspan="5" class="text-center text-muted">Sin registros</td></tr>
                                            <?php else: ?>
                                                <tr class="table-info fw-bold">
                                                    <td colspan="3" class="text-end">TOTALES:</td>
                                                    <td class="text-end text-success">Ingresos: $<?php echo number_format($total_ingresos, 2); ?></td>
                                                    <td class="text-end text-danger">Egresos: $<?php echo number_format($total_egresos, 2); ?></td>
                                                </tr>
                                                <tr class="table-secondary fw-bold">
                                                    <td colspan="3" class="text-end">BALANCE:</td>
                                                    <td colspan="2" class="text-end">
                                                        $<?php echo number_format($total_ingresos - $total_egresos, 2); ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($categoria === 'escuelas' && $tipo === 'estudiantes'): ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="fas fa-graduation-cap text-primary"></i> 
                                        Reporte de Estudiantes<?php echo !empty($busqueda) ? ': ' . htmlspecialchars($busqueda) : ''; ?>
                                    </h5>
                                </div>
                                <?php if (empty($data)): ?>
                                    <p class="text-muted text-center">Sin registros</p>
                                <?php else: ?>
                                    <?php foreach ($data as $estudiante): ?>
                                        <div class="card mb-3">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0">
                                                    <?php echo htmlspecialchars(($estudiante['nombres'] ?? '') . ' ' . ($estudiante['apellidos'] ?? '')); ?>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-2">
                                                    <div class="col-md-6"><strong>Email:</strong> <?php echo htmlspecialchars($estudiante['email'] ?? '-'); ?></div>
                                                    <div class="col-md-6"><strong>Teléfono:</strong> <?php echo htmlspecialchars($estudiante['telefono'] ?? '-'); ?></div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-md-12"><strong>Dirección:</strong> <?php echo htmlspecialchars($estudiante['direccion'] ?? '-'); ?></div>
                                                </div>
                                                <?php if (!empty($estudiante['cursos'])): ?>
                                                    <hr>
                                                    <h6>Cursos Inscritos:</h6>
                                                    <ul class="list-group">
                                                        <?php foreach ($estudiante['cursos'] as $curso): ?>
                                                            <li class="list-group-item">
                                                                <strong><?php echo htmlspecialchars($curso['nombre'] ?? ''); ?></strong>
                                                                <?php if ($curso['fecha_inscripcion']): ?>
                                                                    <small class="text-muted"> - Inscrito: <?php echo date('d/m/Y', strtotime($curso['fecha_inscripcion'])); ?></small>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <p class="text-muted">Sin cursos inscritos</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                    <?php elseif ($categoria === 'escuelas' && $tipo === 'docentes'): ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chalkboard-teacher text-primary"></i> 
                                        Reporte de Docentes<?php echo !empty($busqueda) ? ': ' . htmlspecialchars($busqueda) : ''; ?>
                                    </h5>
                                </div>
                                <?php if (empty($data)): ?>
                                    <p class="text-muted text-center">Sin registros</p>
                                <?php else: ?>
                                    <?php foreach ($data as $docente): ?>
                                        <div class="card mb-3">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="mb-0">
                                                    <?php echo htmlspecialchars(($docente['nombres'] ?? '') . ' ' . ($docente['apellidos'] ?? '')); ?>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-2">
                                                    <div class="col-md-6"><strong>Especialidad:</strong> <?php echo htmlspecialchars($docente['especialidad'] ?? '-'); ?></div>
                                                    <div class="col-md-6"><strong>Email:</strong> <?php echo htmlspecialchars($docente['email'] ?? '-'); ?></div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-md-6"><strong>Teléfono:</strong> <?php echo htmlspecialchars($docente['telefono'] ?? '-'); ?></div>
                                                    <div class="col-md-6"><strong>Dirección:</strong> <?php echo htmlspecialchars($docente['direccion'] ?? '-'); ?></div>
                                                </div>
                                                <?php if (!empty($docente['cursos'])): ?>
                                                    <hr>
                                                    <h6>Cursos que Imparte:</h6>
                                                    <ul class="list-group">
                                                        <?php foreach ($docente['cursos'] as $curso): ?>
                                                            <li class="list-group-item">
                                                                <strong><?php echo htmlspecialchars($curso['nombre'] ?? ''); ?></strong>
                                                                <?php if ($curso['fecha_inicio']): ?>
                                                                    <small class="text-muted"> - Inicio: <?php echo date('d/m/Y', strtotime($curso['fecha_inicio'])); ?></small>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <p class="text-muted">Sin cursos asignados</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                    <?php elseif ($categoria === 'escuelas' && $tipo === 'cursos'): ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="fas fa-book text-primary"></i> 
                                        Reporte de Cursos<?php echo !empty($busqueda) ? ': ' . htmlspecialchars($busqueda) : ''; ?>
                                    </h5>
                                </div>
                                <?php if (empty($data)): ?>
                                    <p class="text-muted text-center">Sin registros</p>
                                <?php else: ?>
                                    <?php foreach ($data as $curso): ?>
                                        <div class="card mb-3">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0">
                                                    <?php echo htmlspecialchars($curso['nombre'] ?? ''); ?>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-2">
                                                    <div class="col-md-6"><strong>Docente:</strong> <?php echo htmlspecialchars(($curso['docente_nombre'] ?? '') . ' ' . ($curso['docente_apellido'] ?? 'Sin asignar')); ?></div>
                                                    <div class="col-md-6"><strong>Capacidad:</strong> <?php echo number_format($curso['capacidad_maxima'] ?? 0); ?></div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-md-6"><strong>Fecha Inicio:</strong> <?php echo $curso['fecha_inicio'] ? date('d/m/Y', strtotime($curso['fecha_inicio'])) : '-'; ?></div>
                                                    <div class="col-md-6"><strong>Fecha Fin:</strong> <?php echo $curso['fecha_fin'] ? date('d/m/Y', strtotime($curso['fecha_fin'])) : '-'; ?></div>
                                                </div>
                                                <?php if (!empty($curso['descripcion'])): ?>
                                                    <div class="mb-2"><strong>Descripción:</strong> <?php echo htmlspecialchars($curso['descripcion']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($curso['estudiantes'])): ?>
                                                    <hr>
                                                    <h6>Estudiantes Inscritos (<?php echo count($curso['estudiantes']); ?>):</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-hover">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Nombre</th>
                                                                    <th>Email</th>
                                                                    <th>Teléfono</th>
                                                                    <th>Fecha de Inscripción</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($curso['estudiantes'] as $est): ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars(($est['nombres'] ?? '') . ' ' . ($est['apellidos'] ?? '')); ?></td>
                                                                        <td><?php echo htmlspecialchars($est['email'] ?? '-'); ?></td>
                                                                        <td><?php echo htmlspecialchars($est['telefono'] ?? '-'); ?></td>
                                                                        <td><?php echo !empty($est['fecha_inscripcion']) ? date('d/m/Y', strtotime($est['fecha_inscripcion'])) : '-'; ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="text-muted">Sin estudiantes inscritos</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                    <?php elseif ($categoria === 'escuelas' && $tipo === 'asistencias'): ?>
                        <?php if (!empty($curso_id_busqueda)): ?>
                            <!-- Reporte de asistencias por curso -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">
                                            <i class="fas fa-clipboard-check text-primary"></i> 
                                            Reporte de Asistencias por Curso
                                        </h5>
                                    </div>
                                    <?php if (empty($data)): ?>
                                        <p class="text-muted text-center">Sin registros</p>
                                    <?php else: ?>
                                        <?php foreach ($data as $curso): ?>
                                            <div class="card mb-3">
                                                <div class="card-header bg-info text-white">
                                                    <h6 class="mb-0">
                                                        <?php echo htmlspecialchars($curso['curso_nombre'] ?? ''); ?>
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row mb-2">
                                                        <div class="col-md-12">
                                                            <strong>Docente a cargo:</strong> 
                                                            <?php 
                                                            $docente_nombre = trim(($curso['docente_nombre'] ?? '') . ' ' . ($curso['docente_apellido'] ?? ''));
                                                            echo htmlspecialchars(empty($docente_nombre) ? 'Sin asignar' : $docente_nombre); 
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <?php if (!empty($curso['estudiantes'])): ?>
                                                        <hr>
                                                        <h6>Estudiantes y Estadísticas:</h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-hover">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Estudiante</th>
                                                                        <th class="text-center">Clases Registradas</th>
                                                                        <th class="text-center">Presente</th>
                                                                        <th class="text-center">Ausente</th>
                                                                        <th class="text-center">Retraso</th>
                                                                        <th class="text-center">% Asistencia</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($curso['estudiantes'] as $est): ?>
                                                                        <tr>
                                                                            <td><?php echo htmlspecialchars(($est['nombres'] ?? '') . ' ' . ($est['apellidos'] ?? '')); ?></td>
                                                                            <td class="text-center">
                                                                                <strong><?php echo number_format((int)($est['total_clases'] ?? $est['total_registros'] ?? 0)); ?></strong>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <span class="badge bg-success">
                                                                                    <?php echo number_format((int)($est['asistencias'] ?? 0)); ?>
                                                                                </span>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <span class="badge bg-danger">
                                                                                    <?php echo number_format((int)($est['ausencias'] ?? 0)); ?>
                                                                                </span>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <span class="badge bg-warning text-dark">
                                                                                    <?php echo number_format((int)($est['retrasos'] ?? 0)); ?>
                                                                                </span>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <strong><?php echo number_format((float)($est['porcentaje_asistencia'] ?? 0), 2); ?>%</strong>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php else: ?>
                                                        <p class="text-muted">Sin estudiantes inscritos</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php elseif (!empty($busqueda)): ?>
                            <!-- Reporte de asistencias por estudiante -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">
                                            <i class="fas fa-clipboard-check text-primary"></i> 
                                            Asistencias: <?php echo htmlspecialchars($busqueda); ?>
                                        </h5>
                                    </div>
                                    <?php if (empty($data)): ?>
                                        <p class="text-muted text-center">Sin registros</p>
                                    <?php else: ?>
                                        <?php foreach ($data as $estudiante): ?>
                                            <div class="card mb-3">
                                                <div class="card-header bg-warning text-dark">
                                                    <h6 class="mb-0">
                                                        <?php echo htmlspecialchars(($estudiante['nombres'] ?? '') . ' ' . ($estudiante['apellidos'] ?? '')); ?>
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row mb-2">
                                                        <div class="col-md-6"><strong>Email:</strong> <?php echo htmlspecialchars($estudiante['email'] ?? '-'); ?></div>
                                                        <div class="col-md-6"><strong>Teléfono:</strong> <?php echo htmlspecialchars($estudiante['telefono'] ?? '-'); ?></div>
                                                    </div>
                                                    <?php if (!empty($estudiante['asistencias'])): ?>
                                                        <hr>
                                                        <h6>Registros de Asistencia:</h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Fecha</th>
                                                                        <th>Curso</th>
                                                                        <th>Registrado por</th>
                                                                        <th>Estado</th>
                                                                        <th>Observación</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($estudiante['asistencias'] as $asist): ?>
                                                                        <?php 
                                                                        $estado_asist = $asist['estado_asistencia'] ?? 'ausente';
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
                                                                        <tr>
                                                                            <td><?php echo date('d/m/Y', strtotime($asist['fecha'])); ?></td>
                                                                            <td><?php echo htmlspecialchars($asist['curso_nombre'] ?? ''); ?></td>
                                                                            <td><?php echo htmlspecialchars(($asist['docente_nombre'] ?? '') . ' ' . ($asist['docente_apellido'] ?? 'N/A')); ?></td>
                                                                            <td>
                                                                                <span class="badge bg-<?php echo $badge_class; ?>">
                                                                                    <?php echo $texto; ?>
                                                                                </span>
                                                                            </td>
                                                                            <td><?php echo htmlspecialchars($asist['detalle_observacion'] ?? '-'); ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php else: ?>
                                                        <p class="text-muted">Sin registros de asistencia</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Reporte general de asistencias -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0"><i class="fas fa-clipboard-check text-primary"></i> Asistencia por Curso</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Curso</th>
                                                    <th>Registros</th>
                                                    <th>Presentes</th>
                                                    <th>Ausentes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($data as $row): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['curso'] ?? ''); ?></td>
                                                        <td><?php echo number_format((int)($row['total_registros'] ?? 0)); ?></td>
                                                        <td>
                                                            <span class="badge bg-success">
                                                                <?php echo number_format((int)($row['presentes'] ?? 0)); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?php echo number_format((int)($row['ausentes'] ?? 0)); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($data)): ?>
                                                    <tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    <?php elseif ($categoria === 'miembros'): ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Crecimiento de Miembros (últimos 12 meses)</h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Mes</th>
                                                <th>Nuevos Miembros</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data as $row): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['mes'] ?? ''); ?></td>
                                                    <td><?php echo number_format((int)($row['cantidad'] ?? 0)); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($data)): ?>
                                                <tr><td colspan="2" class="text-center text-muted">Sin registros</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function actualizarTipoReporte() {
    var categoria = document.getElementById('categoria').value;
    var form = document.getElementById('formReportes');
    
    // Limpiar tipo cuando cambia la categoría
    var tipoInput = form.querySelector('select[name="tipo"]');
    if (tipoInput) {
        tipoInput.value = '';
    }
    
    // Enviar formulario para actualizar los selectores
    form.submit();
}
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>
