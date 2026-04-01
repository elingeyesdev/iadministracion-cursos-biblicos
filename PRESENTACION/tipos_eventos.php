<?php
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
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

$nTipoEvento = new nTipoEvento();
$message = '';
$messageType = '';
$tipoEditar = null;

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
                        header('Location: tipos_eventos.php');
                        exit();
                    }
                    $resultado = $nTipoEvento->insertar(
                        $_POST['nombre'],
                        $_POST['descripcion'] ?? ''
                    );
                    if ($resultado['exito']) {
                        header('Location: tipos_eventos.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                        exit();
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'update':
                    if (!auth::tienePrivilegio('eventos_editar')) {
                        header('Location: tipos_eventos.php');
                        exit();
                    }
                    $resultado = $nTipoEvento->actualizar(
                        $_POST['id'],
                        $_POST['nombre'],
                        $_POST['descripcion'] ?? ''
                    );
                    if ($resultado['exito']) {
                        header('Location: tipos_eventos.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                        exit();
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'delete':
                    if (!auth::tienePrivilegio('eventos_eliminar')) {
                        header('Location: tipos_eventos.php');
                        exit();
                    }
                    $resultado = $nTipoEvento->eliminar($_POST['id']);
                    if ($resultado['exito']) {
                        header('Location: tipos_eventos.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
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

// Procesar edición
if (isset($_GET['action']) && $_GET['action'] === 'editar' && isset($_GET['id'])) {
    $tipoEditar = $nTipoEvento->obtenerPorId($_GET['id']);
    if (!$tipoEditar) {
        $message = 'Tipo de evento no encontrado';
        $messageType = 'danger';
    }
}

// Procesar búsqueda
$terminoBusqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
if (!empty($terminoBusqueda)) {
    // Si es un número, buscar por ID, sino por nombre
    if (is_numeric($terminoBusqueda)) {
        $tipo = $nTipoEvento->obtenerPorId($terminoBusqueda);
        $tipos = $tipo ? [$tipo] : [];
    } else {
        $tipos = $nTipoEvento->buscarPorNombre($terminoBusqueda);
    }
} else {
    $tipos = $nTipoEvento->listar();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tipos de Eventos - Sistema de Administración de Iglesia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 16.666667%;
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
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuTiposEventos" aria-labelledby="offcanvasMenuTiposEventosLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuTiposEventosLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('tipos_eventos'); ?>
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
                        <?php echo generarMenu('tipos_eventos'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuTiposEventos" aria-controls="offcanvasMenuTiposEventos">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-alt text-primary"></i> Tipos de Eventos
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
                    
                    <!-- Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Lista de Tipos de Eventos</h5>
                                <?php if (tieneAcceso('eventos_crear')): ?>
                                <button type="button" class="btn btn-primary" id="btnNuevoTipoEvento">
                                    <i class="fas fa-plus"></i> Nuevo Tipo de Evento
                                </button>
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
                                        <a href="tipos_eventos.php" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Types Table -->
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
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tipos as $tipo): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($tipo['id'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($tipo['nombre'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($tipo['descripcion'] ?? ''); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if (tieneAcceso('eventos_editar')): ?>
                                                        <a href="tipos_eventos.php?action=editar&id=<?php echo $tipo['id']; ?>" 
                                                           class="btn btn-warning" 
                                                           title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                        <?php if (tieneAcceso('eventos_eliminar')): ?>
                                                        <button type="button" 
                                                                class="btn btn-danger" 
                                                                onclick="confirmarEliminar(<?php echo $tipo['id']; ?>)" 
                                                                title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($tipos)): ?>
                                            <tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>
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

    <!-- Modal Tipo de Evento -->
    <div class="modal fade" id="modalTipoEvento" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $tipoEditar ? 'Editar Tipo de Evento' : 'Nuevo Tipo de Evento'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $tipoEditar ? 'update' : 'insert'; ?>">
                        <?php if ($tipoEditar): ?>
                            <input type="hidden" name="id" value="<?php echo $tipoEditar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo $tipoEditar ? htmlspecialchars($tipoEditar['nombre'] ?? '') : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $tipoEditar ? htmlspecialchars($tipoEditar['descripcion'] ?? '') : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $tipoEditar ? 'Actualizar' : 'Guardar'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de eliminar este tipo de evento?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Función para limpiar y abrir el modal
        function limpiarYAbrirModalTipoEvento() {
            const modalTipoEvento = document.getElementById('modalTipoEvento');
            if (!modalTipoEvento) return;
            const form = modalTipoEvento.querySelector('form');
            if (!form) return;
            
            // Limpiar campos
            ['nombre', 'descripcion'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            
            // Remover ID si existe
            const hiddenId = form.querySelector('input[name="id"]');
            if (hiddenId) hiddenId.remove();
            
            // Cambiar action a insert
            const hiddenAction = form.querySelector('input[name="action"]');
            if (hiddenAction) hiddenAction.value = 'insert';
            
            // Cambiar título
            const modalTitle = modalTipoEvento.querySelector('.modal-title');
            if (modalTitle) modalTitle.textContent = 'Nuevo Tipo de Evento';
            
            // Cambiar texto del botón
            const btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
            }
            
            form.reset();
            
            const modal = new bootstrap.Modal(modalTipoEvento);
            modal.show();
        }
        
        // Abrir modal si hay acción de editar
        <?php if ($tipoEditar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalTipoEvento'));
            modal.show();
        });
        <?php endif; ?>
        
        // Botón nuevo tipo de evento
        const btnNuevoTipoEvento = document.getElementById('btnNuevoTipoEvento');
        if (btnNuevoTipoEvento) {
            btnNuevoTipoEvento.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.location.search) window.history.replaceState({}, '', 'tipos_eventos.php');
                limpiarYAbrirModalTipoEvento();
            });
        }
        
        // Limpiar modal al cerrar
        const modalTipoEvento = document.getElementById('modalTipoEvento');
        if (modalTipoEvento) {
            modalTipoEvento.addEventListener('hidden.bs.modal', function() {
                const form = modalTipoEvento.querySelector('form');
                if (form) {
                    form.reset();
                    const hiddenId = form.querySelector('input[name="id"]');
                    if (hiddenId) hiddenId.remove();
                    const hiddenAction = form.querySelector('input[name="action"]');
                    if (hiddenAction) hiddenAction.value = 'insert';
                    const modalTitle = modalTipoEvento.querySelector('.modal-title');
                    if (modalTitle) modalTitle.textContent = 'Nuevo Tipo de Evento';
                    const btnSubmit = form.querySelector('button[type="submit"]');
                    if (btnSubmit) {
                        btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                    }
                }
            });
        }
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>
