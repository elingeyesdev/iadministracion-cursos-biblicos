<?php
require_once '../NEGOCIO/nCelula_new.php';
require_once '../NEGOCIO/nMiembro.php';
require_once '../NEGOCIO/auth.php';
require_once '../DATOS/dCelula_new.php';
require_once 'auth.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

$auth = new AuthController();
$auth->requireLogin();

// Verificar privilegios
if (!auth::tienePrivilegio('celulas_ver')) {
    header('Location: dashboard.php');
    exit();
}

$nCelula = new nCelula();
$nMiembro = new nMiembro();
$message = '';
$messageType = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'insert':
                    if (!auth::tienePrivilegio('celulas_crear')) {
                        header('Location: celulas.php');
                        exit();
                    }
                    $miembros_ids = isset($_POST['miembros_ids']) && is_array($_POST['miembros_ids']) 
                        ? array_map('intval', $_POST['miembros_ids']) 
                        : [];
                    $resultado = $nCelula->insertar(
                        $_POST['nombre'],
                        $_POST['direccion'],
                        $_POST['dia_reunion'],
                        $_POST['hora_reunion'],
                        $_POST['id_lider'] ?? null,
                        $_POST['descripcion'] ?? '',
                        $miembros_ids
                    );
                    if ($resultado['exito']) {
                        $message = $resultado['mensaje'];
                        $messageType = 'success';
                    } else {
                        $message = $resultado['mensaje'];
                        $messageType = 'danger';
                    }
                    break;
                    
                case 'update':
                    if (!auth::tienePrivilegio('celulas_editar')) {
                        header('Location: celulas.php');
                        exit();
                    }
                    $miembros_ids = isset($_POST['miembros_ids']) && is_array($_POST['miembros_ids']) 
                        ? array_map('intval', $_POST['miembros_ids']) 
                        : [];
                    $resultado = $nCelula->actualizar(
                        $_POST['id'],
                        $_POST['nombre'],
                        $_POST['direccion'],
                        $_POST['dia_reunion'],
                        $_POST['hora_reunion'],
                        $_POST['estado'],
                        $_POST['id_lider'] ?? null,
                        $_POST['descripcion'] ?? '',
                        $miembros_ids
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
                    if (!auth::tienePrivilegio('celulas_eliminar')) {
                        header('Location: celulas.php');
                        exit();
                    }
                    $resultado = $nCelula->eliminar($_POST['id']);
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
        $celula = $nCelula->obtenerPorId($terminoBusqueda);
        $celulas = $celula ? [$celula] : [];
    } else {
        $celulas = $nCelula->buscarPorNombre($terminoBusqueda);
    }
} else {
$celulas = $nCelula->listar();
}
$miembros = $nMiembro->listar();

