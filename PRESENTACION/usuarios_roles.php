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
if (!auth::tienePrivilegio('usuarios_editar')) {
    header('Location: dashboard.php');
    exit();
}

$nUsuario = new nUsuario();
$nRol = new nRol();
$message = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
$messageType = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Procesar asignación de roles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] == 'asignar_roles') {
            if (auth::tienePrivilegio('usuarios_editar')) {
                $usuario_id = (int)$_POST['usuario_id'];
                $roles_ids = isset($_POST['roles']) && is_array($_POST['roles']) ? $_POST['roles'] : [];
                
                $resultado = $nUsuario->asignarRoles($usuario_id, $roles_ids);
                
                if ($resultado['exito']) {
                    // Recargar privilegios si es el usuario actual
                    if ($usuario_id == $currentUser['id']) {
                        auth::recargarPrivilegios();
                    }
                    header('Location: usuarios_roles.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                    exit;
                } else {
                    $message = $resultado['mensaje'];
                    $messageType = 'danger';
                }
            } else {
                header('Location: usuarios_roles.php');
                exit();
            }
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Procesar búsqueda
$terminoBusqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$filtroRol = isset($_GET['filtro_rol']) && !empty($_GET['filtro_rol']) ? (int)$_GET['filtro_rol'] : null;

// Usar el método buscar que permite filtrar por texto y rol
$usuarios = $nUsuario->buscar($terminoBusqueda, $filtroRol);

// Obtener listas
$roles = $nRol->listar();

// Obtener usuario para asignar roles si se solicita
$usuarioAsignarRol = null;
$rolesUsuarioActual = [];
if (isset($_GET['action']) && $_GET['action'] == 'asignar' && isset($_GET['id'])) {
    $usuarioAsignarRol = $nUsuario->obtenerPorId($_GET['id']);
    if ($usuarioAsignarRol) {
        $rolesUsuarioActual = $nUsuario->obtenerRoles($_GET['id']);
        $rolesUsuarioActual = array_column($rolesUsuarioActual, 'id');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios y Roles - Sistema de Administración de Iglesia</title>
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
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuUsuariosRoles" aria-labelledby="offcanvasMenuUsuariosRolesLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuUsuariosRolesLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('usuarios_roles'); ?>
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
                        <?php echo generarMenu('usuarios_roles'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuUsuariosRoles" aria-controls="offcanvasMenuUsuariosRoles">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-user-shield text-primary"></i> Asignación de Roles
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
                            <h5 class="mb-3">Gestión de Roles por Usuario</h5>
                            <form method="GET" class="mb-3" id="formBuscar">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="buscar" 
                                           placeholder="Buscar por ID, nombre o usuario..." 
                                           value="<?php echo htmlspecialchars($terminoBusqueda); ?>">
                                    <input type="hidden" name="filtro_rol" id="filtro_rol_hidden" value="<?php echo $filtroRol ? $filtroRol : ''; ?>">
                                    <div class="btn-group">
                                        <button class="btn btn-outline-secondary" type="button" id="btnFiltroRol" data-bs-toggle="dropdown" aria-expanded="false" title="Filtrar por rol">
                                            <i class="fas fa-filter"></i>
                                            <?php if ($filtroRol): ?>
                                                <span class="badge bg-primary ms-1" id="badgeFiltroRol">1</span>
                                            <?php endif; ?>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="btnFiltroRol">
                                            <li><h6 class="dropdown-header">Filtrar por Rol</h6></li>
                                            <li><a class="dropdown-item <?php echo empty($filtroRol) ? 'active' : ''; ?>" href="#" data-rol-id="" onclick="seleccionarRol(event, '');">
                                                <i class="fas fa-check <?php echo empty($filtroRol) ? '' : 'd-none'; ?>"></i> Todos los roles
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <?php foreach ($roles as $rol): ?>
                                                <li><a class="dropdown-item <?php echo ($filtroRol == $rol['id']) ? 'active' : ''; ?>" href="#" data-rol-id="<?php echo $rol['id']; ?>" onclick="seleccionarRol(event, <?php echo $rol['id']; ?>);">
                                                    <i class="fas fa-check <?php echo ($filtroRol == $rol['id']) ? '' : 'd-none'; ?>"></i> <?php echo htmlspecialchars($rol['nombre']); ?>
                                                </a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    <?php if (!empty($terminoBusqueda) || !empty($filtroRol)): ?>
                                        <a href="usuarios_roles.php" class="btn btn-outline-danger">
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
                                                    <th>Roles Asignados</th>
                                                    <th>Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($usuarios as $usuario): ?>
                                                    <?php 
                                                    $rolesUsuario = $nUsuario->obtenerRoles($usuario['id']);
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $usuario['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                                        <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                                                        <td>
                                                            <?php if (!empty($rolesUsuario)): ?>
                                                                <?php foreach ($rolesUsuario as $rol): ?>
                                                                    <span class="badge bg-info me-1">
                                                                        <?php echo htmlspecialchars($rol['nombre']); ?>
                                                                    </span>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Sin roles asignados</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <a href="usuarios_roles.php?action=asignar&id=<?php echo $usuario['id']; ?>" 
                                                               class="btn btn-primary btn-sm" title="Asignar/Cambiar Roles">
                                                                <i class="fas fa-user-shield"></i> Asignar Roles
                                                            </a>
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

    <!-- Modal Asignar Roles -->
    <?php if ($usuarioAsignarRol): ?>
    <div class="modal fade show" id="modalAsignarRol" tabindex="-1" style="display: block;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Roles - <?php echo htmlspecialchars($usuarioAsignarRol['nombre_completo']); ?></h5>
                    <a href="usuarios_roles.php" class="btn-close"></a>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="asignar_roles">
                        <input type="hidden" name="usuario_id" value="<?php echo $usuarioAsignarRol['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Seleccionar Roles</label>
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                <?php if (empty($roles)): ?>
                                    <p class="text-muted">No hay roles disponibles.</p>
                                <?php else: ?>
                                    <?php foreach ($roles as $rol): ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="roles[]" value="<?php echo $rol['id']; ?>"
                                                   id="rol_<?php echo $rol['id']; ?>"
                                                   <?php echo in_array($rol['id'], $rolesUsuarioActual) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="rol_<?php echo $rol['id']; ?>">
                                                <strong><?php echo htmlspecialchars($rol['nombre']); ?></strong>
                                                <?php if (!empty($rol['descripcion'])): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($rol['descripcion']); ?></small>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <small class="form-text text-muted">
                                Seleccione uno o más roles para asignar al usuario. Puede seleccionar múltiples roles.
                            </small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota:</strong> Los privilegios del usuario se combinarán de todos los roles asignados. Los privilegios personalizados también se mantendrán.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="usuarios_roles.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Roles</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para seleccionar rol en el filtro
        function seleccionarRol(event, rolId) {
            event.preventDefault();
            
            // Actualizar el input hidden
            const filtroRolHidden = document.getElementById('filtro_rol_hidden');
            if (filtroRolHidden) {
                filtroRolHidden.value = rolId;
            }
            
            // Actualizar los iconos de check en el dropdown
            const dropdownItems = document.querySelectorAll('#btnFiltroRol + ul .dropdown-item');
            dropdownItems.forEach(item => {
                const icon = item.querySelector('.fa-check');
                if (icon) {
                    icon.classList.add('d-none');
                }
                item.classList.remove('active');
            });
            
            // Marcar el item seleccionado
            const selectedItem = event.currentTarget;
            selectedItem.classList.add('active');
            const selectedIcon = selectedItem.querySelector('.fa-check');
            if (selectedIcon) {
                selectedIcon.classList.remove('d-none');
            }
            
            // Actualizar el badge en el botón
            const btnFiltroRol = document.getElementById('btnFiltroRol');
            let badgeFiltroRol = document.getElementById('badgeFiltroRol');
            
            if (rolId) {
                if (!badgeFiltroRol) {
                    badgeFiltroRol = document.createElement('span');
                    badgeFiltroRol.id = 'badgeFiltroRol';
                    badgeFiltroRol.className = 'badge bg-primary ms-1';
                    btnFiltroRol.appendChild(badgeFiltroRol);
                }
                badgeFiltroRol.textContent = '1';
            } else {
                if (badgeFiltroRol) {
                    badgeFiltroRol.remove();
                }
            }
            
            // Cerrar el dropdown
            const dropdown = bootstrap.Dropdown.getInstance(btnFiltroRol);
            if (dropdown) {
                dropdown.hide();
            }
            
            // Auto-submit del formulario para búsqueda automática
            document.getElementById('formBuscar').submit();
        }
        
        <?php if ($usuarioAsignarRol): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalAsignarRol'));
            modal.show();
        });
        <?php endif; ?>
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>

