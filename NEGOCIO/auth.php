<?php

require_once '../DATOS/dConexion.php';
require_once '../DATOS/dUsuario.php';

class auth {
    
    private static function iniciarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function login($username, $password) {
        $dUsuario = new dUsuario();
        $usuario = $dUsuario->buscarPorUsuario($username);
        
        // password_verify() - Verifica que la contraseña en texto plano coincida con el hash almacenado
        // Compara automáticamente el hash bcrypt almacenado con la contraseña ingresada
        // Retorna true si coinciden, false si no
        // Es seguro contra timing attacks
        if ($usuario && password_verify($password, $usuario['password']) && $usuario['estado'] == 1) {
            // Obtener todos los roles del usuario
            $roles = $dUsuario->obtenerRoles($usuario['id']);
            
            // Si tiene múltiples roles, guardar información temporal y retornar array con roles
            if (count($roles) > 1) {
                self::iniciarSesion();
                // Guardar información temporal del usuario (sin completar login)
                $_SESSION['login_pendiente'] = true;
                $_SESSION['login_usuario_id'] = $usuario['id'];
                $_SESSION['login_usuario_nombre'] = $usuario['nombre_completo'];
                $_SESSION['login_usuario_usuario'] = $usuario['username'];
                $_SESSION['login_roles'] = $roles;
                // Retornar array indicando que hay múltiples roles
                return ['multiples_roles' => true, 'roles' => $roles];
            }
            
            // Si tiene un solo rol o ninguno, proceder con login normal
            self::iniciarSesion();
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
            $_SESSION['usuario_usuario'] = $usuario['username'];
            
            $rol_id_actual = null;
            if (count($roles) == 1) {
                $_SESSION['usuario_rol_id'] = $roles[0]['id'];
                $_SESSION['usuario_rol'] = $roles[0]['nombre'];
                $rol_id_actual = $roles[0]['id'];
            } else {
                $_SESSION['usuario_rol_id'] = $usuario['rol_id'];
                $_SESSION['usuario_rol'] = $usuario['rol_nombre'] ?? null;
                $rol_id_actual = $usuario['rol_id'];
            }
            
            // Cargar solo los privilegios del rol actual (no de todos los roles)
            require_once '../NEGOCIO/nRol.php';
            $nRol = new nRol();
            $privilegios = [];
            if ($rol_id_actual) {
                $privilegios = $nRol->obtenerPrivilegios($rol_id_actual);
            }
            $_SESSION['usuario_privilegios'] = array_column($privilegios, 'nombre');
            
            // También cargar privilegios personalizados del usuario (si los tiene)
            $privilegios_personalizados = $dUsuario->obtenerPrivilegiosPersonalizados($usuario['id']);
            $privilegios_personalizados_nombres = array_column($privilegios_personalizados, 'nombre');
            
            // Combinar privilegios del rol con privilegios personalizados
            $_SESSION['usuario_privilegios'] = array_unique(array_merge($_SESSION['usuario_privilegios'], $privilegios_personalizados_nombres));
            
            // Debug: verificar qué privilegios se cargaron
            error_log("=== LOGIN DEBUG ===");
            error_log("Usuario: " . $usuario['username']);
            error_log("Rol ID: " . ($_SESSION['usuario_rol_id'] ?? 'N/A'));
            error_log("Rol Nombre: " . ($_SESSION['usuario_rol'] ?? 'N/A'));
            error_log("Total privilegios del rol: " . count($privilegios));
            error_log("Privilegios personalizados: " . count($privilegios_personalizados_nombres));
            error_log("Total privilegios cargados en sesión: " . count($_SESSION['usuario_privilegios']));
            error_log("Privilegios cargados en sesión: " . print_r($_SESSION['usuario_privilegios'], true));
            
            return true;
        }
        
        return false;
    }
    
