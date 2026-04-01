<?php
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once '../NEGOCIO/nEvento.php';
require_once '../NEGOCIO/nTipoEvento.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

$auth = new AuthController();
$auth->requireLogin();

// Verificar privilegios
if (!auth::tienePrivilegio('eventos_ver')) {
    header('Location: dashboard.php');
    exit();
}

$nEvento = new nEvento();
$nTipoEvento = new nTipoEvento();
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
                    if (!auth::tienePrivilegio('eventos_crear')) {
                        header('Location: eventos.php');
                        exit();
                    }
                    $resultado = $nEvento->insertar($_POST);
                    if ($resultado && isset($resultado['exito']) && $resultado['exito']) {
                        // Redirigir para evitar reenvío del formulario y cerrar el modal
                        header('Location: eventos.php?mensaje=' . urlencode($resultado['mensaje'] ?? 'Evento registrado exitosamente') . '&tipo=success');
                        exit;
                    } else {
                        $message = isset($resultado['mensaje']) ? $resultado['mensaje'] : 'Error al registrar el evento';
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'update':
                    if (!auth::tienePrivilegio('eventos_editar')) {
                        header('Location: eventos.php');
                        exit();
                    }
                    $resultado = $nEvento->actualizar($_POST);
                    if ($resultado && isset($resultado['exito']) && $resultado['exito']) {
                        // Redirigir para evitar reenvío del formulario y cerrar el modal
                        header('Location: eventos.php?mensaje=' . urlencode($resultado['mensaje'] ?? 'Evento actualizado exitosamente') . '&tipo=success');
                        exit;
                    } else {
                        $message = isset($resultado['mensaje']) ? $resultado['mensaje'] : 'Error al actualizar el evento';
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'delete':
                    if (!auth::tienePrivilegio('eventos_eliminar')) {
                        header('Location: eventos.php');
                        exit();
                    }
                    $resultado = $nEvento->eliminar($_POST['id']);
                    if ($resultado && isset($resultado['exito']) && $resultado['exito']) {
                        // Redirigir para evitar reenvío del formulario
                        header('Location: eventos.php?mensaje=' . urlencode($resultado['mensaje'] ?? 'Evento eliminado exitosamente') . '&tipo=success');
                        exit;
                    } else {
                        $message = isset($resultado['mensaje']) ? $resultado['mensaje'] : 'Error al eliminar el evento';
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
        $evento = $nEvento->obtenerPorId($terminoBusqueda);
        $eventos = $evento ? [$evento] : [];
    } else {
        $eventos = $nEvento->buscarPorNombre($terminoBusqueda);
    }
} else {
$eventos = $nEvento->listar();
}
$tipos_evento = $nTipoEvento->listar();

// Obtener datos para editar si se solicita
$eventoEditar = null;
if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $eventoEditar = $nEvento->obtenerPorId($_GET['id']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Eventos - Sistema de Administración de Iglesia</title>
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
    </style>
    <?php echo generarEstilosNotificaciones(); ?>
</head>
<body>
    <!-- Offcanvas Menu (móviles) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuEventos" aria-labelledby="offcanvasMenuEventosLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuEventosLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('eventos'); ?>
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
                        <?php echo generarMenu('eventos'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuEventos" aria-controls="offcanvasMenuEventos">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-alt text-primary"></i> Gestión de Eventos
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
                    
                    <!-- Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Lista de Eventos</h5>
                                <?php if (tieneAcceso('eventos_crear')): ?>
                                <a href="eventos.php" class="btn btn-primary" id="btnNuevoEvento">
                                    <i class="fas fa-plus"></i> Nuevo Evento
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
                                        <a href="eventos.php" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Events Table -->
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
                                                    <th>Tipo</th>
                                                    <th>Fecha Inicio</th>
                                                    <th>Fecha Fin</th>
                                                    <th>Presupuesto</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($eventos as $evento): ?>
                                                    <tr>
                                                        <td><?php echo $evento['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($evento['nombre'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($evento['tipo_evento_nombre'] ?? 'Sin tipo'); ?></td>
                                                        <td><?php echo !empty($evento['fecha_inicio']) ? date('d/m/Y', strtotime($evento['fecha_inicio'])) : 'N/A'; ?></td>
                                                        <td><?php echo !empty($evento['fecha_fin']) ? date('d/m/Y', strtotime($evento['fecha_fin'])) : 'N/A'; ?></td>
                                                        <td><?php echo isset($evento['presupuesto']) && $evento['presupuesto'] > 0 ? '$' . number_format($evento['presupuesto'], 2) : '-'; ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <?php if (tieneAcceso('eventos_editar')): ?>
                                                                <a href="eventos.php?action=editar&id=<?php echo $evento['id']; ?>" 
                                                                   class="btn btn-warning" 
                                                                   title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <?php endif; ?>
                                                                <?php if (tieneAcceso('eventos_eliminar')): ?>
                                                                <button type="button" 
                                                                        class="btn btn-danger" 
                                                                        onclick="confirmarEliminar(<?php echo $evento['id']; ?>)"
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

    <!-- Event Modal -->
    <div class="modal fade" id="modalEvento" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $eventoEditar ? 'Editar Evento' : 'Nuevo Evento'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $eventoEditar ? 'update' : 'insert'; ?>">
                        <?php if ($eventoEditar): ?>
                            <input type="hidden" name="id" value="<?php echo $eventoEditar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo $eventoEditar ? htmlspecialchars($eventoEditar['nombre'] ?? '') : ''; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="id_tipo_evento" class="form-label">Tipo de Evento *</label>
                                <select class="form-select" id="id_tipo_evento" name="id_tipo_evento" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($tipos_evento as $tipo): ?>
                                        <option value="<?php echo $tipo['id']; ?>" 
                                                <?php echo $eventoEditar && ($eventoEditar['tipo_evento_id'] ?? 0) == $tipo['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tipo['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha Inicio *</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                       value="<?php echo $eventoEditar ? ($eventoEditar['fecha_inicio'] ?? '') : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                       value="<?php echo $eventoEditar ? ($eventoEditar['fecha_fin'] ?? '') : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <input type="checkbox" id="es_economico" name="es_economico" value="1" 
                                           <?php echo $eventoEditar && ($eventoEditar['es_economico'] ?? 0) == 1 ? 'checked' : ''; ?>>
                                    Es Económico
                                </label>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="presupuesto" class="form-label">Presupuesto</label>
                                <input type="number" step="0.01" class="form-control" id="presupuesto" name="presupuesto" 
                                       value="<?php echo $eventoEditar ? ($eventoEditar['presupuesto'] ?? '') : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $eventoEditar ? htmlspecialchars($eventoEditar['descripcion']) : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="1" <?php echo $eventoEditar && ($eventoEditar['estado'] ?? 0) == 1 ? 'selected' : ''; ?>>Activo</option>
                                    <option value="0" <?php echo $eventoEditar && ($eventoEditar['estado'] ?? 0) == 0 ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $eventoEditar ? 'Actualizar' : 'Guardar'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de que desea eliminar este evento?')) {
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
        function limpiarYAbrirModalEvento() {
            const modalEvento = document.getElementById('modalEvento');
            if (!modalEvento) return;
            const form = modalEvento.querySelector('form');
            if (!form) return;
            
            ['nombre', 'fecha_inicio', 'fecha_fin', 'presupuesto', 'descripcion'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            
            const checkboxEconomico = form.querySelector('#es_economico');
            if (checkboxEconomico) checkboxEconomico.checked = false;
            
            const hiddenId = form.querySelector('input[name="id"]');
            if (hiddenId) hiddenId.remove();
            const hiddenAction = form.querySelector('input[name="action"]');
            if (hiddenAction) hiddenAction.value = 'insert';
            const modalTitle = modalEvento.querySelector('.modal-title');
            if (modalTitle) modalTitle.textContent = 'Nuevo Evento';
            const btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                const icon = btnSubmit.querySelector('i');
                if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                else btnSubmit.textContent = 'Guardar';
            }
            const selectTipo = form.querySelector('#id_tipo_evento');
            if (selectTipo && selectTipo.options.length > 0) {
                Array.from(selectTipo.options).forEach(option => option.selected = false);
                selectTipo.options[0].selected = true;
            }
            const selectEstado = form.querySelector('#estado');
            if (selectEstado) selectEstado.value = '1';
            
            form.reset();
            
            // Asegurar que los campos estén limpios después del reset
            ['nombre', 'fecha_inicio', 'fecha_fin', 'presupuesto', 'descripcion'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            if (checkboxEconomico) checkboxEconomico.checked = false;
            if (selectTipo && selectTipo.options.length > 0) {
                Array.from(selectTipo.options).forEach(option => option.selected = false);
                selectTipo.options[0].selected = true;
            }
            if (selectEstado) selectEstado.value = '1';
            
            const modal = new bootstrap.Modal(modalEvento);
            modal.show();
        }
        
        const btnNuevoEvento = document.getElementById('btnNuevoEvento');
        if (btnNuevoEvento) {
            btnNuevoEvento.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.location.search) window.history.replaceState({}, '', 'eventos.php');
                limpiarYAbrirModalEvento();
            });
        }
        
        const modalEvento = document.getElementById('modalEvento');
        if (modalEvento) {
            modalEvento.addEventListener('hidden.bs.modal', function() {
                const form = modalEvento.querySelector('form');
                if (form) {
                    form.reset();
                    const hiddenId = form.querySelector('input[name="id"]');
                    if (hiddenId) hiddenId.remove();
                    const hiddenAction = form.querySelector('input[name="action"]');
                    if (hiddenAction) hiddenAction.value = 'insert';
                    const modalTitle = modalEvento.querySelector('.modal-title');
                    if (modalTitle) modalTitle.textContent = 'Nuevo Evento';
                    const btnSubmit = form.querySelector('button[type="submit"]');
                    if (btnSubmit) {
                        const icon = btnSubmit.querySelector('i');
                        if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                        else btnSubmit.textContent = 'Guardar';
                    }
                }
            });
        }
        
        // Auto-abrir modal si hay datos para editar
        <?php if ($eventoEditar): ?>
            const modal = new bootstrap.Modal(document.getElementById('modalEvento'));
            modal.show();
        <?php endif; ?>
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>