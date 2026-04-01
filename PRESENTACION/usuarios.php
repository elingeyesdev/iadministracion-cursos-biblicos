<?php
require_once '../NEGOCIO/nUsuario.php';
require_once '../NEGOCIO/nRol.php';
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

$auth = new AuthController();
$auth->requireLogin();
$currentUser = $auth->getCurrentUser();
if (!auth::tienePrivilegio('usuarios_ver')) {
    header('Location: dashboard.php');
    exit();
}

$nUsuario = new nUsuario();
$nRol = new nRol();
$message = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
$messageType = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'insert':
                    if (auth::tienePrivilegio('usuarios_crear')) {
                        // rol_id no es obligatorio al crear - se puede asignar después
                        $resultado = $nUsuario->insertar(
                            $_POST['nombre_completo'],
                            $_POST['username'],
                            $_POST['password'],
                            isset($_POST['rol_id']) && !empty($_POST['rol_id']) ? $_POST['rol_id'] : null,
                            isset($_POST['estado']) ? 1 : 0,
                            $_POST['email'] ?? null,
                            $_POST['telefono'] ?? null
                        );
                        if ($resultado['exito']) {
                            header('Location: usuarios.php?tab=usuarios&mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                            exit;
                        } else {
                            $message = $resultado['mensaje'];
                            $messageType = 'danger';
                        }
                    } else {
                        header('Location: usuarios.php?tab=usuarios');
                        exit();
                    }
                    break;
                    
                case 'update':
                    if (auth::tienePrivilegio('usuarios_editar')) {
                        $resultado = $nUsuario->actualizar(
                            $_POST['id'],
                            $_POST['nombre_completo'],
                            $_POST['username'],
                            null, // rol_id ya no se actualiza aquí
                            isset($_POST['estado']) ? 1 : 0,
                            $_POST['email'] ?? null,
                            $_POST['telefono'] ?? null
                        );
                        if ($resultado['exito']) {
                            header('Location: usuarios.php?tab=usuarios&mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                            exit;
                        } else {
                            $message = $resultado['mensaje'];
                            $messageType = 'danger';
                        }
                    } else {
                        header('Location: usuarios.php?tab=usuarios');
                        exit();
                    }
                    break;
                    
                case 'delete':
                    if (auth::tienePrivilegio('usuarios_eliminar')) {
                        $resultado = $nUsuario->eliminar($_POST['id']);
                        if ($resultado['exito']) {
                            header('Location: usuarios.php?tab=usuarios&mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                            exit;
                        } else {
                            $message = $resultado['mensaje'];
                            $messageType = 'danger';
                        }
                    } else {
                        header('Location: usuarios.php?tab=usuarios');
                        exit();
                    }
                    break;
                    
                case 'asignar_privilegios':
                    if (auth::tienePrivilegio('usuarios_editar')) {
                        $privilegios_ids = isset($_POST['privilegios']) ? $_POST['privilegios'] : [];
                        // Log para debug
                        error_log("Asignando privilegios - Usuario ID: " . $_POST['usuario_id']);
                        error_log("Privilegios recibidos: " . print_r($privilegios_ids, true));
                        
                        $resultado = $nUsuario->asignarPrivilegios($_POST['usuario_id'], $privilegios_ids);
                        if ($resultado['exito']) {
                            // Recargar privilegios si es el usuario actual
                            if ($_POST['usuario_id'] == $currentUser['id']) {
                                auth::recargarPrivilegios();
                            }
                            // Redirigir para evitar reenvío
                            header('Location: usuarios.php?tab=usuarios&mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                            exit;
                        } else {
                            $message = $resultado['mensaje'];
                            $messageType = 'danger';
                        }
                    } else {
                        header('Location: usuarios.php?tab=usuarios');
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

// Usar el método buscar que permite filtrar por texto
$usuarios = $nUsuario->buscar($terminoBusqueda, null);

// Obtener datos para editar si se solicita
$usuarioEditar = null;
$usuarioPrivilegios = [];
if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $usuarioEditar = $nUsuario->obtenerPorId($_GET['id']);
    if ($usuarioEditar) {
        $usuarioPrivilegios = $nUsuario->obtenerPrivilegios($_GET['id']);
        $usuarioPrivilegios = array_column($usuarioPrivilegios, 'id');
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
    <title>Gestión de Usuarios - Sistema de Administración de Iglesia</title>
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
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuUsuarios" aria-labelledby="offcanvasMenuUsuariosLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuUsuariosLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('usuarios'); ?>
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
                        <?php echo generarMenu('usuarios'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuUsuarios" aria-controls="offcanvasMenuUsuarios">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-users text-primary"></i> Gestión de Usuarios
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
                                <h5 class="mb-0">Lista de Usuarios</h5>
                                <?php if (auth::tienePrivilegio('usuarios_crear')): ?>
                                <button type="button" class="btn btn-primary" id="btnNuevoUsuario">
                                    <i class="fas fa-plus"></i> Nuevo Usuario
                                </button>
                                <?php endif; ?>
                            </div>
                            <form method="GET" class="mb-3" id="formBuscar">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="buscar" 
                                           placeholder="Buscar por ID, nombre o usuario..." 
                                           value="<?php echo htmlspecialchars($terminoBusqueda); ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    <?php if (!empty($terminoBusqueda)): ?>
                                        <a href="usuarios.php" class="btn btn-outline-danger">
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
                                                    <th>Nombre Completo</th>
                                                    <th>Usuario</th>
                                                    <th>Email</th>
                                                    <th>Teléfono</th>
                                                    <th>Fecha Creación</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($usuarios as $usuario): ?>
                                                    <tr>
                                                        <td><?php echo $usuario['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                                        <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                                                        <td><?php echo htmlspecialchars($usuario['email'] ?? ''); ?></td>
                                                        <td><?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="usuarios.php?action=editar&id=<?php echo $usuario['id']; ?>" 
                                                                   class="btn btn-warning" title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <a href="usuarios.php?action=privilegios&id=<?php echo $usuario['id']; ?>" 
                                                                   class="btn btn-info" title="Asignar Privilegios">
                                                                    <i class="fas fa-key"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-danger" 
                                                                        onclick="confirmarEliminar(<?php echo $usuario['id']; ?>)" title="Eliminar">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
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

    <!-- Modal Usuario -->
    <div class="modal fade" id="modalUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $usuarioEditar ? 'Editar Usuario' : 'Nuevo Usuario'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $usuarioEditar ? 'update' : 'insert'; ?>">
                        <?php if ($usuarioEditar): ?>
                            <input type="hidden" name="id" value="<?php echo $usuarioEditar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="nombre_completo" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" 
                                   value="<?php echo $usuarioEditar ? htmlspecialchars($usuarioEditar['nombre_completo']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario *</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo $usuarioEditar ? htmlspecialchars($usuarioEditar['username']) : ''; ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $usuarioEditar ? htmlspecialchars($usuarioEditar['email'] ?? '') : ''; ?>" 
                                       placeholder="ejemplo@gmail.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo $usuarioEditar ? htmlspecialchars($usuarioEditar['telefono'] ?? '') : ''; ?>" 
                                       placeholder="+1234567890">
                            </div>
                        </div>
                        
                        <div class="mb-3" id="password_container" style="<?php echo $usuarioEditar ? 'display: none;' : ''; ?>">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="password" name="password" <?php echo $usuarioEditar ? '' : 'required'; ?>>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="estado" name="estado" 
                                       <?php echo ($usuarioEditar && $usuarioEditar['estado']) ? 'checked' : 'checked'; ?>>
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

    <!-- Modal Privilegios Usuario -->
    <?php 
    $usuarioPrivilegiosModal = null;
    $privilegiosRolIds = [];
    $privilegiosUsuarioIds = [];
    if (isset($_GET['action']) && $_GET['action'] == 'privilegios' && isset($_GET['id'])) {
        $usuarioPrivilegiosModal = $nUsuario->obtenerPorId($_GET['id']);
        if ($usuarioPrivilegiosModal) {
            // Obtener todos los roles del usuario
            $rolesUsuario = $nUsuario->obtenerRoles($_GET['id']);
            // Obtener privilegios de todos los roles del usuario
            foreach ($rolesUsuario as $rol) {
                $privilegiosRol = $nRol->obtenerPrivilegios($rol['id']);
                $privilegiosRolIds = array_merge($privilegiosRolIds, array_column($privilegiosRol, 'id'));
            }
            // Eliminar duplicados
            $privilegiosRolIds = array_unique($privilegiosRolIds);
            // Obtener privilegios personalizados del usuario
            $privilegiosUsuario = $nUsuario->obtenerPrivilegiosPersonalizados($_GET['id']);
            $privilegiosUsuarioIds = array_column($privilegiosUsuario, 'id');
        }
    }
    ?>
    <?php if ($usuarioPrivilegiosModal): ?>
    <div class="modal fade show" id="modalPrivilegiosUsuario" tabindex="-1" style="display: block;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Privilegios - <?php echo htmlspecialchars($usuarioPrivilegiosModal['nombre_completo']); ?></h5>
                        <a href="usuarios.php" class="btn-close"></a>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="asignar_privilegios">
                        <input type="hidden" name="usuario_id" value="<?php echo $usuarioPrivilegiosModal['id']; ?>">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Nota:</strong> Los privilegios marcados con <span class="badge bg-secondary">(Rol)</span> provienen del rol asignado. 
                            Puede modificar todos los privilegios aquí y los cambios solo afectarán a este usuario específico, no al rol.
                        </div>
                        <div class="row">
                            <?php foreach ($privilegiosPorModulo as $modulo => $privs): ?>
                                <div class="col-md-6 mb-3">
                                    <h6><?php echo ucfirst($modulo); ?></h6>
                                    <?php foreach ($privs as $priv): ?>
                                        <?php 
                                        $esDelRol = in_array($priv['id'], $privilegiosRolIds);
                                        $esPersonalizado = in_array($priv['id'], $privilegiosUsuarioIds);
                                        // Si está en el rol O es personalizado, debe aparecer marcado
                                        $estaMarcado = $esDelRol || $esPersonalizado;
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="privilegios[]" value="<?php echo $priv['id']; ?>"
                                                   id="priv_<?php echo $priv['id']; ?>"
                                                   <?php echo $estaMarcado ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="priv_<?php echo $priv['id']; ?>">
                                                <?php echo htmlspecialchars($priv['descripcion']); ?>
                                                <?php if ($esDelRol && !$esPersonalizado): ?>
                                                    <span class="badge bg-secondary ms-2" title="Proviene del rol">(Rol)</span>
                                                <?php elseif ($esPersonalizado): ?>
                                                    <span class="badge bg-info ms-2" title="Privilegio personalizado">(Personalizado)</span>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Privilegios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de eliminar este usuario?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Función para limpiar y abrir el modal
        function limpiarYAbrirModalUsuario() {
            const modalUsuario = document.getElementById('modalUsuario');
            if (!modalUsuario) return;
            const form = modalUsuario.querySelector('form');
            if (!form) return;
            
            ['nombre_completo', 'username', 'password', 'email', 'telefono'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            
            const hiddenId = form.querySelector('input[name="id"]');
            if (hiddenId) hiddenId.remove();
            const hiddenAction = form.querySelector('input[name="action"]');
            if (hiddenAction) hiddenAction.value = 'insert';
            const modalTitle = modalUsuario.querySelector('.modal-title');
            if (modalTitle) modalTitle.textContent = 'Nuevo Usuario';
            const btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                const icon = btnSubmit.querySelector('i');
                if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                else btnSubmit.textContent = 'Guardar';
            }
            // El campo rol_id ya no existe en el formulario de creación, así que no hay que limpiarlo
            const checkboxEstado = form.querySelector('#estado');
            if (checkboxEstado) checkboxEstado.checked = true;
            const passwordContainer = form.querySelector('#password_container');
            if (passwordContainer) {
                passwordContainer.style.display = 'block';
                const passwordField = form.querySelector('#password');
                if (passwordField) {
                    passwordField.required = true;
                    passwordField.value = '';
                }
            }
            
            form.reset();
            
            ['nombre_completo', 'username', 'password', 'email', 'telefono'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            // El campo rol_id ya no existe en el formulario de creación
            if (checkboxEstado) checkboxEstado.checked = true;
            if (passwordContainer) {
                passwordContainer.style.display = 'block';
                const passwordField = form.querySelector('#password');
                if (passwordField) {
                    passwordField.required = true;
                    passwordField.value = '';
                }
            }
            
            const modal = new bootstrap.Modal(modalUsuario);
            modal.show();
        }
        
        const btnNuevoUsuario = document.getElementById('btnNuevoUsuario');
        if (btnNuevoUsuario) {
            btnNuevoUsuario.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (window.location.search) window.history.replaceState({}, '', 'usuarios.php');
                limpiarYAbrirModalUsuario();
                return false;
            });
        }
        
        const modalUsuario = document.getElementById('modalUsuario');
        if (modalUsuario) {
            modalUsuario.addEventListener('hidden.bs.modal', function() {
                const form = modalUsuario.querySelector('form');
                if (form) {
                    form.reset();
                    const hiddenId = form.querySelector('input[name="id"]');
                    if (hiddenId) hiddenId.remove();
                    const hiddenAction = form.querySelector('input[name="action"]');
                    if (hiddenAction) hiddenAction.value = 'insert';
                    const modalTitle = modalUsuario.querySelector('.modal-title');
                    if (modalTitle) modalTitle.textContent = 'Nuevo Usuario';
                    const btnSubmit = form.querySelector('button[type="submit"]');
                    if (btnSubmit) {
                        const icon = btnSubmit.querySelector('i');
                        if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                        else btnSubmit.textContent = 'Guardar';
                    }
                    const passwordContainer = form.querySelector('#password_container');
                    if (passwordContainer) {
                        passwordContainer.style.display = 'block';
                        const passwordField = form.querySelector('#password');
                        if (passwordField) {
                            passwordField.required = true;
                            passwordField.value = '';
                        }
                    }
                }
            });
        }
        
        <?php if ($usuarioEditar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalUsuario'));
            modal.show();
        });
        <?php endif; ?>
        
        <?php if ($usuarioPrivilegiosModal): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalPrivilegiosUsuario'));
            modal.show();
        });
        <?php endif; ?>
        
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>

