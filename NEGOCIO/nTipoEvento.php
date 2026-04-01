<?php
require_once '../DATOS/dTipoEvento.php';

class nTipoEvento {
    
    public function insertar($nombre, $descripcion) {
        if (empty($nombre)) {
            return ['exito' => false, 'mensaje' => 'El nombre es obligatorio'];
        }
        
        $dTipoEvento = new dTipoEvento();
        $resultado = $dTipoEvento->insertar($nombre, $descripcion);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Tipo de evento creado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al crear el tipo de evento'];
        }
    }
    
    public function actualizar($id, $nombre, $descripcion, $estado = null) {
        if (empty($id) || empty($nombre)) {
            return ['exito' => false, 'mensaje' => 'ID y nombre son obligatorios'];
        }
        
        $dTipoEvento = new dTipoEvento();
        $resultado = $dTipoEvento->actualizar($id, $nombre, $descripcion, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Tipo de evento actualizado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar el tipo de evento'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de tipo de evento requerido'];
        }
        
        $dTipoEvento = new dTipoEvento();
        $resultado = $dTipoEvento->eliminar($id);
        
        if ($resultado === false) {
            return ['exito' => false, 'mensaje' => 'No se puede eliminar: hay eventos asociados a este tipo'];
        } elseif ($resultado) {
            return ['exito' => true, 'mensaje' => 'Tipo de evento eliminado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar el tipo de evento'];
        }
    }
    
    public function listar() {
        $dTipoEvento = new dTipoEvento();
        return $dTipoEvento->listar();
    }
    
    public function obtenerPorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dTipoEvento = new dTipoEvento();
        return $dTipoEvento->buscarPorId($id);
    }
    
    public function buscarPorNombre($termino) {
        if ($termino === null || $termino === '') return [];
        $dTipoEvento = new dTipoEvento();
        return $dTipoEvento->buscarPorNombre($termino);
    }
}