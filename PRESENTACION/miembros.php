<?php
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once '../NEGOCIO/nMiembro.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

$auth = new AuthController();
$auth->requireLogin();

// Verificar privilegios
if (!auth::tienePrivilegio('miembros_ver')) {
    header('Location: dashboard.php');
    exit();
}

$nMiembro = new nMiembro();
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
                    if (!auth::tienePrivilegio('miembros_crear')) {
                        header('Location: miembros.php');
                        exit();
                    }
                    $resultado = $nMiembro->insertar(
                        $_POST['nombres'],
                        $_POST['apellidos'],
                        $_POST['email'],
                        $_POST['telefono'] ?? '',
                        $_POST['direccion'] ?? '',
                        $_POST['fecha_nacimiento'] ?? null,
                        $_POST['genero'],
                        $_POST['estado_civil'] ?? '',
                        $_POST['profesion'] ?? null
                    );
                    if ($resultado['exito']) {
                        // Redirigir para cerrar el modal y evitar reenvío del formulario
                        header('Location: miembros.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                        exit();
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'update':
                    if (!auth::tienePrivilegio('miembros_editar')) {
                        header('Location: miembros.php');
                        exit();
                    }
                    $resultado = $nMiembro->actualizar(
                        $_POST['id'],
                        $_POST['nombres'],
                        $_POST['apellidos'],
                        $_POST['email'],
                        $_POST['telefono'] ?? '',
                        $_POST['direccion'] ?? '',
                        $_POST['fecha_nacimiento'] ?? null,
                        $_POST['genero'],
                        $_POST['estado_civil'] ?? '',
                        $_POST['profesion'] ?? null,
                        1  // Siempre mantener estado = 1 (activo) al actualizar
                    );
                    if ($resultado['exito']) {
                        // Redirigir para cerrar el modal y evitar reenvío del formulario
                        header('Location: miembros.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                        exit();
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'delete':
                    if (!auth::tienePrivilegio('miembros_eliminar')) {
                        header('Location: miembros.php');
                        exit();
                    }
                    $resultado = $nMiembro->eliminar($_POST['id']);
                    if ($resultado['exito']) {
                        $message = $resultado['mensaje'];
                        $messageType = 'success';
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
        $miembro = $nMiembro->obtenerPorId($terminoBusqueda);
        $miembros = $miembro ? [$miembro] : [];
    } else {
        $miembros = $nMiembro->buscarPorNombre($terminoBusqueda);
    }
} else {
    $miembros = $nMiembro->listar();
}

// Obtener datos para editar si se solicita
$miembroEditar = null;
if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $miembroEditar = $nMiembro->obtenerPorId($_GET['id']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Miembros - Sistema de Administración de Iglesia</title>
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
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuMiembros" aria-labelledby="offcanvasMenuMiembrosLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuMiembrosLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('miembros'); ?>
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
                        <?php echo generarMenu('miembros'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuMiembros" aria-controls="offcanvasMenuMiembros">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-users text-primary"></i> Gestión de Miembros
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
                                <h5 class="mb-0">Lista de Miembros</h5>
                                <div class="btn-group">
                                <?php if (tieneAcceso('miembros_crear')): ?>
                                    <button type="button" class="btn btn-success" id="btnCompartirFormulario" title="Compartir formulario de registro">
                                        <i class="fas fa-share-alt"></i> Compartir Formulario
                                    </button>
                                <a href="miembros.php" class="btn btn-primary" id="btnNuevoMiembro">
                                    <i class="fas fa-plus"></i> Nuevo Miembro
                                </a>
                                <?php endif; ?>
                                </div>
                            </div>
                            <form method="GET" class="mb-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="buscar" 
                                           placeholder="Buscar por ID, nombre o apellido..." 
                                           value="<?php echo htmlspecialchars($terminoBusqueda); ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    <?php if (!empty($terminoBusqueda)): ?>
                                        <a href="miembros.php" class="btn btn-outline-danger">
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
                                                    <th>Nombres</th>
                                                    <th>Apellidos</th>
                                                    <th>Email</th>
                                                    <th>Teléfono</th>
                                                    <th>Género</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($miembros as $miembro): ?>
                                                    <tr>
                                                        <td><?php echo $miembro['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($miembro['nombres']); ?></td>
                                                        <td><?php echo htmlspecialchars($miembro['apellidos']); ?></td>
                                                        <td><?php echo htmlspecialchars($miembro['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($miembro['telefono'] ?? ''); ?></td>
                                                        <td><?php echo $miembro['genero'] == 'M' ? 'Masculino' : 'Femenino'; ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <?php if (tieneAcceso('miembros_editar')): ?>
                                                                <a href="miembros.php?action=editar&id=<?php echo $miembro['id']; ?>" 
                                                                   class="btn btn-warning" title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <?php endif; ?>
                                                                <?php if (tieneAcceso('miembros_eliminar')): ?>
                                                                <button type="button" class="btn btn-danger" 
                                                                        onclick="confirmarEliminar(<?php echo $miembro['id']; ?>)" title="Eliminar">
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

    <!-- Modal Miembro -->
    <div class="modal fade" id="modalMiembro" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $miembroEditar ? 'Editar Miembro' : 'Nuevo Miembro'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $miembroEditar ? 'update' : 'insert'; ?>">
                        <?php if ($miembroEditar): ?>
                            <input type="hidden" name="id" value="<?php echo $miembroEditar['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombres" class="form-label">Nombres *</label>
                                <input type="text" class="form-control" id="nombres" name="nombres" 
                                       value="<?php echo $miembroEditar ? htmlspecialchars($miembroEditar['nombres']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellidos" class="form-label">Apellidos *</label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                       value="<?php echo $miembroEditar ? htmlspecialchars($miembroEditar['apellidos']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $miembroEditar ? htmlspecialchars($miembroEditar['email']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo $miembroEditar ? htmlspecialchars($miembroEditar['telefono'] ?? '') : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?php echo $miembroEditar ? htmlspecialchars($miembroEditar['direccion'] ?? '') : ''; ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                       value="<?php echo $miembroEditar && $miembroEditar['fecha_nacimiento'] ? $miembroEditar['fecha_nacimiento'] : ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="genero" class="form-label">Género *</label>
                                <select class="form-select" id="genero" name="genero" required>
                                    <option value="">Seleccione...</option>
                                    <option value="M" <?php echo ($miembroEditar && $miembroEditar['genero'] == 'M') ? 'selected' : ''; ?>>Masculino</option>
                                    <option value="F" <?php echo ($miembroEditar && $miembroEditar['genero'] == 'F') ? 'selected' : ''; ?>>Femenino</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="estado_civil" class="form-label">Estado Civil</label>
                                <input type="text" class="form-control" id="estado_civil" name="estado_civil" 
                                       value="<?php echo $miembroEditar ? htmlspecialchars($miembroEditar['estado_civil'] ?? '') : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="profesion" class="form-label">Profesión</label>
                            <input type="text" class="form-control" id="profesion" name="profesion" 
                                   value="<?php echo $miembroEditar ? htmlspecialchars($miembroEditar['profesion'] ?? '') : ''; ?>">
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
            if (confirm('¿Está seguro de eliminar este miembro?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Función para limpiar y abrir el modal
        function limpiarYAbrirModalMiembro() {
            const modalMiembro = document.getElementById('modalMiembro');
            if (!modalMiembro) return;
            const form = modalMiembro.querySelector('form');
            if (!form) return;
            
            // Limpiar campos
            ['nombres', 'apellidos', 'email', 'telefono', 'direccion', 'fecha_nacimiento', 'estado_civil', 'profesion'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            
            // Limpiar hidden id
            const hiddenId = form.querySelector('input[name="id"]');
            if (hiddenId) hiddenId.remove();
            
            // Cambiar action
            const hiddenAction = form.querySelector('input[name="action"]');
            if (hiddenAction) hiddenAction.value = 'insert';
            
            // Cambiar título
            const modalTitle = modalMiembro.querySelector('.modal-title');
            if (modalTitle) modalTitle.textContent = 'Nuevo Miembro';
            
            // Cambiar botón
            const btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                const icon = btnSubmit.querySelector('i');
                if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                else btnSubmit.textContent = 'Guardar';
            }
            
            // Resetear selects
            const selectGenero = form.querySelector('#genero');
            if (selectGenero) selectGenero.value = '';
            
            form.reset();
            
            // Volver a limpiar
            ['nombres', 'apellidos', 'email', 'telefono', 'direccion', 'fecha_nacimiento', 'estado_civil', 'profesion'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            if (selectGenero) selectGenero.value = '';
            
            const modal = new bootstrap.Modal(modalMiembro);
            modal.show();
        }
        
        const btnNuevoMiembro = document.getElementById('btnNuevoMiembro');
        if (btnNuevoMiembro) {
            btnNuevoMiembro.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.location.search) window.history.replaceState({}, '', 'miembros.php');
                limpiarYAbrirModalMiembro();
            });
        }
        
        const modalMiembro = document.getElementById('modalMiembro');
        if (modalMiembro) {
            modalMiembro.addEventListener('hidden.bs.modal', function() {
                const form = modalMiembro.querySelector('form');
                if (form) {
                    form.reset();
                    const hiddenId = form.querySelector('input[name="id"]');
                    if (hiddenId) hiddenId.remove();
                    const hiddenAction = form.querySelector('input[name="action"]');
                    if (hiddenAction) hiddenAction.value = 'insert';
                    const modalTitle = modalMiembro.querySelector('.modal-title');
                    if (modalTitle) modalTitle.textContent = 'Nuevo Miembro';
                    const btnSubmit = form.querySelector('button[type="submit"]');
                    if (btnSubmit) {
                        const icon = btnSubmit.querySelector('i');
                        if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                        else btnSubmit.textContent = 'Guardar';
                    }
                }
            });
        }
        
        <?php if ($miembroEditar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalMiembro'));
            modal.show();
        });
        <?php endif; ?>
        
        // Funcionalidad de compartir formulario
        const btnCompartirFormulario = document.getElementById('btnCompartirFormulario');
        if (btnCompartirFormulario) {
            btnCompartirFormulario.addEventListener('click', function() {
                fetch('ajax_generar_url_miembro.php', {
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
                                            <p class="mb-3">Comparte este enlace con el miembro. El formulario solo puede usarse una vez.</p>
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
        
        // Función para abrir modal con solicitud de miembro
        window.abrirModalConSolicitudMiembro = function(solicitudId) {
            fetch(`ajax_notificaciones_miembros.php?accion=obtener_por_id&id=${solicitudId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exito && data.solicitud) {
                        const solicitud = data.solicitud;
                        const form = document.querySelector('#modalMiembro form');
                        if (form) {
                            if (form.querySelector('#nombres')) form.querySelector('#nombres').value = solicitud.nombres || '';
                            if (form.querySelector('#apellidos')) form.querySelector('#apellidos').value = solicitud.apellidos || '';
                            if (form.querySelector('#fecha_nacimiento')) form.querySelector('#fecha_nacimiento').value = solicitud.fecha_nacimiento || '';
                            if (form.querySelector('#genero')) form.querySelector('#genero').value = solicitud.genero || '';
                            if (form.querySelector('#direccion')) form.querySelector('#direccion').value = solicitud.direccion || '';
                            if (form.querySelector('#telefono')) form.querySelector('#telefono').value = solicitud.telefono || '';
                            if (form.querySelector('#email')) form.querySelector('#email').value = solicitud.email || '';
                            if (form.querySelector('#estado_civil')) form.querySelector('#estado_civil').value = solicitud.estado_civil || '';
                            if (form.querySelector('#profesion')) form.querySelector('#profesion').value = solicitud.profesion || '';
                            
                            // Marcar como procesado al guardar
                            const originalSubmit = form.onsubmit;
                            form.onsubmit = function(e) {
                                if (originalSubmit) originalSubmit.call(this, e);
                                fetch(`ajax_notificaciones_miembros.php?accion=marcar_procesado&id=${solicitudId}`)
                                    .catch(err => console.error('Error al marcar como procesado:', err));
                            };
                        }
                        
                        const modal = new bootstrap.Modal(document.getElementById('modalMiembro'));
                        modal.show();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar la solicitud');
                });
        };
        
        // Si hay notificación en la URL, cargarla
        <?php if (isset($_GET['notificacion']) && is_numeric($_GET['notificacion'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            window.abrirModalConSolicitudMiembro(<?php echo (int)$_GET['notificacion']; ?>);
        });
        <?php endif; ?>
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>

