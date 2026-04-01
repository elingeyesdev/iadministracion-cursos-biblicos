<?php
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once '../NEGOCIO/nMinisterio.php';
require_once '../NEGOCIO/nMiembro.php';
require_once '../DATOS/dMinisterio.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

$auth = new AuthController();
$auth->requireLogin();

// Verificar privilegios
if (!auth::tienePrivilegio('ministerios_ver')) {
    header('Location: dashboard.php');
    exit();
}

$nMinisterio = new nMinisterio();
$nMiembro = new nMiembro();
$message = '';
$messageType = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'insert':
                    if (!auth::tienePrivilegio('ministerios_crear')) {
                        header('Location: ministerios.php');
                        exit();
                    }
                    $lider_id = !empty($_POST['lider_id']) ? (int)$_POST['lider_id'] : null;
                    $miembros_ids = isset($_POST['miembros_ids']) && is_array($_POST['miembros_ids']) 
                        ? array_map('intval', $_POST['miembros_ids']) 
                        : [];
                    $resultado = $nMinisterio->insertar(
                        $_POST['nombre'] ?? '',
                        $_POST['descripcion'] ?? '',
                        $lider_id,
                        $miembros_ids
                    );
                    if ($resultado && $resultado['exito']) {
                        $message = $resultado['mensaje'];
                        $messageType = 'success';
                    } else {
                        $message = $resultado['mensaje'] ?? 'Error al registrar el ministerio';
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'update':
                    if (!auth::tienePrivilegio('ministerios_editar')) {
                        header('Location: ministerios.php');
                        exit();
                    }
                    $lider_id = !empty($_POST['lider_id']) ? (int)$_POST['lider_id'] : null;
                    $miembros_ids = isset($_POST['miembros_ids']) && is_array($_POST['miembros_ids']) 
                        ? array_map('intval', $_POST['miembros_ids']) 
                        : [];
                    $resultado = $nMinisterio->actualizar(
                        $_POST['id'] ?? '',
                        $_POST['nombre'] ?? '',
                        $_POST['descripcion'] ?? '',
                        $lider_id,
                        $_POST['estado'] ?? 'activo',
                        $miembros_ids
                    );
                    if ($resultado && $resultado['exito']) {
                        $message = $resultado['mensaje'];
                        $messageType = 'success';
                    } else {
                        $message = $resultado['mensaje'] ?? 'Error al actualizar el ministerio';
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'delete':
                    if (!auth::tienePrivilegio('ministerios_eliminar')) {
                        header('Location: ministerios.php');
                        exit();
                    }
                    $resultado = $nMinisterio->eliminar($_POST['id']);
                    if ($resultado && $resultado['exito']) {
                        $message = $resultado['mensaje'];
                        $messageType = 'success';
                    } else {
                        $message = $resultado['mensaje'] ?? 'Error al eliminar el ministerio';
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
        $ministerio = $nMinisterio->obtenerPorId($terminoBusqueda);
        $ministerios = $ministerio ? [$ministerio] : [];
    } else {
        $ministerios = $nMinisterio->buscarPorNombre($terminoBusqueda);
    }
} else {
$ministerios = $nMinisterio->listar();
}

// Obtener lista de miembros para el select
$miembros = $nMiembro->listar();

// Obtener datos para editar si se solicita
$ministerioEditar = null;
$miembrosMinisterio = [];
if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $ministerioEditar = $nMinisterio->obtenerPorId($_GET['id']);
    if ($ministerioEditar) {
        $dMinisterio = new dMinisterio();
        $miembrosMinisterio = $dMinisterio->listarMiembros($ministerioEditar['id']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ministerios - Sistema de Administración de Iglesia</title>
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
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuMinisterios" aria-labelledby="offcanvasMenuMinisteriosLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuMinisteriosLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('ministerios'); ?>
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
                        <?php echo generarMenu('ministerios'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuMinisterios" aria-controls="offcanvasMenuMinisterios">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-praying-hands text-primary"></i> Gestión de Ministerios
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
                                <h5 class="mb-0">Lista de Ministerios</h5>
                                <?php if (tieneAcceso('ministerios_crear')): ?>
                                <a href="ministerios.php" class="btn btn-primary" id="btnNuevoMinisterio">
                                    <i class="fas fa-plus"></i> Nuevo Ministerio
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
                                        <a href="ministerios.php" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Ministries Table -->
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
                                                    <th>Líder</th>
                                                    <th>Contacto</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ministerios as $ministerio): ?>
                                                    <tr>
                                                        <td><?php echo $ministerio['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($ministerio['nombre']); ?></td>
                                                        <td><?php echo htmlspecialchars(substr($ministerio['descripcion'] ?? '', 0, 50)) . (strlen($ministerio['descripcion'] ?? '') > 50 ? '...' : ''); ?></td>
                                                        <td>
                                                            <?php 
                                                            $liderNombre = trim(($ministerio['lider_nombre'] ?? '') . ' ' . ($ministerio['lider_apellido'] ?? ''));
                                                            echo $liderNombre ? htmlspecialchars($liderNombre) : '<span class="text-muted">Sin líder</span>';
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $contacto = $ministerio['lider_telefono'] ?? $ministerio['contacto_lider'] ?? '';
                                                            echo $contacto ? htmlspecialchars($contacto) : '<span class="text-muted">N/A</span>';
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <?php if (tieneAcceso('ministerios_editar')): ?>
                                                                <a href="ministerios.php?action=editar&id=<?php echo $ministerio['id']; ?>" 
                                                                   class="btn btn-warning" 
                                                                   title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <?php endif; ?>
                                                                <?php if (tieneAcceso('ministerios_eliminar')): ?>
                                                                <button type="button" 
                                                                        class="btn btn-danger" 
                                                                        onclick="confirmarEliminar(<?php echo $ministerio['id']; ?>)"
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

    <!-- Ministry Modal -->
    <div class="modal fade" id="modalMinisterio" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $ministerioEditar ? 'Editar Ministerio' : 'Nuevo Ministerio'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $ministerioEditar ? 'update' : 'insert'; ?>">
                        <?php if ($ministerioEditar): ?>
                            <input type="hidden" name="id" value="<?php echo $ministerioEditar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo $ministerioEditar ? htmlspecialchars($ministerioEditar['nombre']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="buscar_lider" class="form-label">Líder</label>
                                <input type="hidden" id="lider_id" name="lider_id" value="<?php echo ($ministerioEditar && isset($ministerioEditar['lider_id'])) ? $ministerioEditar['lider_id'] : ''; ?>">
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control" 
                                           id="buscar_lider" 
                                           placeholder="Buscar miembro líder..." 
                                           autocomplete="off"
                                           value="<?php 
                                           if ($ministerioEditar && isset($ministerioEditar['lider_id'])) {
                                               $liderNombre = trim(($ministerioEditar['lider_nombre'] ?? '') . ' ' . ($ministerioEditar['lider_apellido'] ?? ''));
                                               echo htmlspecialchars($liderNombre);
                                           }
                                           ?>">
                                    <div id="resultados_busqueda_lider" class="list-group position-absolute w-100" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto; background: white; border: 1px solid #ddd; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); margin-top: 2px;"></div>
                                </div>
                                <small class="text-muted">Escriba al menos 2 caracteres para buscar. Seleccione un miembro para asignarlo como líder.</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="horario" class="form-label">Horario</label>
                                <input type="text" class="form-control" id="horario" name="horario" 
                                       value="<?php echo $ministerioEditar ? htmlspecialchars($ministerioEditar['horario'] ?? '') : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $ministerioEditar ? htmlspecialchars($ministerioEditar['descripcion'] ?? '') : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="buscar_miembros" class="form-label">Miembros del Ministerio</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control" 
                                           id="buscar_miembros" 
                                           placeholder="Buscar miembros para agregar..." 
                                           autocomplete="off">
                                    <div id="resultados_busqueda_miembros" class="list-group position-absolute w-100" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto; background: white; border: 1px solid #ddd; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); margin-top: 2px;"></div>
                                </div>
                                <small class="text-muted">Escriba al menos 2 caracteres para buscar. Seleccione miembros para agregarlos al ministerio.</small>
                                <div id="miembros_seleccionados" class="mt-3">
                                    <?php if (!empty($miembrosMinisterio)): ?>
                                        <?php foreach ($miembrosMinisterio as $miembro): ?>
                                            <span class="badge bg-primary me-2 mb-2" data-miembro-id="<?php echo $miembro['miembro_id']; ?>">
                                                <?php echo htmlspecialchars(($miembro['nombres'] ?? '') . ' ' . ($miembro['apellidos'] ?? '')); ?>
                                                <button type="button" class="btn-close btn-close-white ms-1" onclick="removerMiembro(<?php echo $miembro['miembro_id']; ?>)"></button>
                                                <input type="hidden" name="miembros_ids[]" value="<?php echo $miembro['miembro_id']; ?>">
                                            </span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="activo" <?php echo $ministerioEditar && isset($ministerioEditar['estado']) && $ministerioEditar['estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="inactivo" <?php echo $ministerioEditar && isset($ministerioEditar['estado']) && $ministerioEditar['estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $ministerioEditar ? 'Actualizar' : 'Guardar'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de que desea eliminar este ministerio?')) {
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
        function limpiarYAbrirModal() {
            const modalMinisterio = document.getElementById('modalMinisterio');
            if (!modalMinisterio) return;
            
            const form = modalMinisterio.querySelector('form');
            if (!form) return;
            
            // PRIMERO: Limpiar todos los campos ANTES de abrir el modal
            
            // Limpiar campos de texto manualmente (antes de reset)
            const nombreInput = form.querySelector('#nombre');
            if (nombreInput) nombreInput.value = '';
            
            const descripcionTextarea = form.querySelector('#descripcion');
            if (descripcionTextarea) descripcionTextarea.value = '';
            
            const horarioInput = form.querySelector('#horario');
            if (horarioInput) horarioInput.value = '';
            
            // Limpiar el campo hidden de id si existe
            const hiddenId = form.querySelector('input[name="id"]');
            if (hiddenId) {
                hiddenId.remove();
            }
            
            // Cambiar el action a insert
            const hiddenAction = form.querySelector('input[name="action"]');
            if (hiddenAction) {
                hiddenAction.value = 'insert';
            }
            
            // Cambiar el título del modal
            const modalTitle = modalMinisterio.querySelector('.modal-title');
            if (modalTitle) {
                modalTitle.textContent = 'Nuevo Ministerio';
            }
            
            // Cambiar el texto del botón de guardar
            const btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                const icon = btnSubmit.querySelector('i');
                if (icon) {
                    btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                } else {
                    btnSubmit.textContent = 'Guardar';
                }
            }
            
            // Limpiar el buscador de líder
            const inputBuscarLider = form.querySelector('#buscar_lider');
            const hiddenLiderId = form.querySelector('#lider_id');
            const resultadosBusquedaLider = form.querySelector('#resultados_busqueda_lider');
            if (inputBuscarLider) inputBuscarLider.value = '';
            if (hiddenLiderId) hiddenLiderId.value = '';
            if (resultadosBusquedaLider) {
                resultadosBusquedaLider.style.display = 'none';
                resultadosBusquedaLider.innerHTML = '';
            }
            
            // Limpiar miembros seleccionados
            limpiarMiembros();
            
            // Resetear el select de estado
            const selectEstado = form.querySelector('#estado');
            if (selectEstado) {
                selectEstado.value = 'activo';
            }
            
            // Ahora hacer reset del formulario (por si acaso)
            form.reset();
            
            // Y volver a limpiar los campos después del reset (por si el reset no funcionó)
            if (nombreInput) nombreInput.value = '';
            if (descripcionTextarea) descripcionTextarea.value = '';
            if (horarioInput) horarioInput.value = '';
            if (inputBuscarLider) inputBuscarLider.value = '';
            if (hiddenLiderId) hiddenLiderId.value = '';
            if (resultadosBusquedaLider) {
                resultadosBusquedaLider.style.display = 'none';
                resultadosBusquedaLider.innerHTML = '';
            }
            if (selectEstado) selectEstado.value = 'activo';
            
            // DESPUÉS: Abrir el modal
            const modal = new bootstrap.Modal(modalMinisterio);
            modal.show();
        }
        
        // Manejar el botón "Nuevo Ministerio"
        const btnNuevoMinisterio = document.getElementById('btnNuevoMinisterio');
        if (btnNuevoMinisterio) {
            btnNuevoMinisterio.addEventListener('click', function(e) {
                e.preventDefault();
                // Si hay parámetros en la URL, limpiarlos y abrir el modal
                if (window.location.search) {
                    // Cambiar la URL sin recargar la página
                    window.history.replaceState({}, '', 'ministerios.php');
                }
                // Abrir el modal directamente
                limpiarYAbrirModal();
            });
        }
        
        // Si la URL tiene ?nuevo=1, abrir el modal automáticamente
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('nuevo') === '1') {
            // Limpiar el parámetro de la URL
            window.history.replaceState({}, '', 'ministerios.php');
            // Abrir el modal
            limpiarYAbrirModal();
        }
        
        // Función para limpiar buscador de líder
        const limpiarBuscadorLider = function() {
            const inputBuscarLider = document.getElementById('buscar_lider');
            const hiddenLiderId = document.getElementById('lider_id');
            const resultadosBusquedaLider = document.getElementById('resultados_busqueda_lider');
            if (inputBuscarLider) inputBuscarLider.value = '';
            if (hiddenLiderId) hiddenLiderId.value = '';
            if (resultadosBusquedaLider) {
                resultadosBusquedaLider.style.display = 'none';
                resultadosBusquedaLider.innerHTML = '';
            }
        };
        
        // Limpiar formulario cuando se cierra el modal
        const modalMinisterio = document.getElementById('modalMinisterio');
        if (modalMinisterio) {
            modalMinisterio.addEventListener('hidden.bs.modal', function() {
                const form = modalMinisterio.querySelector('form');
                if (form) {
                    form.reset();
                    
                    // Limpiar campos manualmente
                    const nombreInput = form.querySelector('#nombre');
                    if (nombreInput) nombreInput.value = '';
                    
                    const descripcionTextarea = form.querySelector('#descripcion');
                    if (descripcionTextarea) descripcionTextarea.value = '';
                    
                    const horarioInput = form.querySelector('#horario');
                    if (horarioInput) horarioInput.value = '';
                    
                    const hiddenId = form.querySelector('input[name="id"]');
                    if (hiddenId) {
                        hiddenId.remove();
                    }
                    const hiddenAction = form.querySelector('input[name="action"]');
                    if (hiddenAction) {
                        hiddenAction.value = 'insert';
                    }
                    const modalTitle = modalMinisterio.querySelector('.modal-title');
                    if (modalTitle) {
                        modalTitle.textContent = 'Nuevo Ministerio';
                    }
                    
                    // Cambiar el texto del botón de guardar
                    const btnSubmit = form.querySelector('button[type="submit"]');
                    if (btnSubmit) {
                        const icon = btnSubmit.querySelector('i');
                        if (icon) {
                            btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                        } else {
                            btnSubmit.textContent = 'Guardar';
                        }
                    }
                    
                    // Limpiar buscador de líder
                    limpiarBuscadorLider();
                    
                    // Limpiar miembros seleccionados
                    limpiarMiembros();
                    
                    const selectEstado = form.querySelector('#estado');
                    if (selectEstado) {
                        selectEstado.value = 'activo';
                    }
                }
            });
        }
        
        // Búsqueda de líder en tiempo real
        let timeoutBusquedaLider = null;
        const inputBuscarLider = document.getElementById('buscar_lider');
        const resultadosBusquedaLider = document.getElementById('resultados_busqueda_lider');
        const hiddenLiderId = document.getElementById('lider_id');
        
        if (inputBuscarLider) {
            inputBuscarLider.addEventListener('input', function() {
                const termino = this.value.trim();
                
                // Limpiar timeout anterior
                if (timeoutBusquedaLider) {
                    clearTimeout(timeoutBusquedaLider);
                }
                
                // Ocultar resultados si el término es muy corto
                if (termino.length < 2) {
                    if (resultadosBusquedaLider) {
                        resultadosBusquedaLider.style.display = 'none';
                        resultadosBusquedaLider.innerHTML = '';
                    }
                    if (hiddenLiderId) {
                        hiddenLiderId.value = '';
                    }
                    return;
                }
                
                // Buscar después de 300ms de inactividad
                timeoutBusquedaLider = setTimeout(function() {
                    buscarMiembrosLider(termino);
                }, 300);
            });
            
            // Ocultar resultados al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (resultadosBusquedaLider && !inputBuscarLider.contains(e.target) && !resultadosBusquedaLider.contains(e.target)) {
                    resultadosBusquedaLider.style.display = 'none';
                }
            });
        }
        
        function buscarMiembrosLider(termino) {
            if (!termino || termino.length < 2) return;
            
            fetch('ajax_buscar_miembros.php?q=' + encodeURIComponent(termino))
                .then(response => response.json())
                .then(data => {
                    if (!resultadosBusquedaLider) return;
                    
                    resultadosBusquedaLider.innerHTML = '';
                    
                    if (data.length === 0) {
                        resultadosBusquedaLider.innerHTML = '<div class="list-group-item text-muted">No se encontraron miembros</div>';
                        resultadosBusquedaLider.style.display = 'block';
                        return;
                    }
                    
                    data.forEach(function(miembro) {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.innerHTML = '<strong>' + miembro.nombre + '</strong>';
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            seleccionarLider(miembro);
                        });
                        resultadosBusquedaLider.appendChild(item);
                    });
                    
                    resultadosBusquedaLider.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error al buscar miembros:', error);
                });
        }
        
        function seleccionarLider(miembro) {
            if (inputBuscarLider) {
                inputBuscarLider.value = miembro.nombre;
            }
            if (hiddenLiderId) {
                hiddenLiderId.value = miembro.id;
            }
            if (resultadosBusquedaLider) {
                resultadosBusquedaLider.style.display = 'none';
                resultadosBusquedaLider.innerHTML = '';
            }
        }
        
        // Búsqueda de miembros en tiempo real
        let timeoutBusquedaMiembros = null;
        const inputBuscarMiembros = document.getElementById('buscar_miembros');
        const resultadosBusquedaMiembros = document.getElementById('resultados_busqueda_miembros');
        const miembrosSeleccionadosDiv = document.getElementById('miembros_seleccionados');
        const miembrosSeleccionados = new Set();
        
        // Inicializar miembros ya seleccionados
        <?php if (!empty($miembrosMinisterio)): ?>
            <?php foreach ($miembrosMinisterio as $miembro): ?>
                miembrosSeleccionados.add(<?php echo $miembro['miembro_id']; ?>);
            <?php endforeach; ?>
        <?php endif; ?>
        
        if (inputBuscarMiembros) {
            inputBuscarMiembros.addEventListener('input', function() {
                const termino = this.value.trim();
                
                // Limpiar timeout anterior
                if (timeoutBusquedaMiembros) {
                    clearTimeout(timeoutBusquedaMiembros);
                }
                
                // Ocultar resultados si el término es muy corto
                if (termino.length < 2) {
                    if (resultadosBusquedaMiembros) {
                        resultadosBusquedaMiembros.style.display = 'none';
                        resultadosBusquedaMiembros.innerHTML = '';
                    }
                    return;
                }
                
                // Buscar después de 300ms de inactividad
                timeoutBusquedaMiembros = setTimeout(function() {
                    buscarMiembros(termino);
                }, 300);
            });
            
            // Ocultar resultados al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (resultadosBusquedaMiembros && !inputBuscarMiembros.contains(e.target) && !resultadosBusquedaMiembros.contains(e.target)) {
                    resultadosBusquedaMiembros.style.display = 'none';
                }
            });
        }
        
        function buscarMiembros(termino) {
            if (!termino || termino.length < 2) return;
            
            fetch('ajax_buscar_miembros.php?q=' + encodeURIComponent(termino))
                .then(response => response.json())
                .then(data => {
                    if (!resultadosBusquedaMiembros) return;
                    
                    resultadosBusquedaMiembros.innerHTML = '';
                    
                    if (data.length === 0) {
                        resultadosBusquedaMiembros.innerHTML = '<div class="list-group-item text-muted">No se encontraron miembros</div>';
                        resultadosBusquedaMiembros.style.display = 'block';
                        return;
                    }
                    
                    data.forEach(function(miembro) {
                        // Omitir si ya está seleccionado
                        if (miembrosSeleccionados.has(miembro.id)) {
                            return;
                        }
                        
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.innerHTML = '<strong>' + miembro.nombre + '</strong>';
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            agregarMiembro(miembro);
                        });
                        resultadosBusquedaMiembros.appendChild(item);
                    });
                    
                    resultadosBusquedaMiembros.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error al buscar miembros:', error);
                });
        }
        
        function agregarMiembro(miembro) {
            if (miembrosSeleccionados.has(miembro.id)) {
                return;
            }
            
            miembrosSeleccionados.add(miembro.id);
            
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary me-2 mb-2';
            badge.setAttribute('data-miembro-id', miembro.id);
            badge.innerHTML = miembro.nombre + 
                '<button type="button" class="btn-close btn-close-white ms-1" onclick="removerMiembro(' + miembro.id + ')"></button>' +
                '<input type="hidden" name="miembros_ids[]" value="' + miembro.id + '">';
            
            if (miembrosSeleccionadosDiv) {
                miembrosSeleccionadosDiv.appendChild(badge);
            }
            
            // Limpiar el campo de búsqueda
            if (inputBuscarMiembros) {
                inputBuscarMiembros.value = '';
            }
            if (resultadosBusquedaMiembros) {
                resultadosBusquedaMiembros.style.display = 'none';
                resultadosBusquedaMiembros.innerHTML = '';
            }
        }
        
        function removerMiembro(miembroId) {
            miembrosSeleccionados.delete(miembroId);
            const badge = miembrosSeleccionadosDiv.querySelector('[data-miembro-id="' + miembroId + '"]');
            if (badge) {
                badge.remove();
            }
        }
        
        // Limpiar miembros seleccionados al limpiar el formulario
        const limpiarMiembros = function() {
            miembrosSeleccionados.clear();
            if (miembrosSeleccionadosDiv) {
                miembrosSeleccionadosDiv.innerHTML = '';
            }
        };
        
        // Auto-abrir modal si hay datos para editar
        <?php if ($ministerioEditar): ?>
            const modal = new bootstrap.Modal(document.getElementById('modalMinisterio'));
            modal.show();
        <?php endif; ?>
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>