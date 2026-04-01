<?php
require_once '../DATOS/dRol.php';

class nRol {
    
    public function insertar($nombre, $descripcion, $estado = 1) {
        if (empty($nombre)) {
            return ['exito' => false, 'mensaje' => 'El nombre del rol es obligatorio'];
        }
        
        $dRol = new dRol();
        $resultado = $dRol->insertar($nombre, $descripcion, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Rol creado exitosamente', 'id' => $resultado];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al crear el rol'];
        }
    }
    
    public function actualizar($id, $nombre, $descripcion, $estado) {
        if (empty($id) || empty($nombre)) {
            return ['exito' => false, 'mensaje' => 'ID y nombre son obligatorios'];
        }
        
        $dRol = new dRol();
        $resultado = $dRol->actualizar($id, $nombre, $descripcion, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Rol actualizado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar el rol'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de rol requerido'];
        }
        
        $dRol = new dRol();
        $resultado = $dRol->eliminar($id);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Rol eliminado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar el rol. Verifique que no haya usuarios asignados a este rol'];
        }
    }
    
    public function listar() {
        $dRol = new dRol();
        return $dRol->listar();
    }
    
    public function obtenerPorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dRol = new dRol();
        return $dRol->buscarPorId($id);
    }
    
    public function buscarPorNombre($termino) {
        if ($termino === null || $termino === '') {
            return [];
        }
        $dRol = new dRol();
        return $dRol->buscarPorNombre($termino);
    }
    
    public function obtenerPrivilegios($rol_id) {
        if (empty($rol_id)) {
            return [];
        }
        
        $dRol = new dRol();
        return $dRol->obtenerPrivilegios($rol_id);
    }
    
    public function asignarPrivilegios($rol_id, $privilegios_ids) {
        if (empty($rol_id)) {
            return ['exito' => false, 'mensaje' => 'ID de rol requerido'];
        }
        
        $dRol = new dRol();
        $resultado = $dRol->asignarPrivilegios($rol_id, $privilegios_ids);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Privilegios asignados exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al asignar privilegios'];
        }
    }
    
    public function listarPrivilegios() {
        $dRol = new dRol();
        return $dRol->listarPrivilegios();
    }
}

