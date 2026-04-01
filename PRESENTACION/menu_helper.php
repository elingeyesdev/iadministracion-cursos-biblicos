<?php

require_once '../NEGOCIO/auth.php';

function tieneAcceso($privilegio) {
    return auth::tienePrivilegio($privilegio);
}

/**
 * Genera un subtítulo para el menú
 */
function generarSubtituloMenu($titulo) {
    return '<div class="px-3 py-2 mt-3 mb-2"><small class="text-white-50 fw-bold text-uppercase">' . htmlspecialchars($titulo) . '</small></div>';
}

/**
 * Genera el menú lateral completo con verificación de privilegios
 */
function generarMenu($pagina_activa = 'dashboard') {
    $menu = '';
    $usuario = auth::obtenerUsuarioActual();
    $usuarioNombre = htmlspecialchars($usuario['username'] ?? 'Usuario');
    $usuarioRol = htmlspecialchars($usuario['rol'] ?? '');
    
    // Dashboard - siempre visible
    $active = ($pagina_activa == 'dashboard') ? 'active' : '';
    $menu .= '<a class="nav-link ' . $active . '" href="dashboard.php">';
    $menu .= '<i class="fas fa-tachometer-alt"></i> Dashboard';
    $menu .= '</a>';
    
    // ========== ACCESO Y SEGURIDAD ==========
    $tieneAccesoYSeguridad = tieneAcceso('usuarios_ver') || tieneAcceso('roles_ver') || tieneAcceso('usuarios_editar');
    if ($tieneAccesoYSeguridad) {
        $menu .= generarSubtituloMenu('Acceso y Seguridad');
        $active = (in_array($pagina_activa, ['usuarios', 'roles', 'usuarios_roles'])) ? 'active' : '';
        $menu .= '<div class="dropdown">';
        $menu .= '<a class="nav-link ' . $active . ' dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">';
        $menu .= '<i class="fas fa-shield-alt"></i> Acceso y Seguridad';
        $menu .= '</a>';
        $menu .= '<ul class="dropdown-menu w-100">';
        if (tieneAcceso('usuarios_ver')) {
            $menu .= '<li><a class="dropdown-item ' . ($pagina_activa == 'usuarios' ? 'active' : '') . '" href="usuarios.php">Usuarios</a></li>';
        }
        if (tieneAcceso('usuarios_editar')) {
            $menu .= '<li><a class="dropdown-item ' . ($pagina_activa == 'usuarios_roles' ? 'active' : '') . '" href="usuarios_roles.php">Asignar Roles</a></li>';
        }
        if (tieneAcceso('roles_ver')) {
            $menu .= '<li><a class="dropdown-item ' . ($pagina_activa == 'roles' ? 'active' : '') . '" href="roles.php">Roles</a></li>';
        }
        $menu .= '</ul>';
        $menu .= '</div>';
    }
    
    // ========== PARAMETRIZACIÓN ==========
    $tieneParametrizacion = false;
    $menuParametrizacion = '';
    
    // Miembros
    $tieneAccesoMiembros = tieneAcceso('miembros_ver') || tieneAcceso('ministerios_ver') || tieneAcceso('celulas_ver');
    if ($tieneAccesoMiembros) {
        $tieneParametrizacion = true;
        $active = (in_array($pagina_activa, ['miembros', 'ministerios', 'celulas'])) ? 'active' : '';
        $menuParametrizacion .= '<div class="dropdown">';
        $menuParametrizacion .= '<a class="nav-link ' . $active . ' dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">';
        $menuParametrizacion .= '<i class="fas fa-users"></i> Miembros';
        $menuParametrizacion .= '</a>';
        $menuParametrizacion .= '<ul class="dropdown-menu w-100">';
        if (tieneAcceso('miembros_ver')) {
            $menuParametrizacion .= '<li><a class="dropdown-item ' . ($pagina_activa == 'miembros' ? 'active' : '') . '" href="miembros.php">Gestionar Miembros</a></li>';
        }
        if (tieneAcceso('ministerios_ver')) {
            $menuParametrizacion .= '<li><a class="dropdown-item ' . ($pagina_activa == 'ministerios' ? 'active' : '') . '" href="ministerios.php">Ministerios</a></li>';
        }
        if (tieneAcceso('celulas_ver')) {
            $menuParametrizacion .= '<li><a class="dropdown-item ' . ($pagina_activa == 'celulas' ? 'active' : '') . '" href="celulas.php">Células</a></li>';
        }
        $menuParametrizacion .= '</ul>';
        $menuParametrizacion .= '</div>';
    }
    
    // Eventos
    if (tieneAcceso('eventos_ver')) {
        $tieneParametrizacion = true;
        $active = (in_array($pagina_activa, ['eventos', 'tipos_eventos'])) ? 'active' : '';
        $menuParametrizacion .= '<div class="dropdown">';
        $menuParametrizacion .= '<a class="nav-link ' . $active . ' dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">';
        $menuParametrizacion .= '<i class="fas fa-calendar-alt"></i> Eventos';
        $menuParametrizacion .= '</a>';
        $menuParametrizacion .= '<ul class="dropdown-menu w-100">';
        $menuParametrizacion .= '<li><a class="dropdown-item ' . ($pagina_activa == 'eventos' ? 'active' : '') . '" href="eventos.php">Gestionar Eventos</a></li>';
        if (tieneAcceso('eventos_ver')) {
            $menuParametrizacion .= '<li><a class="dropdown-item ' . ($pagina_activa == 'tipos_eventos' ? 'active' : '') . '" href="tipos_eventos.php">Tipos de Eventos</a></li>';
        }
        $menuParametrizacion .= '</ul>';
        $menuParametrizacion .= '</div>';
    }
    
    // Finanzas
    if (tieneAcceso('finanzas_ver')) {
        $tieneParametrizacion = true;
        $active = (in_array($pagina_activa, ['finanzas', 'tipos_transacciones'])) ? 'active' : '';
        $menuParametrizacion .= '<div class="dropdown">';
        $menuParametrizacion .= '<a class="nav-link ' . $active . ' dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">';
        $menuParametrizacion .= '<i class="fas fa-dollar-sign"></i> Finanzas';
        $menuParametrizacion .= '</a>';
        $menuParametrizacion .= '<ul class="dropdown-menu w-100">';
        $menuParametrizacion .= '<li><a class="dropdown-item ' . ($pagina_activa == 'finanzas' ? 'active' : '') . '" href="finanzas.php">Transacciones</a></li>';
        if (tieneAcceso('finanzas_ver')) {
            $menuParametrizacion .= '<li><a class="dropdown-item ' . ($pagina_activa == 'tipos_transacciones' ? 'active' : '') . '" href="tipos_transacciones.php">Tipos de Transacciones</a></li>';
        }
        $menuParametrizacion .= '</ul>';
        $menuParametrizacion .= '</div>';
    }
    
    // Escuelas (sin Asistencias)
    $tieneAccesoEscuelas = tieneAcceso('docentes_ver') || tieneAcceso('cursos_ver') || tieneAcceso('estudiantes_ver');
    if ($tieneAccesoEscuelas) {
        $tieneParametrizacion = true;
        $active = (in_array($pagina_activa, ['docentes', 'cursos', 'estudiantes'])) ? 'active' : '';
        $menuParametrizacion .= '<div class="dropdown">';
        $menuParametrizacion .= '<a class="nav-link ' . $active . ' dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">';
        $menuParametrizacion .= '<i class="fas fa-graduation-cap"></i> Escuelas';
        $menuParametrizacion .= '</a>';
        $menuParametrizacion .= '<ul class="dropdown-menu w-100">';
        if (tieneAcceso('docentes_ver')) {
            $menuParametrizacion .= '<li><a class="dropdown-item ' . ($pagina_activa == 'docentes' ? 'active' : '') . '" href="docentes.php">Docentes</a></li>';
        }
        if (tieneAcceso('cursos_ver')) {
            $menuParametrizacion .= '<li><a class="dropdown-item ' . ($pagina_activa == 'cursos' ? 'active' : '') . '" href="cursos.php">Cursos</a></li>';
        }
        if (tieneAcceso('estudiantes_ver')) {
            $menuParametrizacion .= '<li><a class="dropdown-item ' . ($pagina_activa == 'estudiantes' ? 'active' : '') . '" href="estudiantes.php">Estudiantes</a></li>';
        }
        $menuParametrizacion .= '</ul>';
        $menuParametrizacion .= '</div>';
    }
    
    // Mostrar sección Parametrización si tiene al menos un elemento
    if ($tieneParametrizacion) {
        $menu .= generarSubtituloMenu('Parametrización');
        $menu .= $menuParametrizacion;
    }
    
    // ========== TRANSACCIONAL ==========
    if (tieneAcceso('asistencias_ver')) {
        $menu .= generarSubtituloMenu('Transaccional');
        $active = ($pagina_activa == 'asistencias') ? 'active' : '';
        $menu .= '<a class="nav-link ' . $active . '" href="asistencias.php">';
        $menu .= '<i class="fas fa-clipboard-check"></i> Asistencias';
        $menu .= '</a>';
    }
    
    // ========== REPORTES ==========
    $tieneAccesoReportes = tieneAcceso('reportes_ver') || tieneAcceso('reportes_finanzas') || 
                           tieneAcceso('reportes_escuelas') || tieneAcceso('reportes_miembros');
    if ($tieneAccesoReportes) {
        $menu .= generarSubtituloMenu('Reportes');
        $active = ($pagina_activa == 'reportes') ? 'active' : '';
        $menu .= '<a class="nav-link ' . $active . '" href="reportes.php">';
        $menu .= '<i class="fas fa-chart-bar"></i> Reportes';
        $menu .= '</a>';
    }

    // Bloque de usuario anclado al final del menú (siempre visible)
    $menu .= '<div class="mt-3"></div>';
    $menu .= '<div class="dropdown mt-auto">';
    $menu .= '  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">';
    $menu .= '    <i class="fas fa-user-circle"></i> ';
    $menu .= '    <span class="d-inline-block">';
    $menu .=          $usuarioNombre;
    if (!empty($usuarioRol)) {
        $menu .= '      <br><small>' . $usuarioRol . '</small>';
    }
    $menu .= '    </span>';
    $menu .= '  </a>';
    $menu .= '  <ul class="dropdown-menu w-100">';
    $menu .= '    <li><a class="dropdown-item ' . ($pagina_activa == 'perfil' ? 'active' : '') . '" href="perfil.php"><i class="fas fa-id-badge"></i> Mi Perfil</a></li>';
    $menu .= '    <li><hr class="dropdown-divider"></li>';
    $menu .= '    <li>
                    <form method="POST" action="auth.php" class="px-3 m-0">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="dropdown-item text-danger w-100 text-start">
                            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                        </button>
                    </form>
                 </li>';
    $menu .= '  </ul>';
    $menu .= '</div>';
 
    return $menu;
}
?>

