<?php
require_once '../NEGOCIO/nRol.php';
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

$auth = new AuthController();
$auth->requireLogin();
if (!auth::tienePrivilegio('roles_ver')) {
    header('Location: dashboard.php');
    exit();
}

$nRol = new nRol();
$message = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
$messageType = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'insert':
                    if (auth::tienePrivilegio('roles_crear')) {
                        $resultado = $nRol->insertar(
                            $_POST['nombre'],
                            $_POST['descripcion'],
                            isset($_POST['estado']) ? 1 : 0
                        );
                        if ($resultado['exito']) {
                            $rol_id = $resultado['id'];
                            // Asignar privilegios al rol
                            if (isset($_POST['privilegios_rol']) && is_array($_POST['privilegios_rol'])) {
                                $nRol->asignarPrivilegios($rol_id, $_POST['privilegios_rol']);
                            }
                            header('Location: roles.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                            exit;
                        } else {
                            $message = $resultado['mensaje'];
                            $messageType = 'danger';
                        }
                    } else {
                        $message = 'No tiene permisos para crear roles';
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'update':
                    if (auth::tienePrivilegio('roles_editar')) {
                        $resultado = $nRol->actualizar(
                            $_POST['id'],
                            $_POST['nombre'],
                            $_POST['descripcion'],
                            isset($_POST['estado']) ? 1 : 0
                        );
                        if ($resultado['exito']) {
                            // Actualizar privilegios del rol
                            if (isset($_POST['privilegios_rol']) && is_array($_POST['privilegios_rol'])) {
                                $nRol->asignarPrivilegios($_POST['id'], $_POST['privilegios_rol']);
                            }
                            header('Location: roles.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                            exit;
                        } else {
                            $message = $resultado['mensaje'];
                            $messageType = 'danger';
                        }
                    } else {
                        header('Location: roles.php');
                        exit();
                    }
                    break;
                    
                case 'delete':
                    if (auth::tienePrivilegio('roles_eliminar')) {
                        $resultado = $nRol->eliminar($_POST['id']);
                        if ($resultado['exito']) {
                            header('Location: roles.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                            exit;
                        } else {
                            $message = $resultado['mensaje'];
                            $messageType = 'danger';
                        }
                    } else {
                        header('Location: roles.php');
                        exit();
                    }
                    break;
                    
                case 'asignar_privilegios_rol':
                    if (auth::tienePrivilegio('roles_editar')) {
                        $privilegios_ids = isset($_POST['privilegios_rol']) ? $_POST['privilegios_rol'] : [];
                        $resultado = $nRol->asignarPrivilegios($_POST['rol_id'], $privilegios_ids);
                        if ($resultado) {
                            header('Location: roles.php?mensaje=' . urlencode('Privilegios del rol actualizados exitosamente') . '&tipo=success');
                            exit;
                        } else {
                            $message = 'Error al actualizar los privilegios del rol';
                            $messageType = 'danger';
                        }
                    } else {
                        header('Location: roles.php');
                        exit();
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
        $rol = $nRol->obtenerPorId($terminoBusqueda);
        $roles = $rol ? [$rol] : [];
    } else {
        $roles = $nRol->buscarPorNombre($terminoBusqueda);
    }
} else {
    $roles = $nRol->listar();
}

// Obtener datos para editar si se solicita
$rolEditar = null;
$rolPrivilegios = [];
if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $rolEditar = $nRol->obtenerPorId($_GET['id']);
    if ($rolEditar) {
        $rolPrivilegios = $nRol->obtenerPrivilegios($_GET['id']);
        $rolPrivilegios = array_column($rolPrivilegios, 'id');
    }
}

$rolPrivilegiosModal = null;
$privilegiosRolIds = [];
if (isset($_GET['action']) && $_GET['action'] == 'privilegios' && isset($_GET['id'])) {
    $rolPrivilegiosModal = $nRol->obtenerPorId($_GET['id']);
    if ($rolPrivilegiosModal) {
        $privilegiosRol = $nRol->obtenerPrivilegios($_GET['id']);
        $privilegiosRolIds = array_column($privilegiosRol, 'id');
    }
}

// Obtener listas
$privilegios = $nRol->listarPrivilegios();
$privilegiosPorModulo = [];
foreach ($privilegios as $privilegio) {
    $modulo = $privilegio['modulo'];
    if (!isset($privilegiosPorModulo[$modulo])) {
        $privilegiosPorModulo[$modulo] = [];
    }
    $privilegiosPorModulo[$modulo][] = $privilegio;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles - Sistema de Administración de Iglesia</title>
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
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuRoles" aria-labelledby="offcanvasMenuRolesLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuRolesLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('roles'); ?>
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
                        <?php echo generarMenu('roles'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuRoles" aria-controls="offcanvasMenuRoles">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-user-shield text-primary"></i> Gestión de Roles
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
                                <h5 class="mb-0">
                                    <i class="fas fa-user-shield text-primary"></i> Lista de Roles
                                    <small class="text-muted">(Total: <?php echo count($roles); ?>)</small>
                                </h5>
                                <?php if (auth::tienePrivilegio('roles_crear')): ?>
                                <a href="roles.php" class="btn btn-primary" id="btnNuevoRol">
                                    <i class="fas fa-plus"></i> Nuevo Rol
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
                                        <a href="roles.php" class="btn btn-outline-danger">
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
                                    <?php if (empty($roles)): ?>
                                        <div class="alert alert-info text-center">
                                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                                            <h5>No hay roles registrados</h5>
                                            <p>Comience creando un nuevo rol para asignar a los usuarios.</p>
                                            <?php if (auth::tienePrivilegio('roles_crear')): ?>
                                            <a href="roles.php" class="btn btn-primary" id="btnNuevoRolVacio">
                                                <i class="fas fa-plus"></i> Crear Nuevo Rol
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
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
                                                    <?php foreach ($roles as $rol): ?>
                                                        <tr>
                                                            <td><?php echo $rol['id']; ?></td>
                                                            <td>
                                                                <strong><?php echo htmlspecialchars($rol['nombre']); ?></strong>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($rol['descripcion'] ?? 'Sin descripción'); ?></td>
                                                            <td>
                                                                <div class="btn-group btn-group-sm">
                                                                    <a href="roles.php?action=editar&id=<?php echo $rol['id']; ?>" 
                                                                       class="btn btn-warning" title="Editar">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <a href="roles.php?action=privilegios&id=<?php echo $rol['id']; ?>" 
                                                                       class="btn btn-info" title="Gestionar Privilegios">
                                                                        <i class="fas fa-key"></i>
                                                                    </a>
                                                                    <button type="button" class="btn btn-danger" 
                                                                            onclick="confirmarEliminar(<?php echo $rol['id']; ?>)" title="Eliminar">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Rol -->
    <div class="modal fade" id="modalRol" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $rolEditar ? 'Editar Rol' : 'Nuevo Rol'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $rolEditar ? 'update' : 'insert'; ?>">
                        <?php if ($rolEditar): ?>
                            <input type="hidden" name="id" value="<?php echo $rolEditar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="nombre_rol" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre_rol" name="nombre" 
                                   value="<?php echo $rolEditar ? htmlspecialchars($rolEditar['nombre']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion_rol" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion_rol" name="descripcion" rows="3"><?php echo $rolEditar ? htmlspecialchars($rolEditar['descripcion'] ?? '') : ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Privilegios</label>
                            <div class="row" style="max-height: 400px; overflow-y: auto;">
                                <?php foreach ($privilegiosPorModulo as $modulo => $privs): ?>
                                    <div class="col-md-6 mb-3">
                                        <h6><?php echo ucfirst($modulo); ?></h6>
                                        <?php foreach ($privs as $priv): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="privilegios_rol[]" value="<?php echo $priv['id']; ?>"
                                                       id="rol_priv_<?php echo $priv['id']; ?>"
                                                       <?php echo ($rolEditar && in_array($priv['id'], $rolPrivilegios)) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="rol_priv_<?php echo $priv['id']; ?>">
                                                    <?php echo htmlspecialchars($priv['descripcion']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="estado_rol" name="estado" 
                                       <?php echo ($rolEditar && $rolEditar['estado']) ? 'checked' : 'checked'; ?>>
                                <label class="form-check-label" for="estado_rol">Activo</label>
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

    <!-- Modal Privilegios Rol -->
    <?php if ($rolPrivilegiosModal): ?>
    <div class="modal fade show" id="modalPrivilegiosRol" tabindex="-1" style="display: block;">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
                <div class="modal-header" style="flex-shrink: 0;">
                    <h5 class="modal-title">Gestionar Privilegios - <?php echo htmlspecialchars($rolPrivilegiosModal['nombre']); ?></h5>
                    <a href="roles.php" class="btn-close"></a>
                </div>
                <form method="POST" style="display: flex; flex-direction: column; height: 100%;">
                    <div class="modal-body" style="flex: 1; overflow-y: auto; max-height: calc(90vh - 150px);">
                        <input type="hidden" name="action" value="asignar_privilegios_rol">
                        <input type="hidden" name="rol_id" value="<?php echo $rolPrivilegiosModal['id']; ?>">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i> 
                            Seleccione los privilegios que tendrá este rol. Los usuarios con este rol heredarán estos privilegios automáticamente.
                        </div>
                        <div class="row">
                            <?php foreach ($privilegiosPorModulo as $modulo => $privs): ?>
                                <div class="col-12 col-md-6 mb-3">
                                    <h6 class="fw-bold text-primary"><?php echo ucfirst($modulo); ?></h6>
                                    <?php foreach ($privs as $priv): ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="privilegios_rol[]" value="<?php echo $priv['id']; ?>"
                                                   id="rol_priv_modal_<?php echo $priv['id']; ?>"
                                                   <?php echo in_array($priv['id'], $privilegiosRolIds) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="rol_priv_modal_<?php echo $priv['id']; ?>">
                                                <?php echo htmlspecialchars($priv['descripcion']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer" style="flex-shrink: 0; border-top: 1px solid #dee2e6;">
                        <a href="roles.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Privilegios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <style>
        @media (max-width: 576px) {
            #modalPrivilegiosRol .modal-content { height: 100vh; }
            #modalPrivilegiosRol .modal-body { max-height: calc(100vh - 140px); overflow-y: auto; padding: 0.75rem; }
            #modalPrivilegiosRol .modal-header, #modalPrivilegiosRol .modal-footer { padding: 0.5rem 0.75rem; }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de eliminar este rol? No se puede eliminar si hay usuarios asignados.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Función para limpiar y abrir el modal
        function limpiarYAbrirModalRol() {
            const modalRol = document.getElementById('modalRol');
            if (!modalRol) return;
            const form = modalRol.querySelector('form');
            if (!form) return;
            
            ['nombre_rol', 'descripcion_rol'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            
            const hiddenId = form.querySelector('input[name="id"]');
            if (hiddenId) hiddenId.remove();
            const hiddenAction = form.querySelector('input[name="action"]');
            if (hiddenAction) hiddenAction.value = 'insert';
            const modalTitle = modalRol.querySelector('.modal-title');
            if (modalTitle) modalTitle.textContent = 'Nuevo Rol';
            const btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                const icon = btnSubmit.querySelector('i');
                if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                else btnSubmit.textContent = 'Guardar';
            }
            // Desmarcar todos los checkboxes de privilegios
            const checkboxes = form.querySelectorAll('input[type="checkbox"][name="privilegios_rol[]"]');
            checkboxes.forEach(cb => cb.checked = false);
            
            form.reset();
            
            ['nombre_rol', 'descripcion_rol'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            checkboxes.forEach(cb => cb.checked = false);
            
            const modal = new bootstrap.Modal(modalRol);
            modal.show();
        }
        
        const btnNuevoRol = document.getElementById('btnNuevoRol');
        if (btnNuevoRol) {
            btnNuevoRol.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.location.search) window.history.replaceState({}, '', 'roles.php');
                limpiarYAbrirModalRol();
            });
        }
        
        const btnNuevoRolVacio = document.getElementById('btnNuevoRolVacio');
        if (btnNuevoRolVacio) {
            btnNuevoRolVacio.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.location.search) window.history.replaceState({}, '', 'roles.php');
                limpiarYAbrirModalRol();
            });
        }
        
        const modalRol = document.getElementById('modalRol');
        if (modalRol) {
            modalRol.addEventListener('hidden.bs.modal', function() {
                const form = modalRol.querySelector('form');
                if (form) {
                    form.reset();
                    const hiddenId = form.querySelector('input[name="id"]');
                    if (hiddenId) hiddenId.remove();
                    const hiddenAction = form.querySelector('input[name="action"]');
                    if (hiddenAction) hiddenAction.value = 'insert';
                    const modalTitle = modalRol.querySelector('.modal-title');
                    if (modalTitle) modalTitle.textContent = 'Nuevo Rol';
                    const btnSubmit = form.querySelector('button[type="submit"]');
                    if (btnSubmit) {
                        const icon = btnSubmit.querySelector('i');
                        if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                        else btnSubmit.textContent = 'Guardar';
                    }
                    const checkboxes = form.querySelectorAll('input[type="checkbox"][name="privilegios_rol[]"]');
                    checkboxes.forEach(cb => cb.checked = false);
                }
            });
        }
        
        <?php if ($rolEditar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalRol'));
            modal.show();
        });
        <?php endif; ?>
        
        <?php if ($rolPrivilegiosModal): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalPrivilegiosRol'));
            modal.show();
        });
        <?php endif; ?>
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>

