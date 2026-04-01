<?php
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once '../NEGOCIO/nCurso.php';
require_once '../NEGOCIO/nDocente.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

$auth = new AuthController();
$auth->requireLogin();

// Verificar privilegios
if (!auth::tienePrivilegio('cursos_ver')) {
    header('Location: dashboard.php');
    exit();
}

$nCurso = new nCurso();
$nDocente = new nDocente();
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
                    if (!auth::tienePrivilegio('cursos_crear')) {
                        header('Location: cursos.php');
                        exit();
                    }
                    $resultado = $nCurso->insertar(
                        $_POST['nombre'],
                        $_POST['descripcion'] ?? '',
                        $_POST['fecha_inicio'],
                        $_POST['fecha_fin'],
                        $_POST['capacidad_maxima'] ?? 0,
                        $_POST['docente_id'],
                        isset($_POST['estado']) ? 1 : 0
                    );
                    if ($resultado['exito']) {
                        // Redirigir para cerrar el modal y mostrar mensaje
                        header('Location: cursos.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                        exit();
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'update':
                    if (!auth::tienePrivilegio('cursos_editar')) {
                        header('Location: cursos.php');
                        exit();
                    }
                    $resultado = $nCurso->actualizar(
                        $_POST['id'],
                        $_POST['nombre'],
                        $_POST['descripcion'] ?? '',
                        $_POST['fecha_inicio'],
                        $_POST['fecha_fin'],
                        $_POST['capacidad_maxima'] ?? 0,
                        $_POST['docente_id'],
                        isset($_POST['estado']) ? 1 : 0
                    );
                    if ($resultado['exito']) {
                        // Redirigir para evitar que el modal se mantenga abierto
                        header('Location: cursos.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                        exit();
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'delete':
                    if (!auth::tienePrivilegio('cursos_eliminar')) {
                        header('Location: cursos.php');
                        exit();
                    }
                    $resultado = $nCurso->eliminar($_POST['id']);
                    if ($resultado['exito']) {
                        // Redirigir para mostrar mensaje
                        header('Location: cursos.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                        exit();
                    } else {
                        $message = $resultado['mensaje'];
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

// Procesar búsqueda
$terminoBusqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
if (!empty($terminoBusqueda)) {
    // Si es un número, buscar por ID, sino por nombre
    if (is_numeric($terminoBusqueda)) {
        $curso = $nCurso->obtenerPorId($terminoBusqueda);
        $cursos = $curso ? [$curso] : [];
    } else {
        $cursos = $nCurso->buscarPorNombre($terminoBusqueda);
    }
} else {
    $cursos = $nCurso->listar();
}

// Obtener datos para editar si se solicita
$cursoEditar = null;
if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $cursoEditar = $nCurso->obtenerPorId($_GET['id']);
}

// Obtener lista de docentes para el select
$docentes = $nDocente->listar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos - Sistema de Administración de Iglesia</title>
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
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuCursos" aria-labelledby="offcanvasMenuCursosLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuCursosLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('cursos'); ?>
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
                        <?php echo generarMenu('cursos'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuCursos" aria-controls="offcanvasMenuCursos">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-book text-primary"></i> Cursos
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
                                <h5 class="mb-0">Lista de Cursos</h5>
                                <?php if (tieneAcceso('cursos_crear')): ?>
                                <a href="cursos.php" class="btn btn-primary" id="btnNuevoCurso">
                                    <i class="fas fa-plus"></i> Nuevo Curso
                                </a>
                                <?php endif; ?>
                            </div>
                            <form method="GET" class="mb-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="buscar" 
                                           placeholder="Buscar por ID o nombre..." 
                                           value="<?php echo htmlspecialchars($terminoBusqueda); ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    <?php if (!empty($terminoBusqueda)): ?>
                                        <a href="cursos.php" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Fecha inicio</th>
                                            <th>Fecha fin</th>
                                            <th>Capacidad</th>
                                            <th>Docente</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cursos as $curso): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($curso['id'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($curso['nombre'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars(substr($curso['descripcion'] ?? '', 0, 50)) . (strlen($curso['descripcion'] ?? '') > 50 ? '...' : ''); ?></td>
                                                <td><?php echo htmlspecialchars($curso['fecha_inicio'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($curso['fecha_fin'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($curso['capacidad_maxima'] ?? 0); ?></td>
                                                <td><?php echo htmlspecialchars(trim(($curso['docente_nombre'] ?? '') . ' ' . ($curso['docente_apellido'] ?? ''))); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if (tieneAcceso('cursos_editar')): ?>
                                                        <a href="cursos.php?action=editar&id=<?php echo $curso['id']; ?>" 
                                                           class="btn btn-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                        <?php if (tieneAcceso('cursos_eliminar')): ?>
                                                        <button type="button" class="btn btn-danger" 
                                                                onclick="confirmarEliminar(<?php echo $curso['id']; ?>)" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($cursos)): ?>
                                            <tr><td colspan="9" class="text-center text-muted">Sin registros</td></tr>
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

    <!-- Modal Curso -->
    <div class="modal fade" id="modalCurso" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $cursoEditar ? 'Editar Curso' : 'Nuevo Curso'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $cursoEditar ? 'update' : 'insert'; ?>">
                        <?php if ($cursoEditar): ?>
                            <input type="hidden" name="id" value="<?php echo $cursoEditar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo $cursoEditar ? htmlspecialchars($cursoEditar['nombre'] ?? '') : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $cursoEditar ? htmlspecialchars($cursoEditar['descripcion'] ?? '') : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio *</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                       value="<?php echo $cursoEditar ? htmlspecialchars($cursoEditar['fecha_inicio'] ?? '') : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_fin" class="form-label">Fecha Fin *</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                       value="<?php echo $cursoEditar ? htmlspecialchars($cursoEditar['fecha_fin'] ?? '') : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="capacidad_maxima" class="form-label">Capacidad Máxima</label>
                                <input type="number" class="form-control" id="capacidad_maxima" name="capacidad_maxima" 
                                       value="<?php echo $cursoEditar ? htmlspecialchars($cursoEditar['capacidad_maxima'] ?? 0) : 0; ?>" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="docente_id" class="form-label">Docente *</label>
                                <select class="form-select" id="docente_id" name="docente_id" required>
                                    <option value="">Seleccione un docente...</option>
                                    <?php foreach ($docentes as $docente): ?>
                                        <option value="<?php echo $docente['id']; ?>" 
                                                <?php echo ($cursoEditar && ($cursoEditar['docente_id'] ?? null) == $docente['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(trim(($docente['nombres'] ?? '') . ' ' . ($docente['apellidos'] ?? ''))); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="estado" name="estado" 
                                       <?php echo ($cursoEditar && ($cursoEditar['estado'] ?? 0)) ? 'checked' : 'checked'; ?>>
                                <label class="form-check-label" for="estado">Activo</label>
                            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de eliminar este curso?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Función para limpiar y abrir el modal
        function limpiarYAbrirModalCurso() {
            const modalCurso = document.getElementById('modalCurso');
            if (!modalCurso) return;
            const form = modalCurso.querySelector('form');
            if (!form) return;
            
            // Limpiar campos
            ['nombre', 'descripcion', 'fecha_inicio', 'fecha_fin', 'capacidad_maxima'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            
            const hiddenId = form.querySelector('input[name="id"]');
            if (hiddenId) hiddenId.remove();
            
            const hiddenAction = form.querySelector('input[name="action"]');
            if (hiddenAction) hiddenAction.value = 'insert';
            
            const modalTitle = modalCurso.querySelector('.modal-title');
            if (modalTitle) modalTitle.textContent = 'Nuevo Curso';
            
            const btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                const icon = btnSubmit.querySelector('i');
                if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                else btnSubmit.textContent = 'Guardar';
            }
            
            const selectDocente = form.querySelector('#docente_id');
            if (selectDocente && selectDocente.options.length > 0) {
                Array.from(selectDocente.options).forEach(option => option.selected = false);
                selectDocente.options[0].selected = true;
            }
            
            const checkboxEstado = form.querySelector('#estado');
            if (checkboxEstado) checkboxEstado.checked = true;
            
            form.reset();
            
            // Volver a limpiar
            ['nombre', 'descripcion', 'fecha_inicio', 'fecha_fin', 'capacidad_maxima'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            if (selectDocente && selectDocente.options.length > 0) {
                Array.from(selectDocente.options).forEach(option => option.selected = false);
                selectDocente.options[0].selected = true;
            }
            if (checkboxEstado) checkboxEstado.checked = true;
            
            const modal = new bootstrap.Modal(modalCurso);
            modal.show();
        }
        
        const btnNuevoCurso = document.getElementById('btnNuevoCurso');
        if (btnNuevoCurso) {
            btnNuevoCurso.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.location.search) window.history.replaceState({}, '', 'cursos.php');
                limpiarYAbrirModalCurso();
            });
        }
        
        const modalCurso = document.getElementById('modalCurso');
        if (modalCurso) {
            modalCurso.addEventListener('hidden.bs.modal', function() {
                const form = modalCurso.querySelector('form');
                if (form) {
                    form.reset();
                    const hiddenId = form.querySelector('input[name="id"]');
                    if (hiddenId) hiddenId.remove();
                    const hiddenAction = form.querySelector('input[name="action"]');
                    if (hiddenAction) hiddenAction.value = 'insert';
                    const modalTitle = modalCurso.querySelector('.modal-title');
                    if (modalTitle) modalTitle.textContent = 'Nuevo Curso';
                    const btnSubmit = form.querySelector('button[type="submit"]');
                    if (btnSubmit) {
                        const icon = btnSubmit.querySelector('i');
                        if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                        else btnSubmit.textContent = 'Guardar';
                    }
                }
            });
        }
        
        <?php if ($cursoEditar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalCurso'));
            modal.show();
        });
        <?php endif; ?>
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
<?php /* eof */ ?>


