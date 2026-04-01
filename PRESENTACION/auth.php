<?php

require_once '../NEGOCIO/auth.php';

class AuthController {
    
    public function requireLogin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Si hay un login pendiente (múltiples roles), redirigir al selector
        if (isset($_SESSION['login_pendiente']) && $_SESSION['login_pendiente']) {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Location: auth.php');
            exit();
        }
        
        if (!auth::estaLogueado()) {
            // Headers para prevenir cacheo
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Location: auth.php');
            exit();
        }
    }
    
    public function getCurrentUser() {
        return auth::obtenerUsuarioActual();
    }
    
    public function login($username, $password) {
        return auth::login($username, $password);
    }
    
    public function logout() {
        return auth::logout();
    }
    
    public function isLoggedIn() {
        return auth::estaLogueado();
    }
}

// Procesar selección de rol
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'seleccionar_rol') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $rol_id = isset($_POST['rol_id']) ? (int)$_POST['rol_id'] : 0;
    
    if (auth::completarLoginConRol($rol_id)) {
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Error al seleccionar el rol. Por favor, intente nuevamente.';
    }
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $auth = new AuthController();
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $resultado = $auth->login($username, $password);
    
    if ($resultado === true) {
        // Login exitoso con un solo rol o sin roles
        header('Location: dashboard.php');
        exit();
    } elseif (is_array($resultado) && isset($resultado['multiples_roles']) && $resultado['multiples_roles']) {
        // Usuario con múltiples roles - mostrar selector
        $roles = $resultado['roles'];
        $mostrarSelectorRoles = true;
    } else {
        // Login fallido
        $error = 'Usuario o contraseña incorrectos';
    }
}

// Procesar logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'logout') {
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Limpiar todas las variables de sesión
    $_SESSION = array();
    
    // Destruir la cookie de sesión si existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir la sesión
    session_destroy();
    
    // Headers para prevenir cacheo
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Redirigir siempre a auth.php
    header('Location: auth.php');
    exit();
}

// Solo mostrar el formulario de login si se accede directamente a este archivo
// y no si se está incluyendo desde otro archivo
if (basename($_SERVER['PHP_SELF']) === 'auth.php') {
    // Verificar si hay un login pendiente (múltiples roles)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['login_pendiente']) && $_SESSION['login_pendiente']) {
        $roles = $_SESSION['login_roles'] ?? [];
        $mostrarSelectorRoles = true;
    } else {
    // Si ya está logueado, redirigir al dashboard
    $auth = new AuthController();
    if ($auth->isLoggedIn()) {
        // Headers para prevenir cacheo
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Location: dashboard.php');
        exit();
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Iniciar Sesión - Sistema de Administración de Iglesia</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                padding: 2rem;
                max-width: 400px;
                width: 100%;
            }
            .login-header {
                text-align: center;
                margin-bottom: 2rem;
            }
            .login-header i {
                font-size: 3rem;
                color: #667eea;
                margin-bottom: 1rem;
            }
            .btn-login {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                width: 100%;
                padding: 12px;
                border-radius: 8px;
                color: white;
                font-weight: 600;
                transition: transform 0.3s ease;
            }
            .btn-login:hover {
                transform: translateY(-2px);
                color: white;
            }
        </style>
    </head>
    <body>
        <div class="login-card">
            <?php if (isset($mostrarSelectorRoles) && $mostrarSelectorRoles): ?>
                <!-- Selector de Roles -->
                <div class="login-header">
                    <i class="fas fa-user-shield"></i>
                    <h3 class="fw-bold">Seleccionar Rol</h3>
                    <p class="text-muted"><?php echo htmlspecialchars($_SESSION['login_usuario_nombre'] ?? ''); ?></p>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="seleccionar_rol">
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-user-tag"></i> Seleccione el rol con el que desea ingresar:
                        </label>
                        <div class="list-group">
                            <?php foreach ($roles as $rol): ?>
                                <label class="list-group-item list-group-item-action" style="cursor: pointer;">
                                    <input type="radio" name="rol_id" value="<?php echo $rol['id']; ?>" 
                                           class="form-check-input me-2" required>
                                    <strong><?php echo htmlspecialchars($rol['nombre']); ?></strong>
                                    <?php if (!empty($rol['descripcion'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($rol['descripcion']); ?></small>
                                    <?php endif; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </button>
                </form>
            <?php else: ?>
                <!-- Formulario de Login -->
            <div class="login-header">
                <i class="fas fa-church"></i>
                <h3 class="fw-bold">Sistema de Administración</h3>
                <p class="text-muted">Iniciar Sesión</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="login">
                
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Usuario
                    </label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <div class="position-relative">
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3" 
                                id="togglePassword" style="border: none; background: none; color: #6c757d; z-index: 10;">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>
            <?php endif; ?>
            
            <div class="text-center mt-3">
                <small class="text-muted">Sistema de Administración de Iglesia</small>
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Función para mostrar/ocultar contraseña
            document.getElementById('togglePassword').addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const eyeIcon = document.getElementById('eyeIcon');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit();
}

