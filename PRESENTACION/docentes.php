<?php
require_once '../NEGOCIO/auth.php';
require_once 'auth.php';
require_once '../NEGOCIO/nDocente.php';
require_once '../NEGOCIO/nMiembro.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

$auth = new AuthController();
$auth->requireLogin();

// Verificar privilegios
if (!auth::tienePrivilegio('docentes_ver')) {
    header('Location: dashboard.php');
    exit();
}

$nDocente = new nDocente();
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
                    if (!auth::tienePrivilegio('docentes_crear')) {
                        header('Location: docentes.php');
                        exit();
                    }
                    $resultado = $nDocente->insertar(
                        $_POST['nombres'],
                        $_POST['apellidos'],
                        $_POST['especialidad'] ?? '',
                        $_POST['telefono'] ?? '',
                        $_POST['email'] ?? '',
                        $_POST['direccion'] ?? '',
                        !empty($_POST['miembro_id']) ? $_POST['miembro_id'] : null,
                        isset($_POST['estado']) ? 1 : 0
                    );
                    if ($resultado['exito']) {
                        // Si hay una solicitud asociada, marcarla como procesada
                        if (!empty($_POST['solicitud_id'])) {
                            require_once '../DATOS/dSolicitudDocente.php';
                            $dSolicitud = new dSolicitudDocente();
                            $solicitud = $dSolicitud->obtenerPorId((int)$_POST['solicitud_id']);
                            if ($solicitud) {
                                $dSolicitud->marcarComoProcesado((int)$_POST['solicitud_id']);
                            }
                        }
                        
                        // Redirigir para cerrar el modal y evitar reenvío del formulario
                        header('Location: docentes.php?mensaje=' . urlencode($resultado['mensaje']) . '&tipo=success');
                        exit();
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'update':
                    if (!auth::tienePrivilegio('docentes_editar')) {
                        header('Location: docentes.php');
                        exit();
                    }
                    $resultado = $nDocente->actualizar(
                        $_POST['id'],
                        $_POST['nombres'],
                        $_POST['apellidos'],
                        $_POST['especialidad'] ?? '',
                        $_POST['telefono'] ?? '',
                        $_POST['email'] ?? '',
                        $_POST['direccion'] ?? '',
                        !empty($_POST['miembro_id']) ? $_POST['miembro_id'] : null,
                        isset($_POST['estado']) ? 1 : 0
                    );
                    if ($resultado['exito']) {
                        $message = $resultado['mensaje'];
                        $messageType = 'success';
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'delete':
                    if (!auth::tienePrivilegio('docentes_eliminar')) {
                        header('Location: docentes.php');
                        exit();
                    }
                    $resultado = $nDocente->eliminar($_POST['id']);
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
        $docente = $nDocente->obtenerPorId($terminoBusqueda);
        $docentes = $docente ? [$docente] : [];
    } else {
        $docentes = $nDocente->buscarPorNombre($terminoBusqueda);
    }
} else {
    $docentes = $nDocente->listar();
}

// Obtener datos para editar si se solicita
$docenteEditar = null;
if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $docenteEditar = $nDocente->obtenerPorId($_GET['id']);
}

// Obtener lista de miembros para el select
$miembros = $nMiembro->listar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docentes - Sistema de Administración de Iglesia</title>
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
        
        /* Estilos para notificaciones toast */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast-notification {
            min-width: 300px;
            max-width: 400px;
            margin-bottom: 10px;
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Botón flotante de notificaciones */
        .btn-notificaciones-flotante {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: transform 0.2s;
        }
        
        .btn-notificaciones-flotante:hover {
            transform: scale(1.1);
        }
        
        .btn-notificaciones-flotante .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
            padding: 4px 6px;
        }
    </style>
    <?php echo generarEstilosNotificaciones(); ?>
    </style>
