<?php

require_once '../DATOS/dMiembro.php';

class nMiembro {
    
    public function insertar($nombres, $apellidos, $email, $telefono, $direccion, $fecha_nacimiento, $genero, $estado_civil, $profesion = null) {
        if (empty($nombres) || empty($apellidos) || empty($email)) {
            return ['exito' => false, 'mensaje' => 'Nombres, apellidos y email son obligatorios'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['exito' => false, 'mensaje' => 'Email inválido'];
        }
        
        if (!in_array($genero, ['M', 'F'])) {
            return ['exito' => false, 'mensaje' => 'Género inválido'];
        }
        
        $dMiembro = new dMiembro();
        $resultado = $dMiembro->insertar($nombres, $apellidos, $email, $telefono, $direccion, $fecha_nacimiento, $genero, $estado_civil, $profesion);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Miembro registrado exitosamente', 'id' => $resultado];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al registrar el miembro'];
        }
    }
    
    public function actualizar($id, $nombres, $apellidos, $email, $telefono, $direccion, $fecha_nacimiento, $genero, $estado_civil, $profesion, $estado) {
        if (empty($id) || empty($nombres) || empty($apellidos) || empty($email)) {
            return ['exito' => false, 'mensaje' => 'Nombres, apellidos y email son obligatorios'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['exito' => false, 'mensaje' => 'Email inválido'];
        }
        
        if (!in_array($genero, ['M', 'F'])) {
            return ['exito' => false, 'mensaje' => 'Género inválido'];
        }
        
        $dMiembro = new dMiembro();
        $resultado = $dMiembro->actualizar($id, $nombres, $apellidos, $email, $telefono, $direccion, $fecha_nacimiento, $genero, $estado_civil, $profesion, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Miembro actualizado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar el miembro'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de miembro requerido'];
        }
        
        $dMiembro = new dMiembro();
        $resultado = $dMiembro->eliminar($id);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Miembro eliminado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar el miembro'];
        }
    }
    
    public function listar() {
        $dMiembro = new dMiembro();
        return $dMiembro->listar();
    }
    
    public function obtenerPorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dMiembro = new dMiembro();
        return $dMiembro->buscarPorId($id);
    }
    
    public function buscarPorNombre($termino) {
        if ($termino === null || $termino === '') {
            return [];
        }
        $dMiembro = new dMiembro();
        return $dMiembro->buscarPorNombre($termino);
    }
    
    public function contarMiembrosActivos() {
        $dMiembro = new dMiembro();
        return $dMiembro->contarMiembrosActivos();
    }
}