// Obtener datos para editar si se solicita
$celulaEditar = null;
$miembrosCelula = [];
if (isset($_GET['action']) && $_GET['action'] == 'editar' && isset($_GET['id'])) {
    $celulaEditar = $nCelula->obtenerPorId($_GET['id']);
    if ($celulaEditar) {
        $dCelula = new dCelula();
        $miembrosCelula = $dCelula->listarMiembros($celulaEditar['id']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Células - Sistema de Administración de Iglesia</title>
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
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuCelulas" aria-labelledby="offcanvasMenuCelulasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuCelulasLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('celulas'); ?>
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
                        <?php echo generarMenu('celulas'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuCelulas" aria-controls="offcanvasMenuCelulas">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-home text-primary"></i> Gestión de Células
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
                                <h5 class="mb-0">Lista de Células</h5>
                                <?php if (tieneAcceso('celulas_crear')): ?>
                                <a href="celulas.php" class="btn btn-primary" id="btnNuevaCelula">
                                    <i class="fas fa-plus"></i> Nueva Célula
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
                                        <a href="celulas.php" class="btn btn-outline-danger">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Cells Table -->
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
                                                    <th>Líder</th>
                                                    <th>Dirección</th>
                                                    <th>Día</th>
                                                    <th>Hora</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($celulas as $celula): ?>
                                                    <tr>
                                                        <td><?php echo $celula['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($celula['nombre']); ?></td>
                                                        <td><?php echo htmlspecialchars($celula['nombre_lider']); ?></td>
                                                        <td><?php echo htmlspecialchars($celula['direccion']); ?></td>
                                                        <td><?php echo htmlspecialchars($celula['dia_reunion']); ?></td>
                                                        <td><?php echo date('H:i', strtotime($celula['hora_reunion'])); ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <?php if (tieneAcceso('celulas_editar')): ?>
                                                                <a href="celulas.php?action=editar&id=<?php echo $celula['id']; ?>" 
                                                                   class="btn btn-warning" 
                                                                   title="Editar">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                                <?php endif; ?>
                                                                <?php if (tieneAcceso('celulas_eliminar')): ?>
                                                                <button type="button" 
                                                                        class="btn btn-danger" 
                                                                        onclick="confirmarEliminar(<?php echo $celula['id']; ?>)"
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

    <!-- Cell Modal -->
    <div class="modal fade" id="modalCelula" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php echo $celulaEditar ? 'Editar Célula' : 'Nueva Célula'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $celulaEditar ? 'update' : 'insert'; ?>">
                        <?php if ($celulaEditar): ?>
                            <input type="hidden" name="id" value="<?php echo $celulaEditar['id'] ?? ''; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo $celulaEditar ? htmlspecialchars($celulaEditar['nombre'] ?? '') : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="buscar_lider" class="form-label">Líder</label>
                                <input type="hidden" id="id_lider" name="id_lider" value="<?php echo ($celulaEditar && isset($celulaEditar['id_lider'])) ? $celulaEditar['id_lider'] : ''; ?>">
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control" 
                                           id="buscar_lider" 
                                           placeholder="Buscar miembro líder..." 
                                           autocomplete="off"
                                           value="<?php 
                                           if ($celulaEditar && isset($celulaEditar['id_lider'])) {
                                               $liderNombre = trim(($celulaEditar['nombre_lider'] ?? '') . ' ' . ($celulaEditar['apellido_lider'] ?? ''));
                                               echo htmlspecialchars($liderNombre);
                                           }
                                           ?>">
                                    <div id="resultados_busqueda_lider" class="list-group position-absolute w-100" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto; background: white; border: 1px solid #ddd; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); margin-top: 2px;"></div>
                                </div>
                                <small class="text-muted">Escriba al menos 2 caracteres para buscar. Seleccione un miembro para asignarlo como líder.</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="direccion" class="form-label">Dirección *</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" 
                                       value="<?php echo $celulaEditar ? htmlspecialchars($celulaEditar['direccion'] ?? '') : ''; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo $celulaEditar ? htmlspecialchars($celulaEditar['telefono'] ?? '') : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="dia_reunion" class="form-label">Día de Reunión *</label>
                                <select class="form-select" id="dia_reunion" name="dia_reunion" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Lunes" <?php echo $celulaEditar && isset($celulaEditar['dia_reunion']) && $celulaEditar['dia_reunion'] == 'Lunes' ? 'selected' : ''; ?>>Lunes</option>
                                    <option value="Martes" <?php echo $celulaEditar && isset($celulaEditar['dia_reunion']) && $celulaEditar['dia_reunion'] == 'Martes' ? 'selected' : ''; ?>>Martes</option>
                                    <option value="Miércoles" <?php echo $celulaEditar && isset($celulaEditar['dia_reunion']) && $celulaEditar['dia_reunion'] == 'Miércoles' ? 'selected' : ''; ?>>Miércoles</option>
                                    <option value="Jueves" <?php echo $celulaEditar && isset($celulaEditar['dia_reunion']) && $celulaEditar['dia_reunion'] == 'Jueves' ? 'selected' : ''; ?>>Jueves</option>
                                    <option value="Viernes" <?php echo $celulaEditar && isset($celulaEditar['dia_reunion']) && $celulaEditar['dia_reunion'] == 'Viernes' ? 'selected' : ''; ?>>Viernes</option>
                                    <option value="Sábado" <?php echo $celulaEditar && isset($celulaEditar['dia_reunion']) && $celulaEditar['dia_reunion'] == 'Sábado' ? 'selected' : ''; ?>>Sábado</option>
                                    <option value="Domingo" <?php echo $celulaEditar && isset($celulaEditar['dia_reunion']) && $celulaEditar['dia_reunion'] == 'Domingo' ? 'selected' : ''; ?>>Domingo</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="hora_reunion" class="form-label">Hora de Reunión *</label>
                                <input type="time" class="form-control" id="hora_reunion" name="hora_reunion" 
                                       value="<?php echo $celulaEditar ? ($celulaEditar['hora_reunion'] ?? '') : ''; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="activa" <?php echo $celulaEditar && isset($celulaEditar['estado']) && $celulaEditar['estado'] == 'activa' ? 'selected' : ''; ?>>Activa</option>
                                    <option value="inactiva" <?php echo $celulaEditar && isset($celulaEditar['estado']) && $celulaEditar['estado'] == 'inactiva' ? 'selected' : ''; ?>>Inactiva</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo $celulaEditar ? htmlspecialchars($celulaEditar['descripcion'] ?? '') : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="buscar_miembros" class="form-label">Miembros de la Célula</label>
                                <div class="position-relative">
                                    <input type="text" 
                                           class="form-control" 
                                           id="buscar_miembros" 
                                           placeholder="Buscar miembros para agregar..." 
                                           autocomplete="off">
                                    <div id="resultados_busqueda_miembros" class="list-group position-absolute w-100" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto; background: white; border: 1px solid #ddd; border-radius: 0.375rem; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); margin-top: 2px;"></div>
                                </div>
                                <small class="text-muted">Escriba al menos 2 caracteres para buscar. Seleccione miembros para agregarlos a la célula.</small>
                                <div id="miembros_seleccionados" class="mt-3">
                                    <?php if (!empty($miembrosCelula)): ?>
                                        <?php foreach ($miembrosCelula as $miembro): ?>
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo $celulaEditar ? 'Actualizar' : 'Guardar'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminar(id) {
            if (confirm('¿Está seguro de que desea eliminar esta célula?')) {
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
        function limpiarYAbrirModalCelula() {
            const modalCelula = document.getElementById('modalCelula');
            if (!modalCelula) return;
            
            const form = modalCelula.querySelector('form');
            if (!form) return;
            
            // Limpiar campos manualmente
            const nombreInput = form.querySelector('#nombre');
            if (nombreInput) nombreInput.value = '';
            
            const direccionInput = form.querySelector('#direccion');
            if (direccionInput) direccionInput.value = '';
            
            const telefonoInput = form.querySelector('#telefono');
            if (telefonoInput) telefonoInput.value = '';
            
            const descripcionTextarea = form.querySelector('#descripcion');
            if (descripcionTextarea) descripcionTextarea.value = '';
            
            const horaReunionInput = form.querySelector('#hora_reunion');
            if (horaReunionInput) horaReunionInput.value = '';
            
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
            const modalTitle = modalCelula.querySelector('.modal-title');
            if (modalTitle) {
                modalTitle.textContent = 'Nueva Célula';
            }
            
            // Cambiar el texto del botón
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
            const inputBuscarLider = form.querySelector('#buscar_lider');
            const hiddenLiderId = form.querySelector('#id_lider');
            const resultadosBusquedaLider = form.querySelector('#resultados_busqueda_lider');
            if (inputBuscarLider) inputBuscarLider.value = '';
            if (hiddenLiderId) hiddenLiderId.value = '';
            if (resultadosBusquedaLider) {
                resultadosBusquedaLider.style.display = 'none';
                resultadosBusquedaLider.innerHTML = '';
            }
            
            const selectDia = form.querySelector('#dia_reunion');
            if (selectDia && selectDia.options.length > 0) {
                Array.from(selectDia.options).forEach(option => option.selected = false);
                selectDia.options[0].selected = true;
            }
            
            const selectEstado = form.querySelector('#estado');
            if (selectEstado) {
                selectEstado.value = 'activa';
            }
            
            // Reset del formulario
            form.reset();
            
            // Volver a limpiar después del reset
            if (nombreInput) nombreInput.value = '';
            if (direccionInput) direccionInput.value = '';
            if (telefonoInput) telefonoInput.value = '';
            if (descripcionTextarea) descripcionTextarea.value = '';
            if (horaReunionInput) horaReunionInput.value = '';
            if (inputBuscarLider) inputBuscarLider.value = '';
            if (hiddenLiderId) hiddenLiderId.value = '';
            if (resultadosBusquedaLider) {
                resultadosBusquedaLider.style.display = 'none';
                resultadosBusquedaLider.innerHTML = '';
            }
            if (selectDia && selectDia.options.length > 0) {
                Array.from(selectDia.options).forEach(option => option.selected = false);
                selectDia.options[0].selected = true;
            }
            if (selectEstado) selectEstado.value = 'activa';
            
            // Limpiar miembros seleccionados
            if (typeof limpiarMiembros === 'function') {
                limpiarMiembros();
            }
            
            // Abrir el modal
            const modal = new bootstrap.Modal(modalCelula);
            modal.show();
        }
        
        // Manejar el botón "Nueva Célula"
        const btnNuevaCelula = document.getElementById('btnNuevaCelula');
        if (btnNuevaCelula) {
            btnNuevaCelula.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.location.search) {
                    window.history.replaceState({}, '', 'celulas.php');
                }
                limpiarYAbrirModalCelula();
            });
        }
        
        // Limpiar formulario cuando se cierra el modal
        const modalCelula = document.getElementById('modalCelula');
        if (modalCelula) {
            modalCelula.addEventListener('hidden.bs.modal', function() {
                const form = modalCelula.querySelector('form');
                if (form) {
                    form.reset();
                    const hiddenId = form.querySelector('input[name="id"]');
                    if (hiddenId) hiddenId.remove();
                    const hiddenAction = form.querySelector('input[name="action"]');
                    if (hiddenAction) hiddenAction.value = 'insert';
                    const modalTitle = modalCelula.querySelector('.modal-title');
                    if (modalTitle) modalTitle.textContent = 'Nueva Célula';
                    const btnSubmit = form.querySelector('button[type="submit"]');
                    if (btnSubmit) {
                        const icon = btnSubmit.querySelector('i');
                        if (icon) {
                            btnSubmit.innerHTML = '<i class="fas fa-save"></i> Guardar';
                        } else {
                            btnSubmit.textContent = 'Guardar';
                        }
                    }
                }
            });
        }
        
        // Búsqueda de líder en tiempo real
        let timeoutBusquedaLider = null;
        const inputBuscarLider = document.getElementById('buscar_lider');
        const resultadosBusquedaLider = document.getElementById('resultados_busqueda_lider');
        const hiddenLiderId = document.getElementById('id_lider');
        
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
        
        // Función para limpiar buscador de líder
        const limpiarBuscadorLider = function() {
            if (inputBuscarLider) inputBuscarLider.value = '';
            if (hiddenLiderId) hiddenLiderId.value = '';
            if (resultadosBusquedaLider) {
                resultadosBusquedaLider.style.display = 'none';
                resultadosBusquedaLider.innerHTML = '';
            }
        };
        
        // Búsqueda de miembros en tiempo real
        let timeoutBusquedaMiembros = null;
        const inputBuscarMiembros = document.getElementById('buscar_miembros');
        const resultadosBusquedaMiembros = document.getElementById('resultados_busqueda_miembros');
        const miembrosSeleccionadosDiv = document.getElementById('miembros_seleccionados');
        const miembrosSeleccionados = new Set();
        
        // Inicializar miembros ya seleccionados
        <?php if (!empty($miembrosCelula)): ?>
            <?php foreach ($miembrosCelula as $miembro): ?>
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
        <?php if ($celulaEditar): ?>
            const modal = new bootstrap.Modal(document.getElementById('modalCelula'));
            modal.show();
        <?php endif; ?>
    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>