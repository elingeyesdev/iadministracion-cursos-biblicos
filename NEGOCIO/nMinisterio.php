<?php
require_once '../DATOS/dMinisterio.php';

class nMinisterio {
    
    public function insertar($nombre, $descripcion, $lider, $miembros_ids = []) {
        if (empty($nombre) || empty($descripcion)) {
            return ['exito' => false, 'mensaje' => 'Nombre y descripción son obligatorios'];
        }
        
        $dMinisterio = new dMinisterio();
        $resultado = $dMinisterio->insertar($nombre, $descripcion, $lider);
        
        if ($resultado) {
            // Agregar miembros si se proporcionaron
            if (is_array($miembros_ids) && count($miembros_ids) > 0) {
                $dMinisterio->actualizarMiembros($resultado, $miembros_ids);
            }
            return ['exito' => true, 'mensaje' => 'Ministerio creado exitosamente', 'id' => $resultado];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al crear el ministerio'];
        }
    }
    
    public function actualizar($id, $nombre, $descripcion, $lider, $estado, $miembros_ids = []) {
        if (empty($id) || empty($nombre) || empty($descripcion) || empty($estado)) {
            return ['exito' => false, 'mensaje' => 'Todos los campos obligatorios deben ser completados'];
        }
        
        if (!in_array($estado, ['activo', 'inactivo'])) {
            return ['exito' => false, 'mensaje' => 'El estado debe ser activo o inactivo'];
        }
        
        $dMinisterio = new dMinisterio();
        $resultado = $dMinisterio->actualizar($id, $nombre, $descripcion, $lider, $estado);
        
        if ($resultado) {
            // Actualizar miembros
            if (is_array($miembros_ids)) {
                $dMinisterio->actualizarMiembros($id, $miembros_ids);
            }
            return ['exito' => true, 'mensaje' => 'Ministerio actualizado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar el ministerio'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de ministerio requerido'];
        }
        
        $dMinisterio = new dMinisterio();
        $resultado = $dMinisterio->eliminar($id);
        
        if ($resultado === false) {
            return ['exito' => false, 'mensaje' => 'No se puede eliminar: hay miembros asociados a este ministerio'];
        } elseif ($resultado) {
            return ['exito' => true, 'mensaje' => 'Ministerio eliminado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar el ministerio'];
        }
    }
    
    public function listar() {
        $dMinisterio = new dMinisterio();
        return $dMinisterio->listar();
    }
    
    public function obtenerPorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dMinisterio = new dMinisterio();
        return $dMinisterio->buscarPorId($id);
    }
    
    public function contarMinisteriosActivos() {
        $dMinisterio = new dMinisterio();
        return $dMinisterio->contarMinisteriosActivos();
    }
}