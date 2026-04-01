<?php
require_once '../DATOS/dTipoTransaccion.php';

class nTipoTransaccion {
    
    public function insertar($nombre, $tipo) {
        if (empty($nombre) || empty($tipo)) {
            return ['exito' => false, 'mensaje' => 'Nombre y tipo son obligatorios'];
        }
        
        if (!in_array($tipo, ['ingreso', 'egreso'])) {
            return ['exito' => false, 'mensaje' => 'El tipo debe ser ingreso o egreso'];
        }
        
        $dTipoTransaccion = new dTipoTransaccion();
        $resultado = $dTipoTransaccion->insertar($nombre, $tipo);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Tipo de transacción creado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al crear el tipo de transacción'];
        }
    }
    
    public function actualizar($id, $nombre, $tipo, $estado = null) {
        if (empty($id) || empty($nombre) || empty($tipo)) {
            return ['exito' => false, 'mensaje' => 'ID, nombre y tipo son obligatorios'];
        }
        
        if (!in_array($tipo, ['ingreso', 'egreso'])) {
            return ['exito' => false, 'mensaje' => 'El tipo debe ser ingreso o egreso'];
        }
        
        $dTipoTransaccion = new dTipoTransaccion();
        $resultado = $dTipoTransaccion->actualizar($id, $nombre, $tipo, $estado);
        
        if ($resultado) {
            return ['exito' => true, 'mensaje' => 'Tipo de transacción actualizado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al actualizar el tipo de transacción'];
        }
    }
    
    public function eliminar($id) {
        if (empty($id)) {
            return ['exito' => false, 'mensaje' => 'ID de tipo de transacción requerido'];
        }
        
        $dTipoTransaccion = new dTipoTransaccion();
        $resultado = $dTipoTransaccion->eliminar($id);
        
        if ($resultado === false) {
            return ['exito' => false, 'mensaje' => 'No se puede eliminar: hay transacciones asociadas a este tipo'];
        } elseif ($resultado) {
            return ['exito' => true, 'mensaje' => 'Tipo de transacción eliminado exitosamente'];
        } else {
            return ['exito' => false, 'mensaje' => 'Error al eliminar el tipo de transacción'];
        }
    }
    
    public function listar() {
        $dTipoTransaccion = new dTipoTransaccion();
        return $dTipoTransaccion->listar();
    }
    
    public function obtenerPorId($id) {
        if (empty($id)) {
            return null;
        }
        
        $dTipoTransaccion = new dTipoTransaccion();
        return $dTipoTransaccion->buscarPorId($id);
    }
    
    public function buscarPorNombre($termino) {
        if ($termino === null || $termino === '') return [];
        $dTipoTransaccion = new dTipoTransaccion();
        return $dTipoTransaccion->buscarPorNombre($termino);
    }
}