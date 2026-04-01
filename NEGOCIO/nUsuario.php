<?php
require_once '../DATOS/dUsuario.php';

class nUsuario {
    
    public function insertar($nombre_completo, $username, $password, $rol_id = null, $estado = 1, $email = null, $telefono = null) {
        // Validar campos obligatorios (rol_id ya no es obligatorio)
        if (empty($nombre_completo) || empty($username) || empty($password)) {
            return ['exito' => false, 'mensaje' => 'Todos los campos obligatorios deben ser completados'];
        }
        
        if (strlen($password) < 6) {
            return ['exito' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres'];
        }
        
        // Validar email si se proporciona
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['exito' => false, 'mensaje' => 'El formato del email no es válido'];
        }
        
        $dUsuario = new dUsuario();
        $usuarioExistente = $dUsuario->buscarPorUsuario($username);
        
        if ($usuarioExistente) {
            return ['exito' => false, 'mensaje' => 'El nombre de usuario ya existe'];
        }
        
        // rol_id puede ser NULL - se asignará después
        $resultado = $dUsuario->insertar($nombre_completo, $username, $password, $rol_id, $estado, $email, $telefono);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Usuario creado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al crear el usuario'];
        }
    }
    
    public function actualizar($id, $nombre_completo, $username, $rol_id = null, $estado = 1, $email = null, $telefono = null) {
        if (empty($id) || empty($nombre_completo) || empty($username)) {
            return ['exito' => false, 'mensaje' => 'Todos los campos obligatorios deben ser completados'];
        }
        
        // Validar email si se proporciona
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['exito' => false, 'mensaje' => 'El formato del email no es válido'];
        }
        
        // Obtener el usuario actual para mantener el rol_id si no se proporciona
        $dUsuario = new dUsuario();
        $usuarioActual = $dUsuario->buscarPorId($id);
        if (!$usuarioActual) {
            return ['exito' => false, 'mensaje' => 'Usuario no encontrado'];
        }
        
        // Si no se proporciona rol_id, mantener el actual
        if ($rol_id === null) {
            $rol_id = $usuarioActual['rol_id'];
        }
        
        $resultado = $dUsuario->actualizar($id, $nombre_completo, $username, $rol_id, $estado, $email, $telefono);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Usuario actualizado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar el usuario'];
        }
    }
    
    public function cambiarClave($id, $passwordActual, $passwordNueva) {
        if (empty($id) || empty($passwordActual) || empty($passwordNueva)) {
            return ['exito' => false, 'mensaje' => 'Todos los campos son obligatorios'];
        }
        
        if (strlen($passwordNueva) < 6) {
            return ['exito' => false, 'mensaje' => 'La nueva contraseña debe tener al menos 6 caracteres'];
        }
        
        $dUsuario = new dUsuario();
        $usuario = $dUsuario->buscarPorId($id);
        
        if (!$usuario) {
            return ['exito' => false, 'mensaje' => 'Usuario no encontrado'];
        }
        
        $usuarioCompleto = $dUsuario->buscarPorUsuario($usuario['username']);
        
        if (md5($passwordActual) !== $usuarioCompleto['password']) {
            return ['exito' => false, 'mensaje' => 'La contraseña actual es incorrecta'];
        }
        
        $resultado = $dUsuario->actualizarClave($id, $passwordNueva);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Contraseña cambiada exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al cambiar la contraseña'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de usuario requerido'];
        }
        
        $dUsuario = new dUsuario();
        $resultado = $dUsuario->eliminar($id);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Usuario eliminado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar el usuario'];
        }
    }
    
    public function listar() {
        $dUsuario = new dUsuario();
        return $dUsuario->listar();
    }
    
    public function obtenerPorId($id) {
        $dUsuario = new dUsuario();
        return $dUsuario->buscarPorId($id);
    }
    
    public function buscarPorNombre($termino) {
        if ($termino === null || $termino === '') {
            return [];
        }
        $dUsuario = new dUsuario();
        return $dUsuario->buscarPorNombre($termino);
    }
    
    public function buscar($termino = null, $rol_id = null) {
        $dUsuario = new dUsuario();
        return $dUsuario->buscar($termino, $rol_id);
    }
    
    public function obtenerPrivilegios($usuario_id) {
        if (empty($usuario_id)) {
            return [];
        }
        
        $dUsuario = new dUsuario();
        return $dUsuario->obtenerPrivilegios($usuario_id);
    }
    
    public function obtenerPrivilegiosPersonalizados($usuario_id) {
        if (empty($usuario_id)) {
            return [];
        }
        
        $dUsuario = new dUsuario();
        return $dUsuario->obtenerPrivilegiosPersonalizados($usuario_id);
    }
    
    public function asignarPrivilegios($usuario_id, $privilegios_ids) {
        if (empty($usuario_id)) {
            return ['exito' => false, 'mensaje' => 'ID de usuario requerido'];
        }
        
        $dUsuario = new dUsuario();
        $resultado = $dUsuario->asignarPrivilegios($usuario_id, $privilegios_ids);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Privilegios asignados exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al asignar privilegios'];
        }
    }
    
    public function tienePrivilegio($usuario_id, $privilegio_nombre) {
        if (empty($usuario_id) || empty($privilegio_nombre)) {
            return false;
        }
        
        $dUsuario = new dUsuario();
        return $dUsuario->tienePrivilegio($usuario_id, $privilegio_nombre);
    }
    
    public function asignarRol($usuario_id, $rol_id) {
        if (empty($usuario_id)) {
            return ['exito' => false, 'mensaje' => 'ID de usuario requerido'];
        }
        
        // rol_id puede ser null para quitar el rol
        $dUsuario = new dUsuario();
        $resultado = $dUsuario->asignarRol($usuario_id, $rol_id);
        
        if ($resultado) {
            $mensaje = $rol_id ? 'Rol asignado exitosamente' : 'Rol removido exitosamente';
            return ['exito' => true, 'mensaje' => $mensaje];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al asignar el rol. Verifique que el usuario y el rol existan.'];
        }
    }
    
    public function obtenerRoles($usuario_id) {
        if (empty($usuario_id)) {
            return [];
        }
        
        $dUsuario = new dUsuario();
        return $dUsuario->obtenerRoles($usuario_id);
    }
    
    public function asignarRoles($usuario_id, $roles_ids) {
        if (empty($usuario_id)) {
            return ['exito' => false, 'mensaje' => 'ID de usuario requerido'];
        }
        
        // roles_ids puede ser un array vacío para quitar todos los roles
        $dUsuario = new dUsuario();
        $resultado = $dUsuario->asignarRoles($usuario_id, $roles_ids);
        
        if ($resultado) {
            $cantidad = is_array($roles_ids) ? count($roles_ids) : 0;
            $mensaje = $cantidad > 0 ? "Se asignaron $cantidad rol(es) exitosamente" : 'Todos los roles fueron removidos exitosamente';
            return ['exito' => true, 'mensaje' => $mensaje];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al asignar los roles. Verifique que el usuario y los roles existan.'];
        }
    }
}