    public static function completarLoginConRol($rol_id) {
        self::iniciarSesion();
        
        // Verificar que hay un login pendiente
        if (!isset($_SESSION['login_pendiente']) || !$_SESSION['login_pendiente']) {
            return false;
        }
        
        // Verificar que el rol seleccionado es válido para este usuario
        $roles = $_SESSION['login_roles'] ?? [];
        $rol_seleccionado = null;
        foreach ($roles as $rol) {
            if ($rol['id'] == $rol_id) {
                $rol_seleccionado = $rol;
                break;
            }
        }
        
        if (!$rol_seleccionado) {
            return false;
        }
        
        // Completar el login con el rol seleccionado
        $_SESSION['usuario_id'] = $_SESSION['login_usuario_id'];
        $_SESSION['usuario_nombre'] = $_SESSION['login_usuario_nombre'];
        $_SESSION['usuario_usuario'] = $_SESSION['login_usuario_usuario'];
        $_SESSION['usuario_rol_id'] = $rol_seleccionado['id'];
        $_SESSION['usuario_rol'] = $rol_seleccionado['nombre'];
        
        // Cargar solo los privilegios del rol seleccionado (no de todos los roles)
        require_once '../NEGOCIO/nRol.php';
        $nRol = new nRol();
        $privilegios = $nRol->obtenerPrivilegios($rol_seleccionado['id']);
        $_SESSION['usuario_privilegios'] = array_column($privilegios, 'nombre');
        
        // También cargar privilegios personalizados del usuario (si los tiene)
        $dUsuario = new dUsuario();
        $privilegios_personalizados = $dUsuario->obtenerPrivilegiosPersonalizados($_SESSION['usuario_id']);
        $privilegios_personalizados_nombres = array_column($privilegios_personalizados, 'nombre');
        
        // Combinar privilegios del rol seleccionado con privilegios personalizados
        $_SESSION['usuario_privilegios'] = array_unique(array_merge($_SESSION['usuario_privilegios'], $privilegios_personalizados_nombres));
        
        // Limpiar variables temporales
        unset($_SESSION['login_pendiente']);
        unset($_SESSION['login_usuario_id']);
        unset($_SESSION['login_usuario_nombre']);
        unset($_SESSION['login_usuario_usuario']);
        unset($_SESSION['login_roles']);
        
        return true;
    }
    
    public static function logout() {
        self::iniciarSesion();
        session_destroy();
        return true;
    }
    
    public static function estaLogueado() {
        self::iniciarSesion();
        return isset($_SESSION['usuario_id']);
    }
    
    public static function obtenerUsuarioActual() {
        self::iniciarSesion();
        if (isset($_SESSION['usuario_id'])) {
            return [
                'id' => $_SESSION['usuario_id'],
                'nombre_completo' => $_SESSION['usuario_nombre'],
                'username' => $_SESSION['usuario_usuario'],
                'rol_id' => $_SESSION['usuario_rol_id'] ?? null,
                'rol' => $_SESSION['usuario_rol'] ?? 'Usuario'
            ];
        }
        return null;
    }
    
    public static function requerirLogin() {
        if (!self::estaLogueado()) {
            header('Location: ../PRESENTACION/auth.php');
            exit();
        }
    }
    
    public static function tieneRol($rolesPermitidos) {
        if (!self::estaLogueado()) {
            return false;
        }
        
        $usuario = self::obtenerUsuarioActual();
        return in_array($usuario['rol'], $rolesPermitidos);
    }
    
    public static function requerirRol($rolesPermitidos) {
        if (!self::tieneRol($rolesPermitidos)) {
            header('Location: ../PRESENTACION/dashboard.php');
            exit();
        }
    }
    
    public static function tienePrivilegio($privilegio_nombre) {
        if (!self::estaLogueado()) {
            return false;
        }
        
        self::iniciarSesion();
        $privilegios = $_SESSION['usuario_privilegios'] ?? [];
        
        // Debug temporal - remover después
        if (isset($_GET['debug'])) {
            error_log("Usuario ID: " . ($_SESSION['usuario_id'] ?? 'N/A'));
            error_log("Privilegios en sesión: " . print_r($privilegios, true));
            error_log("Buscando privilegio: $privilegio_nombre");
            error_log("Resultado: " . (in_array($privilegio_nombre, $privilegios) ? 'SÍ' : 'NO'));
        }
        
        return in_array($privilegio_nombre, $privilegios);
    }
    
    public static function requerirPrivilegio($privilegio_nombre) {
        if (!self::tienePrivilegio($privilegio_nombre)) {
            header('Location: ../PRESENTACION/dashboard.php');
            exit();
        }
    }
    
    public static function recargarPrivilegios() {
        if (!self::estaLogueado()) {
            return;
        }
        
        self::iniciarSesion();
        $usuario = self::obtenerUsuarioActual();
        $rol_id_actual = $_SESSION['usuario_rol_id'] ?? null;
        
        // Cargar solo los privilegios del rol actual (no de todos los roles)
        require_once '../NEGOCIO/nRol.php';
        $nRol = new nRol();
        $privilegios = [];
        if ($rol_id_actual) {
            $privilegios = $nRol->obtenerPrivilegios($rol_id_actual);
        }
        $_SESSION['usuario_privilegios'] = array_column($privilegios, 'nombre');
        
        // También cargar privilegios personalizados del usuario (si los tiene)
        $dUsuario = new dUsuario();
        $privilegios_personalizados = $dUsuario->obtenerPrivilegiosPersonalizados($usuario['id']);
        $privilegios_personalizados_nombres = array_column($privilegios_personalizados, 'nombre');
        
        // Combinar privilegios del rol con privilegios personalizados
        $_SESSION['usuario_privilegios'] = array_unique(array_merge($_SESSION['usuario_privilegios'], $privilegios_personalizados_nombres));
    }
}