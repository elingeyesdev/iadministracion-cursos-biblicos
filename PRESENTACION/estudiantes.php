<?php

require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once '../NEGOCIO/nEstudiante.php';
require_once '../NEGOCIO/nMiembro.php';
require_once '../NEGOCIO/nCurso.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

$auth = new AuthController();
$auth->requireLogin();

// Verificar privilegios
if (!auth::tienePrivilegio('estudiantes_ver')) {
    header('Location: dashboard.php');
    exit();
}

$nEstudiante = new nEstudiante();
$nMiembro = new nMiembro();
$nCurso = new nCurso();

// Obtener mensaje de URL si existe (después de redirección)
$mensaje = '';
$tipo_mensaje = '';
if (isset($_GET['mensaje'])) {
    $mensaje = urldecode($_GET['mensaje']);
    $tipo_mensaje = $_GET['tipo'] ?? 'success';
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
                case 'crear_estudiante':
                    if (!auth::tienePrivilegio('estudiantes_crear')) {
                        header('Location: estudiantes.php');
                        exit();
                    }
                    // Crear estudiante desde cero
                    $resultado = $nEstudiante->crearEstudiante(
                        $_POST['nombres'],
                        $_POST['apellidos'],
                        $_POST['fecha_nacimiento'] ?? null,
                        $_POST['telefono'] ?? '',
                        $_POST['email'] ?? '',
                        $_POST['direccion'] ?? '',
                        null, // No asociar a miembro
                        isset($_POST['estado']) ? 1 : 0
                    );
                    
                    if ($resultado['exito']) {
                        // Si hay una solicitud asociada, marcarla como procesada
                        if (!empty($_POST['solicitud_id'])) {
                            require_once '../DATOS/dSolicitudEstudiante.php';
                            $dSolicitud = new dSolicitudEstudiante();
                            $solicitud = $dSolicitud->obtenerPorId((int)$_POST['solicitud_id']);
                            if ($solicitud) {
                                $dSolicitud->marcarComoProcesado((int)$_POST['solicitud_id']);
                            }
                        }
                        
                        // Si se especificó un curso, inscribirlo
                        if (!empty($_POST['curso_id']) && isset($resultado['id'])) {
                            $inscripcion = $nEstudiante->inscribirEnCurso(
                                $resultado['id'],
                                $_POST['curso_id'],
                                $_POST['fecha_inscripcion'] ?? null
                            );
                            if ($inscripcion['exito']) {
                                $mensaje = $resultado['mensaje'] . ' e inscrito en el curso exitosamente';
                                $tipo_mensaje = 'success';
                            } else {
                                $mensaje = $resultado['mensaje'] . ' pero hubo un error al inscribirlo en el curso: ' . $inscripcion['mensaje'];
                                $tipo_mensaje = 'warning';
                            }
                        } else {
                            $mensaje = $resultado['mensaje'];
                            $tipo_mensaje = 'success';
                        }
                        // Redirigir para evitar reenvío del formulario
                        header('Location: estudiantes.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipo_mensaje);
                        exit;
                    } else {
                        $mensaje = $resultado['mensaje'];
                        $tipo_mensaje = 'danger';
                    }
                    break;
                    
                case 'actualizar_estudiante':
                    if (!auth::tienePrivilegio('estudiantes_editar')) {
                        header('Location: estudiantes.php');
                        exit();
                    }
                    // Actualizar datos del estudiante
                    $resultado = $nEstudiante->actualizarEstudiante(
                        $_POST['id'],
                        $_POST['nombres'],
                        $_POST['apellidos'],
                        $_POST['fecha_nacimiento'] ?? null,
                        $_POST['telefono'] ?? '',
                        $_POST['email'] ?? '',
                        $_POST['direccion'] ?? '',
                        null, // No asociar a miembro
                        isset($_POST['estado']) ? 1 : 0
                    );
                    
                    if ($resultado['exito']) {
                        // Manejar actualización del curso
                        require_once '../DATOS/dEstudiante.php';
                        require_once '../DATOS/dConexion.php';
                        $dEstudiante = new dEstudiante();
                        
                        // Obtener curso actual del estudiante
                        $cursos_actuales = $dEstudiante->obtenerCursosPorEstudiante($_POST['id']);
                        $curso_actual_id = !empty($cursos_actuales) ? (int)$cursos_actuales[0]['curso_id'] : null;
                        
                        $curso_nuevo_id = !empty($_POST['curso_id']) ? (int)$_POST['curso_id'] : null;
                        
                        // Si hay un curso nuevo seleccionado
                        if ($curso_nuevo_id) {
                            // Si es diferente al curso actual, actualizar
                            if ($curso_actual_id !== $curso_nuevo_id) {
                                // Desactivar inscripciones anteriores
                                if ($curso_actual_id) {
                                    $conn = new dConexion();
                                    $conexion = $conn->Conectar();
                                    $sql_desactivar = "UPDATE estudiantes_cursos SET estado = 0 WHERE estudiante_id = " . (int)$_POST['id'] . " AND estado = 1";
                                    mysqli_query($conexion, $sql_desactivar);
                                    mysqli_close($conexion);
                                }
                                
                                // Inscribir en el nuevo curso
                                $inscripcion = $nEstudiante->inscribirEnCurso(
                                    $_POST['id'],
                                    $curso_nuevo_id,
                                    $_POST['fecha_inscripcion'] ?? null
                                );
                                
                                if ($inscripcion['exito']) {
                                    $mensaje = $resultado['mensaje'] . ' y curso actualizado exitosamente';
                                } else {
                                    $mensaje = $resultado['mensaje'] . ' pero hubo un error al actualizar el curso: ' . $inscripcion['mensaje'];
                                    $tipo_mensaje = 'warning';
                                }
                            } else {
                                // Mismo curso, solo actualizar fecha si se proporciona
                                if (!empty($_POST['fecha_inscripcion'])) {
                                    $conn = new dConexion();
                                    $conexion = $conn->Conectar();
                                    $fecha_escaped = mysqli_real_escape_string($conexion, $_POST['fecha_inscripcion']);
                                    $sql_update_fecha = "UPDATE estudiantes_cursos SET fecha_inscripcion = '$fecha_escaped' WHERE estudiante_id = " . (int)$_POST['id'] . " AND curso_id = $curso_nuevo_id AND estado = 1";
                                    mysqli_query($conexion, $sql_update_fecha);
                                    mysqli_close($conexion);
                                }
                                $mensaje = $resultado['mensaje'];
                            }
                        } else {
                            // Si no hay curso seleccionado, desactivar inscripciones actuales
                            if ($curso_actual_id) {
                                $conn = new dConexion();
                                $conexion = $conn->Conectar();
                                $sql_desactivar = "UPDATE estudiantes_cursos SET estado = 0 WHERE estudiante_id = " . (int)$_POST['id'] . " AND estado = 1";
                                mysqli_query($conexion, $sql_desactivar);
                                mysqli_close($conexion);
                                $mensaje = $resultado['mensaje'] . ' e inscripción de curso removida';
                            } else {
                                $mensaje = $resultado['mensaje'];
                            }
                        }
                        
                        if (!isset($tipo_mensaje)) {
                            $tipo_mensaje = 'success';
                        }
                        
                        // Redirigir para evitar reenvío del formulario
                        header('Location: estudiantes.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipo_mensaje);
                        exit;
                    } else {
                        $mensaje = $resultado['mensaje'];
                        $tipo_mensaje = 'danger';
                    }
                break;
                
                case 'inscribir_curso':
                    // Inscribir estudiante existente en curso
                    $resultado = $nEstudiante->inscribirEnCurso(
                        $_POST['estudiante_id'],
                        $_POST['curso_id'],
                        $_POST['fecha_inscripcion'] ?? null
                    );
                    
                    if ($resultado['exito']) {
                        $mensaje = $resultado['mensaje'];
                        $tipo_mensaje = 'success';
                        // Redirigir para evitar reenvío del formulario
                        header('Location: estudiantes.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipo_mensaje);
                        exit;
                    } else {
                        $mensaje = $resultado['mensaje'];
                        $tipo_mensaje = 'danger';
                    }
                break;
                
            case 'eliminar':
                    if (!auth::tienePrivilegio('estudiantes_eliminar')) {
                        header('Location: estudiantes.php');
                        exit();
                    }
                    // Obtener datos del estudiante primero
                    $estudiante = $nEstudiante->obtenerEstudiantePorId($_POST['id']);
                    if ($estudiante) {
                        // Eliminar estudiante (soft delete)
                        $resultado = $nEstudiante->actualizarEstudiante(
                            $_POST['id'],
                            $estudiante['nombres'],
                            $estudiante['apellidos'],
                            $estudiante['fecha_nacimiento'] ?? null,
                            $estudiante['telefono'] ?? '',
                            $estudiante['email'] ?? '',
                            $estudiante['direccion'] ?? '',
                            $estudiante['miembro_id'] ?? null,
                            0 // Estado inactivo
                        );
                        if ($resultado['exito']) {
                    $mensaje = 'Estudiante eliminado exitosamente';
                    $tipo_mensaje = 'success';
                            // Redirigir para evitar reenvío del formulario
                            header('Location: estudiantes.php?mensaje=' . urlencode($mensaje) . '&tipo=' . $tipo_mensaje);
                            exit;
                        } else {
                            $mensaje = 'Error al eliminar el estudiante';
                            $tipo_mensaje = 'danger';
                        }
                } else {
                        $mensaje = 'Estudiante no encontrado';
                    $tipo_mensaje = 'danger';
                }
                break;
        }
        }
    } catch (Exception $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}

// Procesar búsqueda
$terminoBusqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
if (!empty($terminoBusqueda)) {
    // Si es un número, buscar por ID, sino por nombre
    if (is_numeric($terminoBusqueda)) {
        // Usar buscarPorNombre que también busca por ID y devuelve el formato correcto
        $estudiantes = $nEstudiante->buscarPorNombre($terminoBusqueda);
    } else {
        $estudiantes = $nEstudiante->buscarPorNombre($terminoBusqueda);
    }
} else {
    $estudiantes = $nEstudiante->listar();
}

$miembros = $nMiembro->listar();
$cursos = $nCurso->listar();

// Obtener datos para editar si se solicita
$estudiante_editar = null;
if (isset($_GET['accion']) && $_GET['accion'] == 'editar' && isset($_GET['id'])) {
    $estudiante_editar = $nEstudiante->obtenerEstudiantePorId($_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Estudiantes - Iglesia</title>
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
    </style>
    <?php echo generarEstilosNotificaciones(); ?>
</head>
<body>
    <!-- Offcanvas Menu (móviles) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuEstudiantes" aria-labelledby="offcanvasMenuEstudiantesLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuEstudiantesLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('estudiantes'); ?>
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
                        <?php echo generarMenu('estudiantes'); ?>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuEstudiantes" aria-controls="offcanvasMenuEstudiantes">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-user-graduate text-primary"></i> Gestión de Estudiantes
                        </h4>
                        <div class="d-flex align-items-center">
                            <?php echo generarBotonNotificacionesNavbar(); ?>
                        </div>
                    </div>
                </nav>

                <!-- Content -->
                <div class="container-fluid py-4">
                <!-- Mensajes -->
                <?php if ($mensaje): ?>
                    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($mensaje); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Acciones Rápidas -->
                <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Lista de Estudiantes</h5>
                                <div class="btn-group">
                                    <?php if (tieneAcceso('estudiantes_crear')): ?>
                                    <button type="button" class="btn btn-success" id="btnCompartirFormulario" title="Compartir formulario de registro">
                                        <i class="fas fa-share-alt"></i> Compartir Formulario
                                    </button>
                                    <button type="button" class="btn btn-primary" id="btnNuevoEstudiante">
                                        <i class="fas fa-plus"></i> Nuevo Estudiante
                                    </button>
                                    <?php endif; ?>
                                </div>
                    </div>
                    <!-- Formulario de búsqueda -->
                    <form method="GET" class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="buscar" 
                                   placeholder="Buscar por ID o nombre..." 
                                   value="<?php echo htmlspecialchars($terminoBusqueda); ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <?php if (!empty($terminoBusqueda)): ?>
                                <a href="estudiantes.php" class="btn btn-outline-danger">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                    </div>

                    <!-- Tabla de Estudiantes -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Estudiante</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Cursos Inscritos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estudiantes as $estudiante): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($estudiante['estudiante_id'] ?? ''); ?></td>
                                            <td>
                                                <?php 
                                                $nombreEstudiante = trim(($estudiante['estudiante_nombre'] ?? '') . ' ' . ($estudiante['estudiante_apellido'] ?? ''));
                                                echo htmlspecialchars($nombreEstudiante); 
                                                ?>
                                                <?php if (!empty($estudiante['miembro_id'])): ?>
                                                    <small class="text-muted d-block">(Miembro asociado)</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($estudiante['email'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($estudiante['telefono'] ?? ''); ?></td>
                                            <td>
                                                <?php 
                                                $cursos_inscritos = trim($estudiante['cursos_inscritos'] ?? '');
                                                $total_cursos = (int)($estudiante['total_cursos'] ?? 0);
                                                
                                                // Debug temporal - remover después
                                                // var_dump($estudiante['estudiante_id'], $cursos_inscritos, $total_cursos);
                                                
                                                if (!empty($cursos_inscritos) && $total_cursos > 0): 
                                                ?>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($cursos_inscritos); ?></span>
                                                    <small class="text-muted d-block"><?php echo $total_cursos; ?> curso(s)</small>
                                                <?php elseif ($total_cursos > 0): ?>
                                                    <span class="badge bg-warning"><?php echo $total_cursos; ?> curso(s) inscrito(s)</span>
                                                    <small class="text-muted d-block">Sin nombre de curso</small>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin cursos</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if (tieneAcceso('estudiantes_editar')): ?>
                                                    <a href="?accion=editar&id=<?php echo $estudiante['estudiante_id']; ?>" class="btn btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                    <?php endif; ?>
                                                    <?php if (tieneAcceso('estudiantes_eliminar')): ?>
                                                    <button type="button" class="btn btn-danger" onclick="confirmarEliminar(<?php echo $estudiante['estudiante_id']; ?>)" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($estudiantes)): ?>
                                        <tr><td colspan="7" class="text-center text-muted">Sin registros</td></tr>
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
        </div>
    </div>

    <!-- Modal Estudiante -->
    <div class="modal fade" id="modalEstudiante" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $estudiante_editar ? 'Editar Estudiante' : 'Nuevo Estudiante'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formEstudiante">
                    <div class="modal-body">
                        <?php if ($estudiante_editar): ?>
                            <input type="hidden" name="accion" value="actualizar_estudiante">
                            <input type="hidden" name="id" value="<?php echo $estudiante_editar['id']; ?>">
                        <?php else: ?>
                            <input type="hidden" name="accion" value="crear_estudiante">
                        <?php endif; ?>

                        <!-- Búsqueda de miembro para copiar datos -->
                        <div class="mb-3">
                            <label class="form-label">Buscar miembro para copiar datos (Opcional)</label>
                            <div class="position-relative">
                                <input type="text" 
                                       class="form-control" 
                                       id="buscar_miembro" 
                                       placeholder="Escriba el nombre del miembro..." 
                                       autocomplete="off">
                                <div id="resultados_busqueda" class="list-group position-absolute w-100" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto; background: white; border: 1px solid #ddd; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); margin-top: 2px;"></div>
                            </div>
                            <small class="text-muted">Escriba al menos 2 caracteres para buscar. Seleccione un miembro para copiar sus datos automáticamente.</small>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombres" class="form-label">Nombres *</label>
                                <input type="text" class="form-control" id="nombres" name="nombres" 
                                       value="<?php echo $estudiante_editar ? htmlspecialchars($estudiante_editar['nombres'] ?? '') : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellidos" class="form-label">Apellidos *</label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                       value="<?php echo $estudiante_editar ? htmlspecialchars($estudiante_editar['apellidos'] ?? '') : ''; ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $estudiante_editar ? htmlspecialchars($estudiante_editar['email'] ?? '') : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo $estudiante_editar ? htmlspecialchars($estudiante_editar['telefono'] ?? '') : ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                       value="<?php echo $estudiante_editar ? htmlspecialchars($estudiante_editar['fecha_nacimiento'] ?? '') : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="1" <?php echo ($estudiante_editar && ($estudiante_editar['estado'] ?? 1)) ? 'selected' : ($estudiante_editar ? '' : 'selected'); ?>>Activo</option>
                                    <option value="0" <?php echo ($estudiante_editar && !($estudiante_editar['estado'] ?? 1)) ? 'selected' : ''; ?>>Inactivo</option>
                            </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?php echo $estudiante_editar ? htmlspecialchars($estudiante_editar['direccion'] ?? '') : ''; ?>">
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label for="curso_id" class="form-label">Inscribir en Curso (Opcional)</label>
                            <select class="form-select" id="curso_id" name="curso_id">
                                <option value="">No inscribir en curso ahora</option>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?php echo $curso['id']; ?>">
                                        <?php echo htmlspecialchars($curso['nombre'] ?? ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3" id="fecha_inscripcion_container" style="display: none;">
                            <label for="fecha_inscripcion" class="form-label">Fecha de Inscripción</label>
                            <input type="date" class="form-control" id="fecha_inscripcion" name="fecha_inscripcion" 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $estudiante_editar ? 'Actualizar' : 'Guardar'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales para búsqueda
        let timeoutBusqueda = null;
        const inputBuscar = document.getElementById('buscar_miembro');
        const resultadosBusqueda = document.getElementById('resultados_busqueda');
        
        // Auto-abrir modal si se está editando
        <?php if ($estudiante_editar): ?>
            document.addEventListener('DOMContentLoaded', function() {
                // Asegurar que no haya modales abiertos
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                
                const modalElement = document.getElementById('modalEstudiante');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
        <?php endif; ?>
        
        // Función para limpiar y abrir el modal
        function limpiarYAbrirModalEstudiante() {
            const modalEstudiante = document.getElementById('modalEstudiante');
            if (!modalEstudiante) return;
            const form = document.getElementById('formEstudiante');
            if (!form) return;
            
            // Limpiar formulario
            form.reset();
            
            // Remover ID si existe
            const hiddenId = form.querySelector('input[name="id"]');
            if (hiddenId) hiddenId.remove();
            
            // Cambiar acción a crear
            const hiddenAction = form.querySelector('input[name="accion"]');
            if (hiddenAction) hiddenAction.value = 'crear_estudiante';
            
            // Cambiar título
            const modalTitle = modalEstudiante.querySelector('.modal-title');
            if (modalTitle) modalTitle.textContent = 'Nuevo Estudiante';
            
            // Cambiar texto del botón
            const btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
            }
            
            const selectCurso = form.querySelector('#curso_id');
            if (selectCurso) selectCurso.value = '';
            
            // Ocultar fecha de inscripción
            const fechaContainer = document.getElementById('fecha_inscripcion_container');
            if (fechaContainer) fechaContainer.style.display = 'none';
            
            // Limpiar búsqueda de miembro
            const inputBuscar = document.getElementById('buscar_miembro');
            if (inputBuscar) inputBuscar.value = '';
            const resultadosBusqueda = document.getElementById('resultados_busqueda');
            if (resultadosBusqueda) {
                resultadosBusqueda.style.display = 'none';
                resultadosBusqueda.innerHTML = '';
            }
            
            // Limpiar URL si tiene parámetros
            if (window.location.search) {
                window.history.replaceState({}, '', 'estudiantes.php');
            }
            
            // Abrir modal
            const modal = new bootstrap.Modal(modalEstudiante);
            modal.show();
        }
        
        const btnNuevoEstudiante = document.getElementById('btnNuevoEstudiante');
        if (btnNuevoEstudiante) {
            btnNuevoEstudiante.addEventListener('click', function(e) {
                e.preventDefault();
                // Cerrar cualquier modal abierto primero
                const modalExistente = document.getElementById('modalEstudiante');
                if (modalExistente) {
                    const modalInstance = bootstrap.Modal.getInstance(modalExistente);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    // Limpiar backdrop si existe
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
                // Limpiar URL si tiene parámetros
                if (window.location.search) {
                    window.history.replaceState({}, '', 'estudiantes.php');
                }
                // Pequeño delay para asegurar que el modal anterior se cerró
                setTimeout(function() {
                    limpiarYAbrirModalEstudiante();
                }, 100);
            });
        }
        
        const modalEstudiante = document.getElementById('modalEstudiante');
        if (modalEstudiante) {
            // Limpiar cuando se cierra el modal
            modalEstudiante.addEventListener('hidden.bs.modal', function() {
                // Limpiar backdrop si queda alguno
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                
                const form = document.getElementById('formEstudiante');
                if (form) {
                    form.reset();
                    const hiddenId = form.querySelector('input[name="id"]');
                    if (hiddenId) hiddenId.remove();
                    const hiddenAction = form.querySelector('input[name="accion"]');
                    if (hiddenAction) hiddenAction.value = 'crear_estudiante';
                    const modalTitle = modalEstudiante.querySelector('.modal-title');
                    if (modalTitle) modalTitle.textContent = 'Nuevo Estudiante';
                    const btnSubmit = form.querySelector('button[type="submit"]');
                    if (btnSubmit) {
                        const icon = btnSubmit.querySelector('i');
                        if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                        else btnSubmit.textContent = 'Guardar';
                    }
                    // Limpiar select de curso
                    const selectCurso = form.querySelector('#curso_id');
                    if (selectCurso) selectCurso.value = '';
                    // Ocultar fecha de inscripción
                    const fechaContainer = document.getElementById('fecha_inscripcion_container');
                    if (fechaContainer) fechaContainer.style.display = 'none';
                }
                // Limpiar URL si tiene parámetros de edición
                if (window.location.search.includes('accion=editar')) {
                    window.history.replaceState({}, '', 'estudiantes.php');
                }
            });
        }
        
        // Limpiar formulario al abrir modal para nuevo estudiante
        const tieneEstudianteEditar = <?php echo $estudiante_editar ? 'true' : 'false'; ?>;
        document.getElementById('modalEstudiante')?.addEventListener('show.bs.modal', function(e) {
            // No limpiar si viene de una notificación
            const modalElement = e.target;
            if (modalElement.getAttribute('data-from-notification') === 'true') {
                return; // No limpiar el formulario si viene de una notificación
            }
            
            if (!tieneEstudianteEditar) {
                document.getElementById('formEstudiante').reset();
                document.getElementById('fecha_inscripcion_container').style.display = 'none';
                document.getElementById('curso_id').value = '';
                // Limpiar búsqueda de miembro
                if (inputBuscar) {
                    inputBuscar.value = '';
                }
                if (resultadosBusqueda) {
                    resultadosBusqueda.style.display = 'none';
                    resultadosBusqueda.innerHTML = '';
                }
            }
        });
        
        // Mostrar/ocultar fecha de inscripción según si se selecciona curso
        document.getElementById('curso_id')?.addEventListener('change', function() {
            const fechaContainer = document.getElementById('fecha_inscripcion_container');
            if (this.value) {
                fechaContainer.style.display = 'block';
            } else {
                fechaContainer.style.display = 'none';
            }
        });
        
        // Inicializar visibilidad de fecha de inscripción
        document.addEventListener('DOMContentLoaded', function() {
            const cursoSelect = document.getElementById('curso_id');
            const fechaContainer = document.getElementById('fecha_inscripcion_container');
            if (cursoSelect && cursoSelect.value) {
                fechaContainer.style.display = 'block';
            }
        });
        
        // Función para confirmar eliminación
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de eliminar este estudiante?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Búsqueda de miembros en tiempo real
        
        if (inputBuscar) {
            inputBuscar.addEventListener('input', function() {
                const termino = this.value.trim();
                
                // Limpiar timeout anterior
                if (timeoutBusqueda) {
                    clearTimeout(timeoutBusqueda);
                }
                
                // Ocultar resultados si el término es muy corto
                if (termino.length < 2) {
                    resultadosBusqueda.style.display = 'none';
                    resultadosBusqueda.innerHTML = '';
                    return;
                }
                
                // Esperar 300ms antes de buscar (debounce)
                timeoutBusqueda = setTimeout(function() {
                    buscarMiembros(termino);
                }, 300);
            });
            
            // Ocultar resultados al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (!inputBuscar.contains(e.target) && !resultadosBusqueda.contains(e.target)) {
                    resultadosBusqueda.style.display = 'none';
                }
            });
        }
        
        function buscarMiembros(termino) {
            fetch('ajax_buscar_miembros.php?q=' + encodeURIComponent(termino))
                .then(response => response.json())
                .then(data => {
                    mostrarResultados(data);
                })
                .catch(error => {
                    console.error('Error al buscar miembros:', error);
                    resultadosBusqueda.style.display = 'none';
                });
        }
        
        function mostrarResultados(miembros) {
            resultadosBusqueda.innerHTML = '';
            
            if (miembros.length === 0) {
                resultadosBusqueda.innerHTML = '<div class="list-group-item text-muted">No se encontraron miembros</div>';
                resultadosBusqueda.style.display = 'block';
                return;
            }
            
            miembros.forEach(function(miembro) {
                const item = document.createElement('a');
                item.href = '#';
                item.className = 'list-group-item list-group-item-action';
                item.innerHTML = '<strong>' + miembro.nombre + '</strong><br><small class="text-muted">' + 
                                (miembro.email || 'Sin email') + ' | ' + 
                                (miembro.telefono || 'Sin teléfono') + '</small>';
                
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    copiarDatosMiembro(miembro);
                });
                
                resultadosBusqueda.appendChild(item);
            });
            
            resultadosBusqueda.style.display = 'block';
        }
        
        function copiarDatosMiembro(miembro) {
            // Copiar datos a los campos del formulario
            document.getElementById('nombres').value = miembro.nombres || '';
            document.getElementById('apellidos').value = miembro.apellidos || '';
            document.getElementById('email').value = miembro.email || '';
            document.getElementById('telefono').value = miembro.telefono || '';
            document.getElementById('direccion').value = miembro.direccion || '';
            document.getElementById('fecha_nacimiento').value = miembro.fecha_nacimiento || '';
            
            // Ocultar resultados y limpiar búsqueda
            resultadosBusqueda.style.display = 'none';
            inputBuscar.value = miembro.nombre;
            
            // Mostrar mensaje de confirmación
            const alert = document.createElement('div');
            alert.className = 'alert alert-success alert-dismissible fade show mt-2';
            alert.innerHTML = '<i class="fas fa-check-circle"></i> Datos copiados de: <strong>' + miembro.nombre + '</strong>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            
            const buscarContainer = inputBuscar.closest('.mb-3');
            buscarContainer.appendChild(alert);
            
            // Remover alert después de 3 segundos
            setTimeout(function() {
                alert.remove();
            }, 3000);
        }
        
        // Funcionalidad de compartir formulario
        const btnCompartirFormulario = document.getElementById('btnCompartirFormulario');
        if (btnCompartirFormulario) {
            btnCompartirFormulario.addEventListener('click', function() {
                fetch('ajax_generar_url_estudiante.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exito) {
                        const modalHTML = `
                            <div class="modal fade" id="modalCompartir" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-share-alt"></i> Compartir Formulario
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="mb-3">Comparte este enlace con el estudiante. El formulario solo puede usarse una vez.</p>
                                            <div class="mb-3">
                                                <label class="form-label">URL del Formulario:</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="urlFormulario" 
                                                           value="${data.url}" readonly>
                                                    <button class="btn btn-outline-secondary" type="button" 
                                                            id="btnCopiarUrl">
                                                        <i class="fas fa-copy"></i> Copiar
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="d-grid gap-2">
                                                <a href="${data.urlWhatsApp}" target="_blank" class="btn btn-success">
                                                    <i class="fab fa-whatsapp"></i> Compartir por WhatsApp
                                                </a>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        const modalAnterior = document.getElementById('modalCompartir');
                        if (modalAnterior) modalAnterior.remove();
                        
                        document.body.insertAdjacentHTML('beforeend', modalHTML);
                        const modal = new bootstrap.Modal(document.getElementById('modalCompartir'));
                        modal.show();
                        
                        const btnCopiar = document.getElementById('btnCopiarUrl');
                        if (btnCopiar) {
                            btnCopiar.addEventListener('click', function() {
                                const input = document.getElementById('urlFormulario');
                                input.select();
                                document.execCommand('copy');
                                const originalHTML = btnCopiar.innerHTML;
                                btnCopiar.innerHTML = '<i class="fas fa-check"></i> Copiado';
                                btnCopiar.classList.add('btn-success');
                                btnCopiar.classList.remove('btn-outline-secondary');
                                setTimeout(() => {
                                    btnCopiar.innerHTML = originalHTML;
                                    btnCopiar.classList.remove('btn-success');
                                    btnCopiar.classList.add('btn-outline-secondary');
                                }, 2000);
                            });
                        }
                    } else {
                        alert('Error al generar la URL: ' + (data.mensaje || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al generar la URL');
                });
            });
        }
        
        // Función para abrir modal con solicitud de estudiante
        window.abrirModalConSolicitudEstudiante = function(solicitudId) {
            fetch(`ajax_notificaciones_estudiantes.php?accion=obtener_por_id&id=${solicitudId}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Datos recibidos:', data); // Debug
                    if (data.exito && data.solicitud) {
                        const solicitud = data.solicitud;
                        console.log('Solicitud:', solicitud); // Debug
                        const form = document.querySelector('#modalEstudiante form');
                        if (form) {
                            // Cerrar modal de notificaciones primero
                            const modalNotificaciones = bootstrap.Modal.getInstance(document.getElementById('modalNotificaciones'));
                            if (modalNotificaciones) {
                                modalNotificaciones.hide();
                            }
                            
                            // Remover ID si existe
                            const hiddenId = form.querySelector('input[name="id"]');
                            if (hiddenId) hiddenId.remove();
                            
                            // Cambiar acción a crear
                            const hiddenAction = form.querySelector('input[name="accion"]');
                            if (hiddenAction) hiddenAction.value = 'crear_estudiante';
                            
                            // Limpiar campos específicos manualmente (no usar reset para evitar problemas)
                            const nombresInput = form.querySelector('#nombres');
                            const apellidosInput = form.querySelector('#apellidos');
                            const fechaNacimientoInput = form.querySelector('#fecha_nacimiento');
                            const telefonoInput = form.querySelector('#telefono');
                            const emailInput = form.querySelector('#email');
                            const direccionInput = form.querySelector('#direccion');
                            const selectCurso = form.querySelector('#curso_id');
                            const inputBuscar = document.getElementById('buscar_miembro');
                            const resultadosBusqueda = document.getElementById('resultados_busqueda');
                            
                            // Limpiar campos
                            if (nombresInput) nombresInput.value = '';
                            if (apellidosInput) apellidosInput.value = '';
                            if (fechaNacimientoInput) fechaNacimientoInput.value = '';
                            if (telefonoInput) telefonoInput.value = '';
                            if (emailInput) emailInput.value = '';
                            if (direccionInput) direccionInput.value = '';
                            if (selectCurso) selectCurso.value = '';
                            if (inputBuscar) inputBuscar.value = '';
                            if (resultadosBusqueda) {
                                resultadosBusqueda.style.display = 'none';
                                resultadosBusqueda.innerHTML = '';
                            }
                            
                            const fechaContainer = document.getElementById('fecha_inscripcion_container');
                            if (fechaContainer) fechaContainer.style.display = 'none';
                            
                            // Marcar que estamos abriendo desde una notificación para evitar que el listener de show.bs.modal limpie el formulario
                            const modalElement = document.getElementById('modalEstudiante');
                            modalElement.setAttribute('data-from-notification', 'true');
                            
                            // Cambiar título del modal
                            const modalTitle = document.querySelector('#modalEstudiante .modal-title');
                            if (modalTitle) modalTitle.textContent = 'Aprobar Solicitud de Estudiante';
                            
                            // Asegurar que el action sea 'crear_estudiante'
                            const actionInput = form.querySelector('input[name="accion"]');
                            if (actionInput) {
                                actionInput.value = 'crear_estudiante';
                            }
                            
                            // Guardar el ID de la solicitud en un campo hidden
                            let solicitudIdInput = form.querySelector('#solicitud_id');
                            if (!solicitudIdInput) {
                                solicitudIdInput = document.createElement('input');
                                solicitudIdInput.type = 'hidden';
                                solicitudIdInput.name = 'solicitud_id';
                                solicitudIdInput.id = 'solicitud_id';
                                // Insertar después del primer input hidden (accion)
                                const firstInput = form.querySelector('input[type="hidden"]');
                                if (firstInput) {
                                    firstInput.parentNode.insertBefore(solicitudIdInput, firstInput.nextSibling);
                                } else {
                                    form.insertBefore(solicitudIdInput, form.firstChild);
                                }
                            }
                            solicitudIdInput.value = solicitudId;
                            
                            // Cambiar texto del botón
                            const btnSubmit = form.querySelector('button[type="submit"]');
                            if (btnSubmit) {
                                btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                            }
                            
                            // Abrir modal de estudiante
                            const modal = new bootstrap.Modal(modalElement);
                            
                            // Agregar listener para llenar datos cuando el modal se muestre completamente
                            // Esto se ejecuta DESPUÉS del show.bs.modal que limpia el formulario
                            const fillDataOnShown = function() {
                                // Llenar datos cuando el modal esté completamente visible
                                if (nombresInput) {
                                    nombresInput.value = solicitud.nombres || '';
                                    console.log('Nombres asignado en shown:', nombresInput.value);
                                }
                                if (apellidosInput) {
                                    apellidosInput.value = solicitud.apellidos || '';
                                    console.log('Apellidos asignado en shown:', apellidosInput.value);
                                }
                                if (fechaNacimientoInput) {
                                    fechaNacimientoInput.value = solicitud.fecha_nacimiento || '';
                                }
                                if (telefonoInput) {
                                    telefonoInput.value = solicitud.telefono || '';
                                }
                                if (emailInput) {
                                    emailInput.value = solicitud.email || '';
                                }
                                if (direccionInput) {
                                    direccionInput.value = solicitud.direccion || '';
                                }
                                
                                // Remover el atributo después de llenar
                                modalElement.removeAttribute('data-from-notification');
                                modalElement.removeEventListener('shown.bs.modal', fillDataOnShown);
                            };
                            modalElement.addEventListener('shown.bs.modal', fillDataOnShown);
                            
                            modal.show();
                        }
                    } else {
                        if (window.mostrarNotificacionToast) {
                            window.mostrarNotificacionToast('Error al cargar la solicitud: ' + (data.mensaje || 'Error desconocido'), 'danger');
                        } else {
                            alert('Error al cargar la solicitud: ' + (data.mensaje || 'Error desconocido'));
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (window.mostrarNotificacionToast) {
                        window.mostrarNotificacionToast('Error al cargar la solicitud', 'danger');
                    } else {
                        alert('Error al cargar la solicitud');
                    }
                });
        };
        
        // Si hay notificación en la URL, cargarla
        <?php if (isset($_GET['notificacion']) && is_numeric($_GET['notificacion'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            window.abrirModalConSolicitudEstudiante(<?php echo (int)$_GET['notificacion']; ?>);
        });
        <?php endif; ?>
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>