</head>
<body>
    <!-- Offcanvas Menu (móviles) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuDocentes" aria-labelledby="offcanvasMenuDocentesLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuDocentesLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('docentes'); ?>
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
                        <?php echo generarMenu('docentes'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuDocentes" aria-controls="offcanvasMenuDocentes">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-chalkboard-teacher text-primary"></i> Docentes
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
                                <h5 class="mb-0">Lista de Docentes</h5>
                                <div class="btn-group">
                                    <?php if (tieneAcceso('docentes_crear')): ?>
                                    <button type="button" class="btn btn-success" id="btnCompartirFormulario" title="Compartir formulario de registro">
                                        <i class="fas fa-share-alt"></i> Compartir Formulario
                                    </button>
                                    <a href="docentes.php" class="btn btn-primary" id="btnNuevoDocente">
                                        <i class="fas fa-plus"></i> Nuevo Docente
                                    </a>
                                    <?php endif; ?>
                                </div>
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
                                        <a href="docentes.php" class="btn btn-outline-danger">
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
                                            <th>Especialidad</th>
                                            <th>Teléfono</th>
                                            <th>Email</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($docentes as $docente): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($docente['id'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($docente['nombres'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($docente['apellidos'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($docente['especialidad'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($docente['telefono'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($docente['email'] ?? ''); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if (tieneAcceso('docentes_editar')): ?>
                                                        <a href="docentes.php?action=editar&id=<?php echo $docente['id']; ?>" 
                                                           class="btn btn-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                        <?php if (tieneAcceso('docentes_eliminar')): ?>
                                                        <button type="button" class="btn btn-danger" 
                                                                onclick="confirmarEliminar(<?php echo $docente['id']; ?>)" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($docentes)): ?>
                                            <tr><td colspan="8" class="text-center text-muted">Sin registros</td></tr>
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

    <!-- Modal Docente -->
    <div class="modal fade" id="modalDocente" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $docenteEditar ? 'Editar Docente' : 'Nuevo Docente'; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $docenteEditar ? 'update' : 'insert'; ?>">
                        <?php if ($docenteEditar): ?>
                            <input type="hidden" name="id" value="<?php echo $docenteEditar['id']; ?>">
                        <?php endif; ?>
                        <input type="hidden" name="solicitud_id" id="solicitud_id" value="">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombres" class="form-label">Nombres *</label>
                                <input type="text" class="form-control" id="nombres" name="nombres" 
                                       value="<?php echo $docenteEditar ? htmlspecialchars($docenteEditar['nombres'] ?? '') : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellidos" class="form-label">Apellidos *</label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" 
                                       value="<?php echo $docenteEditar ? htmlspecialchars($docenteEditar['apellidos'] ?? '') : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $docenteEditar ? htmlspecialchars($docenteEditar['email'] ?? '') : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo $docenteEditar ? htmlspecialchars($docenteEditar['telefono'] ?? '') : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="especialidad" class="form-label">Especialidad</label>
                            <input type="text" class="form-control" id="especialidad" name="especialidad" 
                                   value="<?php echo $docenteEditar ? htmlspecialchars($docenteEditar['especialidad'] ?? '') : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion" name="direccion" 
                                   value="<?php echo $docenteEditar ? htmlspecialchars($docenteEditar['direccion'] ?? '') : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="miembro_id" class="form-label">Miembro (Opcional)</label>
                            <select class="form-select" id="miembro_id" name="miembro_id">
                                <option value="">Seleccione un miembro...</option>
                                <?php foreach ($miembros as $miembro): ?>
                                    <option value="<?php echo $miembro['id']; ?>" 
                                            <?php echo ($docenteEditar && ($docenteEditar['miembro_id'] ?? null) == $miembro['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($miembro['nombres'] . ' ' . $miembro['apellidos']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="estado" name="estado" 
                                       <?php echo ($docenteEditar && ($docenteEditar['estado'] ?? 0)) ? 'checked' : 'checked'; ?>>
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
    
    <?php echo generarNotificacionesDocentes(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de eliminar este docente?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Función para limpiar y abrir el modal
        function limpiarYAbrirModalDocente() {
            const modalDocente = document.getElementById('modalDocente');
            if (!modalDocente) return;
            const form = modalDocente.querySelector('form');
            if (!form) return;
            
            ['nombres', 'apellidos', 'email', 'telefono', 'especialidad', 'direccion'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            
            const hiddenId = form.querySelector('input[name="id"]');
            if (hiddenId) hiddenId.remove();
            const hiddenAction = form.querySelector('input[name="action"]');
            if (hiddenAction) hiddenAction.value = 'insert';
            const modalTitle = modalDocente.querySelector('.modal-title');
            if (modalTitle) modalTitle.textContent = 'Nuevo Docente';
            const btnSubmit = form.querySelector('button[type="submit"]');
            if (btnSubmit) {
                const icon = btnSubmit.querySelector('i');
                if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                else btnSubmit.textContent = 'Guardar';
            }
            const selectMiembro = form.querySelector('#miembro_id');
            if (selectMiembro && selectMiembro.options.length > 0) {
                Array.from(selectMiembro.options).forEach(option => option.selected = false);
                selectMiembro.options[0].selected = true;
            }
            const checkboxEstado = form.querySelector('#estado');
            if (checkboxEstado) checkboxEstado.checked = true;
            
            form.reset();
            
            ['nombres', 'apellidos', 'email', 'telefono', 'especialidad', 'direccion'].forEach(id => {
                const input = form.querySelector('#' + id);
                if (input) input.value = '';
            });
            if (selectMiembro && selectMiembro.options.length > 0) {
                Array.from(selectMiembro.options).forEach(option => option.selected = false);
                selectMiembro.options[0].selected = true;
            }
            if (checkboxEstado) checkboxEstado.checked = true;
            
            const modal = new bootstrap.Modal(modalDocente);
            modal.show();
        }
        
        const btnNuevoDocente = document.getElementById('btnNuevoDocente');
        if (btnNuevoDocente) {
            btnNuevoDocente.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.location.search) window.history.replaceState({}, '', 'docentes.php');
                limpiarYAbrirModalDocente();
            });
        }
        
        const modalDocente = document.getElementById('modalDocente');
        if (modalDocente) {
            modalDocente.addEventListener('hidden.bs.modal', function() {
                const form = modalDocente.querySelector('form');
                if (form) {
                    form.reset();
                    const hiddenId = form.querySelector('input[name="id"]');
                    if (hiddenId) hiddenId.remove();
                    const hiddenAction = form.querySelector('input[name="action"]');
                    if (hiddenAction) hiddenAction.value = 'insert';
                    const modalTitle = modalDocente.querySelector('.modal-title');
                    if (modalTitle) modalTitle.textContent = 'Nuevo Docente';
                    const btnSubmit = form.querySelector('button[type="submit"]');
                    if (btnSubmit) {
                        const icon = btnSubmit.querySelector('i');
                        if (icon) btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                        else btnSubmit.textContent = 'Guardar';
                    }
                }
            });
        }
        
        <?php if ($docenteEditar): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('modalDocente'));
            modal.show();
        });
        <?php endif; ?>
        
        // Funcionalidad de compartir formulario
        const btnCompartirFormulario = document.getElementById('btnCompartirFormulario');
        if (btnCompartirFormulario) {
            btnCompartirFormulario.addEventListener('click', function() {
                fetch('ajax_generar_url_docente.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exito) {
                        // Crear modal para mostrar la URL
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
                                            <p class="mb-3">Comparte este enlace con el docente. El formulario solo puede usarse una vez.</p>
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
                        
                        // Remover modal anterior si existe
                        const modalAnterior = document.getElementById('modalCompartir');
                        if (modalAnterior) {
                            modalAnterior.remove();
                        }
                        
                        // Agregar nuevo modal al body
                        document.body.insertAdjacentHTML('beforeend', modalHTML);
                        
                        // Mostrar modal
                        const modal = new bootstrap.Modal(document.getElementById('modalCompartir'));
                        modal.show();
                        
                        // Agregar evento al botón copiar
                        const btnCopiar = document.getElementById('btnCopiarUrl');
                        if (btnCopiar) {
                            btnCopiar.addEventListener('click', function() {
                                copiarAlPortapapeles('urlFormulario');
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
        
        // Función para copiar al portapapeles
        function copiarAlPortapapeles(id) {
            const input = document.getElementById(id);
            if (!input) return;
            
            input.select();
            input.setSelectionRange(0, 99999); // Para móviles
            
            try {
                document.execCommand('copy');
                
                // Mostrar feedback
                const btn = document.getElementById('btnCopiarUrl');
                if (btn) {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check"></i> Copiado';
                    btn.classList.add('btn-success');
                    btn.classList.remove('btn-outline-secondary');
                    
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-secondary');
                    }, 2000);
                }
            } catch (err) {
                console.error('Error al copiar:', err);
            }
        }
        
        // Función para abrir modal con solicitud (disponible globalmente)
        window.abrirModalConSolicitudDocente = function(id) {
            fetch(`ajax_notificaciones_docentes.php?accion=obtener_por_id&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exito && data.solicitud) {
                        const solicitud = data.solicitud;
                        
                        // Prellenar el modal con los datos de la solicitud
                        const form = document.querySelector('#modalDocente form');
                        if (form) {
                            // Limpiar formulario primero
                            limpiarYAbrirModalDocente();
                            
                            // Llenar con datos de la solicitud
                            const nombresInput = form.querySelector('#nombres');
                            const apellidosInput = form.querySelector('#apellidos');
                            const emailInput = form.querySelector('#email');
                            const telefonoInput = form.querySelector('#telefono');
                            const especialidadInput = form.querySelector('#especialidad');
                            const direccionInput = form.querySelector('#direccion');
                            
                            if (nombresInput) nombresInput.value = solicitud.nombres || '';
                            if (apellidosInput) apellidosInput.value = solicitud.apellidos || '';
                            if (emailInput) emailInput.value = solicitud.email || '';
                            if (telefonoInput) telefonoInput.value = solicitud.telefono || '';
                            if (especialidadInput) especialidadInput.value = solicitud.especialidad || '';
                            if (direccionInput) direccionInput.value = solicitud.direccion || '';
                            
                            // Cambiar título del modal
                            const modalTitle = document.querySelector('#modalDocente .modal-title');
                            if (modalTitle) modalTitle.textContent = 'Aprobar Solicitud de Docente';
                            
                            // Guardar el ID de la solicitud en el campo hidden
                            let solicitudIdInput = form.querySelector('#solicitud_id');
                            if (!solicitudIdInput) {
                                // Si no existe, crearlo
                                solicitudIdInput = document.createElement('input');
                                solicitudIdInput.type = 'hidden';
                                solicitudIdInput.name = 'solicitud_id';
                                solicitudIdInput.id = 'solicitud_id';
                                form.appendChild(solicitudIdInput);
                            }
                            solicitudIdInput.value = id;
                            
                            // Asegurar que el action sea 'insert'
                            const actionInput = form.querySelector('input[name="action"]');
                            if (actionInput) {
                                actionInput.value = 'insert';
                            }
                            
                            // Cerrar modal de notificaciones
                            const modalNotificaciones = bootstrap.Modal.getInstance(document.getElementById('modalNotificaciones'));
                            if (modalNotificaciones) {
                                modalNotificaciones.hide();
                            }
                            
                            // Abrir modal de docente
                            const modal = new bootstrap.Modal(document.getElementById('modalDocente'));
                            modal.show();
                            
                            // Agregar listener para cuando se cierre el modal después de un submit exitoso
                            const modalElement = document.getElementById('modalDocente');
                            const handleModalHidden = function() {
                                // Recargar notificaciones después de cerrar el modal
                                // Las funciones están en el helper, se recargarán automáticamente
                                modalElement.removeEventListener('hidden.bs.modal', handleModalHidden);
                            };
                            modalElement.addEventListener('hidden.bs.modal', handleModalHidden);
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
        
        // Si hay notificación en la URL, abrir el modal automáticamente
        <?php if (isset($_GET['notificacion']) && is_numeric($_GET['notificacion'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const notificacionId = <?php echo (int)$_GET['notificacion']; ?>;
            // Esperar un poco para que todo esté cargado
            setTimeout(function() {
                if (window.abrirModalConSolicitudDocente) {
                    window.abrirModalConSolicitudDocente(notificacionId);
                    // Limpiar la URL
                    if (window.history && window.history.replaceState) {
                        window.history.replaceState({}, '', 'docentes.php');
                    }
                }
            }, 500);
        });
        <?php endif; ?>
    </script>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>