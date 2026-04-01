<?php
require_once '../DATOS/dCelula_new.php';

class nCelula {
    
    public function insertar($nombre, $direccion, $dia_reunion, $hora_reunion, $lider_id = null, $descripcion = '', $miembros_ids = []) {
        if (empty($nombre) || empty($direccion) || empty($dia_reunion) || empty($hora_reunion)) {
            return ['exito' => false, 'mensaje' => 'Todos los campos obligatorios deben ser completados'];
        }
        
        $dCelula = new dCelula();
        $resultado = $dCelula->insertar($nombre, $direccion, $dia_reunion, $hora_reunion, $lider_id);
        
        if ($resultado) {
            // Agregar miembros si se proporcionaron
            if (is_array($miembros_ids) && count($miembros_ids) > 0) {
                $dCelula->actualizarMiembros($resultado, $miembros_ids);
            }
            return ['exito' => true, 'mensaje' => 'Célula creada exitosamente', 'id' => $resultado];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al crear la célula'];
        }
    }
    
    public function actualizar($id, $nombre, $direccion, $dia_reunion, $hora_reunion, $estado, $lider_id = null, $descripcion = '', $miembros_ids = []) {
        if (empty($id) || empty($nombre) || empty($direccion) || empty($dia_reunion) || empty($hora_reunion)) {
            return ['exito' => false, 'mensaje' => 'Todos los campos obligatorios deben ser completados'];
        }
        
        $dCelula = new dCelula();
        $resultado = $dCelula->actualizar($id, $nombre, $direccion, $dia_reunion, $hora_reunion, $estado, $lider_id);
        
        if ($resultado) {
            // Actualizar miembros
            if (is_array($miembros_ids)) {
                $dCelula->actualizarMiembros($id, $miembros_ids);
            }
            return ['exito' => true, 'mensaje' => 'Célula actualizada exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar la célula'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de célula requerido'];
        }
        
        $dCelula = new dCelula();
        $resultado = $dCelula->eliminar($id);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Célula eliminada exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar la célula. Verifique que no tenga miembros asociados'];
        }
    }
    
    public function listar() {
        $dCelula = new dCelula();
        return $dCelula->listar();
    }
    
    public function obtenerPorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dCelula = new dCelula();
        return $dCelula->buscarPorId($id);
    }
    
    public function buscarPorNombre($termino) {
        if ($termino === null || $termino === '') return [];
        $dCelula = new dCelula();
        return $dCelula->buscarPorNombre($termino);
    }
    
    public function contarCelulasActivas() {
        $dCelula = new dCelula();
        return $dCelula->contarCelulasActivas();
    }
}

