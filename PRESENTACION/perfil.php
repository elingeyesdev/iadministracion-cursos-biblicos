<?php
require_once '../NEGOCIO/auth.php';
require_once '../DATOS/dUsuario.php';
require_once 'auth.php';
require_once 'menu_helper.php';
require_once 'notificaciones_helper.php';

$auth = new AuthController();
$auth->requireLogin();
$userSession = $auth->getCurrentUser();

// Obtener datos completos del usuario desde la base de datos
$dUsuario = new dUsuario();
$user = $dUsuario->buscarPorId($userSession['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Sistema de Administración de Iglesia</title>
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
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px 15px 0 0;
            padding: 3rem 2rem 2rem;
            position: relative;
            overflow: hidden;
        }
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        .profile-icon {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #667eea;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: 5px solid white;
            position: relative;
            z-index: 1;
        }
        .info-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid;
            height: 100%;
        }
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .info-card.user-info { border-left-color: #667eea; }
        .info-card.role-info { border-left-color: #f093fb; }
        .info-card.date-info { border-left-color: #4facfe; }
        .info-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }
        .info-card.user-info .info-card-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .info-card.role-info .info-card-icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .info-card.date-info .info-card-icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .info-card-title {
            font-size: 0.875rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .info-card-value {
            font-size: 1.25rem;
            color: #212529;
            font-weight: 600;
            margin-bottom: 0;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 2rem;
            color: white;
            text-align: center;
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
    </style>
    <?php echo generarEstilosNotificaciones(); ?>
</head>
<body>
    <!-- Offcanvas Menu (móviles) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenuPerfil" aria-labelledby="offcanvasMenuPerfilLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasMenuPerfilLabel">Menú</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column p-3">
                <?php echo generarMenu('perfil'); ?>
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
                        <?php echo generarMenu('perfil'); ?>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-12 col-lg-10 px-0 sidebar-container">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-outline-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenuPerfil" aria-controls="offcanvasMenuPerfil">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h4 class="mb-0">
                            <i class="fas fa-user text-primary"></i> Mi Perfil
                        </h4>
                        <div class="d-flex align-items-center">
                            <?php echo generarBotonNotificacionesNavbar(); ?>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="container-fluid py-4">
                    <div class="row">
                        <div class="col-lg-10 col-xl-9 mx-auto">
                            <!-- Profile Header Card -->
                            <div class="card mb-4">
                                <div class="profile-header text-center text-white">
                                    <div class="profile-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <h2 class="mb-2" style="position: relative; z-index: 1;">
                                        <?php echo htmlspecialchars($user['nombre_completo'] ?? 'Usuario'); ?>
                                    </h2>
                                    <p class="mb-0" style="position: relative; z-index: 1;">
                                        <span class="badge bg-light text-dark px-3 py-2" style="font-size: 1rem;">
                                            <i class="fas fa-shield-alt me-2"></i>
                                            <?php echo htmlspecialchars($user['rol_nombre'] ?? 'Usuario'); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <!-- Information Cards Grid -->
                            <div class="row g-4 mb-4">
                                <!-- Usuario Card -->
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-card user-info">
                                        <div class="info-card-icon">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <div class="info-card-title">Nombre de Usuario</div>
                                        <div class="info-card-value">
                                            <?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nombre Completo Card -->
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-card user-info">
                                        <div class="info-card-icon">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                        <div class="info-card-title">Nombre Completo</div>
                                        <div class="info-card-value">
                                            <?php echo htmlspecialchars($user['nombre_completo'] ?? 'N/A'); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Rol Card -->
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-card role-info">
                                        <div class="info-card-icon">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                        <div class="info-card-title">Rol del Sistema</div>
                                        <div class="info-card-value">
                                            <?php echo htmlspecialchars($user['rol_nombre'] ?? 'Usuario'); ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fecha de Creación Card -->
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-card date-info">
                                        <div class="info-card-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="info-card-title">Fecha de Creación</div>
                                        <div class="info-card-value">
                                            <?php echo isset($user['fecha_creacion']) ? date('d/m/Y', strtotime($user['fecha_creacion'])) : 'N/A'; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Miembro desde Card -->
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-card date-info">
                                        <div class="info-card-icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="info-card-title">Miembro desde</div>
                                        <div class="info-card-value">
                                            <?php 
                                            if (isset($user['fecha_creacion'])) {
                                                $fecha = new DateTime($user['fecha_creacion']);
                                                $ahora = new DateTime();
                                                $diferencia = $ahora->diff($fecha);
                                                echo $diferencia->y . ' años, ' . $diferencia->m . ' meses';
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Estado Card -->
                                <div class="col-md-6 col-lg-4">
                                    <div class="info-card role-info">
                                        <div class="info-card-icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="info-card-title">Estado de la Cuenta</div>
                                        <div class="info-card-value">
                                            <span class="badge bg-success">Activa</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="text-center mt-4">
                                <a href="dashboard.php" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js">    </script>
    <?php echo generarNotificacionesDocentes(); ?>
    <?php echo generarJavaScriptNotificaciones(); ?>
</body>
</html>
